<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use App\Models\User\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $isTopLevel = $user->isTopLevel();

        // Cari rule CAR dari seluruh roles yang dimiliki user
        $carRules = [];
        $rules = [];
        foreach ($user->roles as $r) {
            if (!empty($r->approval_rules['car'])) {
                $carRules = $r->approval_rules['car'];
                $rules = $r->approval_rules;
                break;
            }
        }
        if (empty($carRules) && $user->role) {
            $rules = $user->role->approval_rules ?? [];
            $carRules = $rules['car'] ?? [];
        }

        $levels = (int) ($carRules['levels'] ?? ($rules['approval_levels'] ?? 1));
        $approver1RoleId = $carRules['approver_1_role_id'] ?? ($rules['approver_level_1_role_id'] ?? null);
        $approver2RoleId = $carRules['approver_2_role_id'] ?? ($rules['approver_level_2_role_id'] ?? null);

        if (empty($approver1RoleId) && $isTopLevel) {
            // Top Level tanpa approver otomatis approved
            $statusTahap1 = 'approved';
            $statusTahap2 = 'not_required';
            $statusAkhir  = 'approved';
        } elseif ($levels === 2 && !empty($approver2RoleId)) {
            // Alur 2 Step Berjenjang
            $statusTahap1 = 'pending';
            $statusTahap2 = 'pending';
            $statusAkhir  = 'pending';
        } else {
            // Alur 1 Step
            $statusTahap1 = 'pending';
            $statusTahap2 = 'not_required';
            $statusAkhir  = 'pending';
        }

        DB::beginTransaction();
        try {
            $carHeader = PengajuanCar::create([
                'user_id'               => $user->id,
                'alasan_pembelian'      => $request->alasan_pembelian,
                'receiving_account'     => $request->receiving_account,
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

            DB::commit();
            return redirect()->route('car.riwayat')->with('success', 'Pengajuan CAR berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan pengajuan CAR: ' . $e->getMessage()])->withInput();
        }
    }
}
