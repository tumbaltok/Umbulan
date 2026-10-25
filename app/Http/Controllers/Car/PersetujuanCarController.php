<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Car\PengajuanCar;
use App\Models\User\User;
use App\Services\ApprovalRoutingService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersetujuanCarController extends Controller
{
    // Menampilkan daftar antrean persetujuan pengajuan CAR untuk atasan / approver
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $approvalService = app(ApprovalRoutingService::class);

        $query = PengajuanCar::with(['user.roles', 'user.role', 'details'])
            ->orderBy('created_at', 'desc');

        $query = $approvalService->applyPendingApprovalScope($query, $atasan, 'car');

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan.persetujuanCar', compact('daftarPengajuan'));
    }

    // Memproses tindakan persetujuan (approve) atau penolakan (reject) pada pengajuan CAR
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
        $atasanRoleIds = $atasan->roles->pluck('id')->toArray();
        if (empty($atasanRoleIds) && !empty($atasan->role_id)) {
            $atasanRoleIds = [(int)$atasan->role_id];
        }
        $isAdmin = $atasan->isLevel1() || in_array(1, $atasanRoleIds) || $atasan->hasRole('ADMIN');

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanCar::lockForUpdate()->findOrFail($id);
            $approvalService = app(ApprovalRoutingService::class);
            $authCheck = $approvalService->isUserAuthorizedToApprove($atasan, $pengajuan, 'car');

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
                    SendWhatsAppNotification::sendStatusUpdate('car', $pengajuan, 'rejected', $request->catatan_penolakan);
                } catch (\Throwable $e) {
                    Log::error('Gagal kirim notifikasi penolakan CAR ke pemohon: ' . $e->getMessage());
                }

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

                // NOTIFIKASI WHATSAPP KE ATASAN TAHAP 2 JIKA ALUR BERJENJANG
                if (!$isSingleLevel) {
                    try {
                        $step2Approvers = $approvalService->getApproversForStage($pengajuan->user, 'car', 2);
                        foreach ($step2Approvers as $app2) {
                            SendWhatsAppNotification::sendNewSubmission('car', $pengajuan, $app2, 2);
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal kirim WA persetujuan tahap 2 CAR: ' . $e->getMessage());
                    }
                } else {
                    // Alur 1-Step langsung Final: Kirim notifikasi disetujui ke pemohon
                    try {
                        SendWhatsAppNotification::sendStatusUpdate('car', $pengajuan, 'approved');
                    } catch (\Throwable $e) {
                        Log::error('Gagal kirim notifikasi CAR disetujui ke pemohon: ' . $e->getMessage());
                    }
                }

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

                // Notifikasi WhatsApp disetujui (Final) ke pemohon
                try {
                    SendWhatsAppNotification::sendStatusUpdate('car', $pengajuan, 'approved');
                } catch (\Throwable $e) {
                    Log::error('Gagal kirim notifikasi CAR disetujui tahap 2 ke pemohon: ' . $e->getMessage());
                }

                return redirect()->back()->with('success', 'Persetujuan CAR Tahap 2 (Final) berhasil diproses.');
            }

            DB::commit();

            return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal memproses persetujuan CAR ID {$id}: " . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memproses persetujuan CAR.');
        }
    }
}
