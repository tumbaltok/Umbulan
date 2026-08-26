<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersetujuanCarController extends Controller
{
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $atasanRoleIds = $atasan->roles->pluck('id')->toArray();
        if (empty($atasanRoleIds) && !empty($atasan->role_id)) {
            $atasanRoleIds = [$atasan->role_id];
        }

        $isAdmin = in_array(1, $atasanRoleIds) || $atasan->hasRole('ADMIN');

        $query = PengajuanCar::with(['user.roles', 'details'])
            ->orderBy('created_at', 'desc');

        if ($isAdmin) {
            // Admin Sistem: Akses memantau seluruh antrean CAR yang pending
            $query->where('status_akhir', 'pending');
        } else {
            $query->where(function ($q) use ($atasanRoleIds) {
                // TAHAP 1 PENDING: Atasan memegang role yang menjadi Approver Step 1 pemohon
                $q->where(function ($sub) use ($atasanRoleIds) {
                    $sub->where('status_tahap_1', 'pending')
                        ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                            $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                foreach ($atasanRoleIds as $roleId) {
                                    $jsonQ->orWhere('approval_rules->car->approver_1_role_id', $roleId)
                                          ->orWhere('approval_rules->approver_level_1_role_id', $roleId);
                                }
                            });
                        });
                })
                // TAHAP 2 PENDING: Step 1 sudah disetujui, Step 2 masih pending, dan Atasan memegang role Approver Step 2 pemohon
                ->orWhere(function ($sub) use ($atasanRoleIds) {
                    $sub->where('status_tahap_1', 'approved')
                        ->where('status_tahap_2', 'pending')
                        ->where('status_tahap_2', '!=', 'not_required')
                        ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                            $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                foreach ($atasanRoleIds as $roleId) {
                                    $jsonQ->orWhere('approval_rules->car->approver_2_role_id', $roleId)
                                          ->orWhere('approval_rules->approver_level_2_role_id', $roleId);
                                }
                            });
                        });
                });
            });
        }

        // Proteksi Self-Approval: Pemohon tidak dapat melihat/menyetujui pengajuannya sendiri di antrean approval
        $query->where('user_id', '!=', $atasan->id);

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan.persetujuanCar', compact('daftarPengajuan'));
    }

    public function prosesPersetujuan(Request $request, int $id)
    {
        $tindakan = $request->input('aksi') ?? $request->input('tindakan');

        if (!in_array($tindakan, ['approved', 'rejected'])) {
            return redirect()->back()->with('error', 'Tindakan persetujuan tidak valid.');
        }

        if ($tindakan === 'rejected' && empty($request->input('catatan_penolakan'))) {
            return redirect()->back()->with('error', 'Catatan penolakan wajib diisi saat menolak pengajuan.');
        }

        $atasan = Auth::user();
        $pengajuan = PengajuanCar::findOrFail($id);

        // Proteksi Self-Approval: Cegah pemohon menyetujui pengajuannya sendiri
        if ($pengajuan->user_id === $atasan->id) {
            return redirect()->back()->with('error', 'Aksi ditolak: Anda tidak dapat memproses persetujuan pengajuan Anda sendiri (Self-Approval Protection)!');
        }

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

                return redirect()->back()->with('success', 'Pengajuan CAR berhasil ditolak.');
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
                    ? 'Pengajuan CAR berhasil disetujui (Final).'
                    : 'Persetujuan CAR Tahap 1 berhasil diproses dan diteruskan ke Tahap 2.');
            }

            // 3. PERSETUJUAN TAHAP 2 (FINAL)
            if ($pengajuan->status_tahap_2 === 'pending') {
                $pengajuan->update([
                    'status_tahap_2'      => 'approved',
                    'approver_tahap_2_id' => $atasan->id,
                    'status_akhir'        => 'approved',
                ]);

                DB::commit();

                return redirect()->back()->with('success', 'Persetujuan CAR Tahap 2 (Final) berhasil diproses.');
            }

            DB::commit();

            return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }
}
