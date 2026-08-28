<?php

namespace App\Http\Controllers\Mpr;

use App\Http\Controllers\Controller;
use App\Models\Mpr\PengajuanMpr;
use App\Models\User\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersetujuanMprController extends Controller
{
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $atasanRoleIds = $atasan->roles->pluck('id')->toArray();
        if (empty($atasanRoleIds) && !empty($atasan->role_id)) {
            $atasanRoleIds = [$atasan->role_id];
        }

        $isAdmin = $atasan->isLevel1() || in_array(1, $atasanRoleIds) || $atasan->hasRole('ADMIN');

        $query = PengajuanMpr::with(['user.roles', 'items'])
            ->orderBy('created_at', 'desc');

        if ($isAdmin) {
            // Admin Sistem: Akses memantau seluruh antrean MPR yang pending
            $query->where('status_akhir', 'pending');
        } else {
            $query->where(function ($q) use ($atasanRoleIds) {
                // TAHAP 1 PENDING: Atasan memegang role yang menjadi Approver Step 1 pemohon
                $q->where(function ($sub) use ($atasanRoleIds) {
                    $sub->where('status_tahap_1', 'pending')
                        ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                            $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                foreach ($atasanRoleIds as $roleId) {
                                    $jsonQ->orWhere('approval_rules->mpr->approver_1_role_id', $roleId)
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
                                    $jsonQ->orWhere('approval_rules->mpr->approver_2_role_id', $roleId)
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

                // NOTIFIKASI WHATSAPP KE ATASAN TAHAP 2 JIKA ALUR BERJENJANG
                if (!$isSingleLevel) {
                    try {
                        $submitter = $pengajuan->user;
                        $mprRules = [];
                        $rules = [];
                        foreach ($submitter->roles as $r) {
                            if (!empty($r->approval_rules['mpr'])) {
                                $mprRules = $r->approval_rules['mpr'];
                                $rules = $r->approval_rules;
                                break;
                            }
                        }
                        if (empty($mprRules) && $submitter->role) {
                            $rules = $submitter->role->approval_rules ?? [];
                            $mprRules = $rules['mpr'] ?? [];
                        }
                        $approver2RoleId = $mprRules['approver_2_role_id'] ?? ($rules['approver_level_2_role_id'] ?? null);
                        if ($approver2RoleId) {
                            $step2Approvers = User::whereHas('roles', fn($q) => $q->where('roles.id', $approver2RoleId))
                                ->where('id', '!=', $submitter->id)
                                ->whereNotNull('phone_verified_at')
                                ->get();
                            $waService = app(WhatsAppService::class);
                            foreach ($step2Approvers as $app2) {
                                $waService->sendNewSubmissionNotification('mpr', $pengajuan, $app2, 2);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal kirim WA persetujuan tahap 2 MPR: ' . $e->getMessage());
                    }
                }

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
