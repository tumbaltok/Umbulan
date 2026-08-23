<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersetujuanCarController extends Controller
{
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
}
