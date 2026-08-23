<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use App\Models\User\Station;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanCarController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $riwayatCar = PengajuanCar::with('details')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('car.carriwayat', compact('riwayatCar'));
    }

    public function create()
    {
        $daftarStasiun = Station::orderBy('name', 'asc')->get();
        return view('car.carcreate', compact('daftarStasiun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'alasan_pembelian' => 'required|string',
            'receiving_account' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string|max:255',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.satuan' => 'required|string',
            'items.*.estimasi_harga' => 'required|numeric|min:0',
            'items.*.dokumen_pendukung' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $userRole = $user->role;
        $approvalRules = $userRole->approval_rules ?? [];
        $requiredLevels = (int) ($approvalRules['approval_levels'] ?? 1);

        if (empty($userRole->parent_role_id)) {
            $statusTahap1 = 'approved';
            $statusTahap2 = 'approved';
            $statusAkhir  = 'approved';
        } else {
            $statusTahap1 = 'pending';
            $statusTahap2 = ($requiredLevels === 1) ? 'not_required' : 'pending';
            $statusAkhir  = 'pending';
        }

        $carHeader = PengajuanCar::create([
            'user_id'               => $user->id,
            'alasan_pembelian'      => $request->alasan_pembelian,
            'receiving_account'     => $request->receiving_account,
            'total_approval_levels' => $requiredLevels,
            'status_tahap_1'        => $statusTahap1,
            'approver_tahap_1_id'   => $statusTahap1 === 'approved' ? $user->id : null,
            'status_tahap_2'        => $statusTahap2,
            'approver_tahap_2_id'   => $statusTahap2 === 'approved' ? $user->id : null,
            'status_akhir'          => $statusAkhir,
        ]);

        foreach ($request->items as $index => $item) {
            $pathDokumen = null;
            if ($request->hasFile("items.{$index}.dokumen_pendukung")) {
                $file = $request->file("items.{$index}.dokumen_pendukung");
                $pathDokumen = $file->store('dokumen_car', 'public');
            }

            $carHeader->details()->create([
                'nama_barang' => $item['nama_barang'],
                'jumlah' => $item['jumlah'],
                'satuan' => $item['satuan'],
                'estimasi_harga' => $item['estimasi_harga'],
                'total_harga' => $item['jumlah'] * $item['estimasi_harga'],
                'dokumen_nota_or_proposal' => $pathDokumen,
            ]);
        }

        return redirect()->route('car.riwayat')->with('success', 'Pengajuan CAR berhasil dikirim.');
    }

    public function listPengajuan()
    {
        $atasan = Auth::user();
        $atasanRole = $atasan->role;

        $query = PengajuanCar::with(['user.role', 'details']);

        if (empty($atasanRole->parent_role_id)) {
            $query->where('status_akhir', 'pending');
        } else {
            $atasanTreeCode = $atasanRole->tree_code;

            $query->where(function ($q) use ($atasan, $atasanRole, $atasanTreeCode) {
                // ANTREAN TAHAP 1
                $q->where(function ($sub) use ($atasan, $atasanRole, $atasanTreeCode) {
                    $sub->where('status_tahap_1', 'pending')
                        ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCode) {
                            $uq->where('atasan_langsung_id', $atasan->id)
                               ->orWhereHas('role', function ($rq) use ($atasanTreeCode) {
                                   $rq->where('tree_code', 'LIKE', $atasanTreeCode . '.%')
                                      ->whereRaw("LENGTH(tree_code) - LENGTH(REPLACE(tree_code, '.', '')) = ?", [substr_count($atasanTreeCode, '.') + 1]);
                               });
                        });

                    // Validasi Stasiun Kerja Level 1
                    $reqStationLvl1 = $atasanRole->approval_rules['require_same_station_level_1'] ?? ($atasanRole->approval_rules['require_same_station'] ?? false);
                    if ($reqStationLvl1) {
                        $sub->whereHas('user', fn($uq) => $uq->where('station_id', $atasan->station_id));
                    }
                })
                // ANTREAN TAHAP 2
                ->orWhere(function ($sub) use ($atasan, $atasanRole, $atasanTreeCode) {
                    $sub->where('total_approval_levels', 2)
                        ->where('status_tahap_1', 'approved')
                        ->where('status_tahap_2', 'pending')
                        ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCode) {
                            $uq->where('atasan_dua_id', $atasan->id)
                               ->orWhereHas('role', function ($rq) use ($atasanTreeCode) {
                                   $rq->where('tree_code', 'LIKE', $atasanTreeCode . '.%');
                               });
                        });

                    // Validasi Stasiun Kerja Level 2
                    $reqStationLvl2 = $atasanRole->approval_rules['require_same_station_level_2'] ?? false;
                    if ($reqStationLvl2) {
                        $sub->whereHas('user', fn($uq) => $uq->where('station_id', $atasan->station_id));
                    }
                });
            });
        }

        $daftarPengajuan = $query->latest()->get();

        return view('admin.persetujuan.persetujuanCar', compact('daftarPengajuan'));
    }

    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan'          => 'required|in:approved,rejected',
            'catatan_penolakan' => 'required_if:tindakan,rejected|string|nullable',
        ]);

        $atasan = Auth::user();
        $pengajuan = PengajuanCar::findOrFail($id);
        $tindakan = $request->tindakan;

        if ($tindakan === 'rejected') {
            $pengajuan->update([
                'status_tahap_1'    => $pengajuan->status_tahap_1 === 'pending' ? 'rejected' : $pengajuan->status_tahap_1,
                'status_tahap_2'    => $pengajuan->status_tahap_2 === 'pending' ? 'rejected' : $pengajuan->status_tahap_2,
                'status_akhir'      => 'rejected',
                'catatan_penolakan' => $request->catatan_penolakan,
            ]);

            return redirect()->back()->with('success', 'Pengajuan CAR berhasil ditolak.');
        }

        if ($pengajuan->status_tahap_1 === 'pending') {
            $updateData = [
                'status_tahap_1'      => 'approved',
                'approver_tahap_1_id' => $atasan->id,
            ];

            if ($pengajuan->total_approval_levels === 1) {
                $updateData['status_tahap_2'] = 'not_required';
                $updateData['status_akhir']   = 'approved';
            }

            $pengajuan->update($updateData);

            return redirect()->back()->with('success', 'Persetujuan Tahap 1 berhasil diproses.');
        }

        if ($pengajuan->total_approval_levels === 2 && $pengajuan->status_tahap_2 === 'pending') {
            $pengajuan->update([
                'status_tahap_2'      => 'approved',
                'approver_tahap_2_id' => $atasan->id,
                'status_akhir'        => 'approved',
            ]);

            return redirect()->back()->with('success', 'Persetujuan Tahap 2 (Final) berhasil diproses.');
        }

        return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
    }

    public function print(int $id)
    {
        $car = PengajuanCar::with([
            'user.role',
            'details',
            'approverTahap1.role',
            'approverTahap2.role'
        ])->findOrFail($id);

        if ($car->status_akhir !== 'approved') {
            return redirect()->back()->with('error', 'Dokumen CAR belum disetujui secara penuh.');
        }

        $approverLevel1 = $car->approverTahap1;

        // Jika 1 level, samakan penandatangan Level 2 dengan Level 1
        if ($car->status_tahap_2 === 'not_required' || empty($car->approver_tahap_2_id)) {
            $approverLevel2 = $approverLevel1;
        } else {
            $approverLevel2 = $car->approverTahap2;
        }

        $pdf = Pdf::loadView('car.carcetak', compact('car', 'approverLevel1', 'approverLevel2'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('CAR_' . sprintf('%03d', $car->id) . '.pdf');
    }
}
