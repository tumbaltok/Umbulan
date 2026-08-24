<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\Cuti\PengajuanCuti;
use App\Traits\CutiHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersetujuanCutiController extends Controller
{
    use CutiHelperTrait;

    public function listAtasanView()
    {
        /** @var \App\Models\User\User $atasan */
        $atasan = Auth::user();
        $atasanRoleId = $atasan->role_id;

        $query = PengajuanCuti::query()
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->select(
                'pengajuan_cutis.*',
                'users.name as user_name',
                'jenis_cutis.name_cuti',
                'sub_cutis.nama_sub_cuti'
            )
            ->orderBy('pengajuan_cutis.created_at', 'desc');

        if ($atasanRoleId === 1) {
            // Admin Utama: Dapat memantau seluruh antrean cuti yang belum tuntas
            $query->where('pengajuan_cutis.status_akhir', 'pending');
        } else {
            $query->where(function ($q) use ($atasanRoleId) {
                // TAHAP 1 PENDING: Role user saat ini ditugaskan sebagai Approver Step 1
                $q->where(function ($sub) use ($atasanRoleId) {
                    $sub->where('pengajuan_cutis.status_tahap_1', 'pending')
                        ->where(function ($jsonQ) use ($atasanRoleId) {
                            $jsonQ->where('roles.approval_rules->cuti->approver_1_role_id', $atasanRoleId)
                                  ->orWhere('roles.approval_rules->approver_level_1_role_id', $atasanRoleId);
                        });
                })
                // TAHAP 2 PENDING: Step 1 sudah disetujui, Step 2 masih pending, dan Role user ditugaskan sebagai Approver Step 2
                ->orWhere(function ($sub) use ($atasanRoleId) {
                    $sub->where('pengajuan_cutis.status_tahap_1', 'approved')
                        ->where('pengajuan_cutis.status_tahap_2', 'pending')
                        ->where('pengajuan_cutis.status_tahap_2', '!=', 'not_required')
                        ->where(function ($jsonQ) use ($atasanRoleId) {
                            $jsonQ->where('roles.approval_rules->cuti->approver_2_role_id', $atasanRoleId)
                                  ->orWhere('roles.approval_rules->approver_level_2_role_id', $atasanRoleId);
                        });
                });
            });
        }

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan.persetujuancuti', compact('daftarPengajuan'));
    }

    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan'          => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string',
        ]);

        $atasan = Auth::user();
        $tindakan = $request->tindakan;
        $pengajuan = PengajuanCuti::findOrFail($id);

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
                    'catatan_penolakan'   => $request->catatan_penolakan,
                ]);

                DB::commit();

                return redirect()->back()->with('success', 'Pengajuan cuti telah ditolak.');
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

                if ($isSingleLevel) {
                    $this->sinkronisasiCutiDanAbsen($pengajuan);
                }

                DB::commit();

                return redirect()->back()->with('success', $isSingleLevel 
                    ? 'Pengajuan Cuti berhasil disetujui (Final).' 
                    : 'Persetujuan Cuti Tahap 1 berhasil diproses dan diteruskan ke Tahap 2.');
            }

            // 3. PERSETUJUAN TAHAP 2 (FINAL)
            if ($pengajuan->status_tahap_2 === 'pending') {
                $pengajuan->update([
                    'status_tahap_2'      => 'approved',
                    'approver_tahap_2_id' => $atasan->id,
                    'status_akhir'        => 'approved',
                ]);

                $this->sinkronisasiCutiDanAbsen($pengajuan);

                DB::commit();

                return redirect()->back()->with('success', 'Persetujuan Cuti Tahap 2 (Final) berhasil diproses.');
            }

            DB::commit();

            return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }
}
