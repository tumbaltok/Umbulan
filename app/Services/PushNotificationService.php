<?php

namespace App\Services;

use App\Models\User\User;
use App\Notifications\PengajuanNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PushNotificationService
{
    /**
     * Mengirim push notification ke seluruh perangkat APK Native user melalui FCM
     * sekaligus menyimpan notifikasi ke database (In-App Notifications).
     */
    public function sendToUser(User $recipient, string $title, string $body, array $data = []): array
    {
        // 1. Simpan ke Database Notification Laravel (untuk list notifikasi pada APK / API)
        Notification::send($recipient, new PengajuanNotification($title, $body, $data));

        // 2. Ambil seluruh token FCM perangkat Android / iOS milik penerima
        $tokens = $recipient->devices()->pluck('fcm_token')->filter()->values()->toArray();
        if (empty($tokens)) {
            return [
                'success' => true,
                'delivered_push' => 0,
                'message' => 'Notifikasi tersimpan di kotak masuk aplikasi (perangkat APK belum terdaftar).',
            ];
        }

        return $this->dispatchFcmPayload($tokens, $title, $body, $data);
    }

    /**
     * Notifikasi pengajuan baru (Cuti / CAR / MPR) ke Approver
     */
    public function sendNewSubmissionNotification(string $type, $pengajuan, User $approver, int $tahap = 1): array
    {
        $pemohon = $pengajuan->user;
        $namaPemohon = $pemohon->name ?? 'Karyawan';
        $typeUpper = strtoupper($type);

        $title = "Pengajuan {$typeUpper} Baru (Tahap {$tahap})";
        $body = $this->buildSubmissionBody($type, $pengajuan, $namaPemohon);

        return $this->sendToUser($approver, $title, $body, [
            'category' => 'new_submission',
            'module' => strtolower($type),
            'submission_id' => (string) $pengajuan->id,
            'tahap' => (string) $tahap,
            'screen' => "approval_{$type}",
        ]);
    }

    /**
     * Notifikasi pengingat (follow-up) pengajuan pending ke Approver
     */
    public function sendFollowUpNotification(string $type, $pengajuan, User $approver, int $tahap = 1): array
    {
        $pemohon = $pengajuan->user;
        $namaPemohon = $pemohon->name ?? 'Karyawan';
        $typeUpper = strtoupper($type);

        $title = "Pengingat Persetujuan {$typeUpper} (Tahap {$tahap})";
        $body = "Menunggu peninjauan Anda: " . $this->buildSubmissionBody($type, $pengajuan, $namaPemohon);

        return $this->sendToUser($approver, $title, $body, [
            'category' => 'follow_up',
            'module' => strtolower($type),
            'submission_id' => (string) $pengajuan->id,
            'tahap' => (string) $tahap,
            'screen' => "approval_{$type}",
        ]);
    }

    /**
     * Notifikasi hasil persetujuan / penolakan ke Pemohon
     */
    public function sendApprovalStatusNotification(string $type, $pengajuan, string $status, ?string $catatan = null): array
    {
        $pemohon = $pengajuan->user;
        if (!$pemohon) {
            return ['success' => false, 'message' => 'Pemohon tidak ditemukan.'];
        }

        $typeUpper = strtoupper($type);
        $isApproved = ($status === 'approved');
        $statusLabel = $isApproved ? 'Disetujui' : 'Ditolak';

        $title = "Pengajuan {$typeUpper} {$statusLabel}";
        $body = "Pengajuan {$typeUpper} Anda telah {$statusLabel}.";
        if (!$isApproved && !empty($catatan)) {
            $body .= " Catatan: {$catatan}";
        }

        return $this->sendToUser($pemohon, $title, $body, [
            'category' => 'status_update',
            'module' => strtolower($type),
            'submission_id' => (string) $pengajuan->id,
            'status' => $status,
            'screen' => "{$type}_history",
        ]);
    }

    protected function buildSubmissionBody(string $type, $pengajuan, string $namaPemohon): string
    {
        if ($type === 'cuti') {
            $perihal = $pengajuan->sub_cuti_id && $pengajuan->subCuti
                ? $pengajuan->subCuti->nama_sub_cuti
                : ($pengajuan->jenisCuti->name_cuti ?? 'Cuti/Izin');
            return "{$namaPemohon} mengajukan {$perihal} ({$pengajuan->total_hari} hari: {$pengajuan->tanggal_mulai} s/d {$pengajuan->tanggal_selesai}).";
        } elseif ($type === 'car') {
            $totalEst = 'Rp ' . number_format($pengajuan->details?->sum('total_harga') ?? 0, 0, ',', '.');
            return "{$namaPemohon} mengajukan CAR ({$totalEst}): " . ($pengajuan->alasan_pembelian ?: '-');
        } elseif ($type === 'mpr') {
            return "{$namaPemohon} mengajukan MPR #{$pengajuan->nomor_mpr}: " . ($pengajuan->keperluan_urgensi ?: '-');
        }

        return "{$namaPemohon} mengirimkan pengajuan baru.";
    }

    /**
     * Mengirim payload notifikasi ke Firebase Cloud Messaging (jika FCM dikonfigurasi).
     */
    protected function dispatchFcmPayload(array $tokens, string $title, string $body, array $data): array
    {
        $serverKey = config('services.fcm.server_key');
        if (empty($serverKey)) {
            Log::info("[PushNotificationService] Skip HTTP FCM karena FCM_SERVER_KEY belum diisi; tersimpan di DB notifications.", [
                'tokens_count' => count($tokens),
                'title' => $title,
            ]);
            return [
                'success' => true,
                'delivered_push' => 0,
                'message' => 'Notifikasi tersimpan di database (FCM key belum dikonfigurasi).',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(8)->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'delivered_push' => count($tokens),
                    'response' => $response->json(),
                ];
            }

            Log::warning("[PushNotificationService] FCM HTTP {$response->status()}: " . $response->body());
            return ['success' => false, 'message' => 'Gagal mengirim payload FCM.'];
        } catch (\Throwable $e) {
            Log::error("[PushNotificationService] Exception FCM: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
