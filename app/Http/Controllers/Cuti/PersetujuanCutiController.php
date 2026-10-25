<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Cuti\PengajuanCuti;
use App\Models\User\User;
use App\Services\ApprovalRoutingService;
use App\Services\WhatsAppService;
use App\Traits\CutiHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersetujuanCutiController extends Controller
{
    use CutiHelperTrait;

    // Menampilkan daftar antrean pengajuan cuti yang memerlukan persetujuan atasan
    public function listAtasanView()
    {
        $atasan = Auth::user();
        $approvalService = app(ApprovalRoutingService::class);

        $query = PengajuanCuti::with(['user.roles', 'user.role', 'jenisCuti', 'subCuti'])
            ->orderBy('created_at', 'desc');

        $query = $approvalService->applyPendingApprovalScope($query, $atasan, 'cuti');

        $daftarPengajuan = $query->get();

        $daftarPengajuan->transform(function ($item) {
            $item->user_name = $item->user->name ?? '-';
            $item->name_cuti = $item->jenisCuti->name_cuti ?? 'Cuti';
            $item->nama_sub_cuti = $item->subCuti->nama_sub_cuti ?? null;
            return $item;
        });

        return view('admin.persetujuan.persetujuancuti', compact('daftarPengajuan'));
    }

    // Memproses persetujuan (approve) atau penolakan (reject) cuti
    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan'          => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string',
        ]);

        $atasan = Auth::user();
        $tindakan = $request->tindakan;

        $atasanRoleIds = $atasan->roles->pluck('id')->toArray();
        if (empty($atasanRoleIds) && !empty($atasan->role_id)) {
            $atasanRoleIds = [(int)$atasan->role_id];
        }
        $isAdmin = $atasan->isLevel1() || in_array(1, $atasanRoleIds) || $atasan->hasRole('ADMIN');

        DB::beginTransaction();
        try {
            // [C-02 FIX] Kunci baris pengajuan cuti (lockForUpdate) untuk mencegah double-approval
            $pengajuan = PengajuanCuti::lockForUpdate()->findOrFail($id);
            $approvalService = app(ApprovalRoutingService::class);
            $authCheck = $approvalService->isUserAuthorizedToApprove($atasan, $pengajuan, 'cuti');

            if (!$authCheck['authorized']) {
                DB::rollBack();
                return redirect()->back()->with('error', $authCheck['reason']);
            }
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

                // Notifikasi WhatsApp status ditolak ke pemohon
                try {
                    SendWhatsAppNotification::sendStatusUpdate('cuti', $pengajuan, 'rejected', $request->catatan_penolakan);
                } catch (\Throwable $e) {
                    Log::error('Gagal kirim notifikasi penolakan cuti ke pemohon: ' . $e->getMessage());
                }

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
                        $step2Approvers = $approvalService->getApproversForStage($pengajuan->user, 'cuti', 2);
                        foreach ($step2Approvers as $app2) {
                            SendWhatsAppNotification::sendNewSubmission('cuti', $pengajuan, $app2, 2);
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal kirim WA persetujuan tahap 2 cuti: ' . $e->getMessage());
                    }
                } else {
                    // Alur 1-Step langsung Final: Kirim notifikasi disetujui ke pemohon
                    try {
                        SendWhatsAppNotification::sendStatusUpdate('cuti', $pengajuan, 'approved');
                    } catch (\Throwable $e) {
                        Log::error('Gagal kirim notifikasi cuti disetujui ke pemohon: ' . $e->getMessage());
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

                // Notifikasi WhatsApp disetujui (Final) ke pemohon
                try {
                    SendWhatsAppNotification::sendStatusUpdate('cuti', $pengajuan, 'approved');
                } catch (\Throwable $e) {
                    Log::error('Gagal kirim notifikasi cuti disetujui tahap 2 ke pemohon: ' . $e->getMessage());
                }

                return redirect()->back()->with('success', 'Persetujuan Cuti Tahap 2 (Final) berhasil diproses.');
            }

            DB::commit();

            return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal memproses persetujuan cuti ID {$id}: " . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memproses persetujuan cuti.');
        }
    }
}
