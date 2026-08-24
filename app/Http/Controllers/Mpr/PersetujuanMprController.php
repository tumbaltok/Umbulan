<?php

namespace App\Http\Controllers\Mpr;

use App\Http\Controllers\Controller;
use App\Models\Mpr\PengajuanMpr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersetujuanMprController extends Controller
{
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $atasanRoleId = $atasan->role_id;

        $query = PengajuanMpr::with(['user.role', 'items'])
            ->orderBy('created_at', 'desc');

        if ($atasanRoleId === 1) {
            // Admin Sistem: Akses memantau seluruh antrean MPR yang pending
            $query->where('status_akhir', 'pending');
        } else {
            $query->where(function ($q) use ($atasanRoleId) {
                // TAHAP 1 PENDING: Role user saat ini ditugaskan sebagai Approver Step 1
                $q->where(function ($sub) use ($atasanRoleId) {
                    $sub->where('status_tahap_1', 'pending')
                        ->whereHas('user.role', function ($rq) use ($atasanRoleId) {
                            $rq->where(function ($jsonQ) use ($atasanRoleId) {
                                $jsonQ->where('approval_rules->mpr->approver_1_role_id', $atasanRoleId)
                                      ->orWhere('approval_rules->approver_level_1_role_id', $atasanRoleId);
                            });
                        });
                })
                // TAHAP 2 PENDING: Step 1 sudah disetujui, Step 2 masih pending, dan Role user ditugaskan sebagai Approver Step 2
                ->orWhere(function ($sub) use ($atasanRoleId) {
                    $sub->where('status_tahap_1', 'approved')
                        ->where('status_tahap_2', 'pending')
                        ->where('status_tahap_2', '!=', 'not_required')
                        ->whereHas('user.role', function ($rq) use ($atasanRoleId) {
                            $rq->where(function ($jsonQ) use ($atasanRoleId) {
                                $jsonQ->where('approval_rules->mpr->approver_2_role_id', $atasanRoleId)
                                      ->orWhere('approval_rules->approver_level_2_role_id', $atasanRoleId);
                            });
                        });
                });
            });
        }

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan.persetujuanmpr', compact('daftarPengajuan'));
    }

    public function prosesPersetujuan(Request $request, int $id)
    {
        $tindakan = $request->input('aksi') ?? $request->input('tindakan');

        if (!in_array($tindakan, ['approved', 'rejected'])) {
            return redirect()->back()->with('error', 'Tindakan persetujuan tidak valid.');
        }

        if ($tindakan === 'rejected' && empty($request->input('catatan_penolakan'))) {
            // Jika reject tidak memiliki catatan penolakan spesifik, beri catatan default jika null
            $catatanPenolakan = $request->input('catatan_penolakan') ?? 'Ditolak oleh penanggung jawab role.';
        } else {
            $catatanPenolakan = $request->input('catatan_penolakan');
        }

        $atasan = Auth::user();
        $pengajuan = PengajuanMpr::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. PENOLAKAN PENGAJUAN (REJECT - FIRST TO ACT)
            if ($tindakan === 'rejected') {
                $isTahap1Pending = $pengajuan->status_tahap_1 === 'pending';

                $pengajuan->update([
                    'status_tahap_1'      => $isTahap1Pending ? 'rejected' : $pengajuan->status_tahap_1,
                    'approver_tahap_1_id' => $isTahap1Pending ? $atasan->id : $pengajuan->approver_tahap_1_id,
                    'status_tahap_2'      => !$isTahap1Pending && $pengajuan->status_tahap_2 === 'pending' ? 'rejected' : $pengajuan->status_tahap_2,
                    'approver_tahap_2_id' => !$isTahap1Pending && $pengajuan->status_tahap_2 === 'pending' ? $atasan->id : $pengajuan->approver_tahap_2_id,
                    'status_akhir'        => 'rejected',
                    'catatan_penolakan'   => $catatanPenolakan,
                ]);

                DB::commit();

                return redirect()->back()->with('success', 'Pengajuan MPR telah ditolak.');
            }

            // 2. PERSETUJUAN TAHAP 1
            if ($pengajuan->status_tahap_1 === 'pending') {
                $isSingleLevel = $pengajuan->status_tahap_2 === 'not_required';

                $updateData = [
                    'status_tahap_1'      => 'approved',
                    'approver_tahap_1_id' => $atasan->id,
                ];

                if ($isSingleLevel) {
                    $updateData['status_akhir'] = 'approved';
                }

                $pengajuan->update($updateData);

                DB::commit();

                return redirect()->back()->with('success', $isSingleLevel
                    ? 'Pengajuan MPR berhasil disetujui (Final).'
                    : 'Persetujuan MPR Tahap 1 berhasil diproses dan diteruskan ke Tahap 2.');
            }

            // 3. PERSETUJUAN TAHAP 2 (FINAL)
            if ($pengajuan->status_tahap_2 === 'pending') {
                $pengajuan->update([
                    'status_tahap_2'      => 'approved',
                    'approver_tahap_2_id' => $atasan->id,
                    'status_akhir'        => 'approved',
                ]);

                DB::commit();

                return redirect()->back()->with('success', 'Persetujuan MPR Tahap 2 (Final) berhasil diproses.');
            }

            DB::commit();

            return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }
}
