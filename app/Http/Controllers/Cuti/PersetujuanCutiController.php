<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\Cuti\PengajuanCuti;
use App\Models\User\User;
use App\Services\WhatsAppService;
use App\Traits\CutiHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersetujuanCutiController extends Controller
{
    use CutiHelperTrait;

    public function listAtasanView()
    {
        /** @var \App\Models\User\User $atasan */
        $atasan = Auth::user();
        $atasanRoleIds = $atasan->roles->pluck('id')->toArray();
        if (empty($atasanRoleIds) && !empty($atasan->role_id)) {
            $atasanRoleIds = [$atasan->role_id];
        }

        $isAdmin = $atasan->isLevel1() || in_array(1, $atasanRoleIds) || $atasan->hasRole('ADMIN');

        $query = PengajuanCuti::with(['user.roles', 'jenisCuti', 'subCuti'])
            ->orderBy('created_at', 'desc');

        if ($isAdmin) {
            // Admin Utama: Dapat memantau seluruh antrean cuti yang belum tuntas
            $query->where('status_akhir', 'pending');
        } else {
            $query->where(function ($q) use ($atasanRoleIds) {
                // TAHAP 1 PENDING: Atasan memegang role yang menjadi Approver Step 1 pemohon
                $q->where(function ($sub) use ($atasanRoleIds) {
                    $sub->where('status_tahap_1', 'pending')
                        ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                            $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                foreach ($atasanRoleIds as $roleId) {
                                    $jsonQ->orWhere('approval_rules->cuti->approver_1_role_id', $roleId)
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
                                    $jsonQ->orWhere('approval_rules->cuti->approver_2_role_id', $roleId)
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

        $daftarPengajuan->transform(function ($item) {
            $item->user_name = $item->user->name ?? '-';
            $item->name_cuti = $item->jenisCuti->name_cuti ?? 'Cuti';
            $item->nama_sub_cuti = $item->subCuti->nama_sub_cuti ?? null;
            return $item;
        });

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

                // NOTIFIKASI WHATSAPP KE ATASAN TAHAP 2 JIKA ALUR BERJENJANG
                if (!$isSingleLevel) {
                    try {
                        $submitter = $pengajuan->user;
                        $cutiRules = [];
                        $rules = [];
                        foreach ($submitter->roles as $r) {
                            if (!empty($r->approval_rules['cuti'])) {
                                $cutiRules = $r->approval_rules['cuti'];
                                $rules = $r->approval_rules;
                                break;
                            }
                        }
                        if (empty($cutiRules) && $submitter->role) {
                            $rules = $submitter->role->approval_rules ?? [];
                            $cutiRules = $rules['cuti'] ?? [];
                        }
                        $approver2RoleId = $cutiRules['approver_2_role_id'] ?? ($rules['approver_level_2_role_id'] ?? null);
                        if ($approver2RoleId) {
                            $step2Approvers = User::whereHas('roles', fn($q) => $q->where('roles.id', $approver2RoleId))
                                ->where('id', '!=', $submitter->id)
                                ->whereNotNull('phone_verified_at')
                                ->get();
                            $waService = app(WhatsAppService::class);
                            foreach ($step2Approvers as $app2) {
                                $waService->sendNewSubmissionNotification('cuti', $pengajuan, $app2, 2);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal kirim WA persetujuan tahap 2 cuti: ' . $e->getMessage());
                    }
                }

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
