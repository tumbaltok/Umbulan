<?php

namespace App\Services;

use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? config('services.whatsapp.url', 'http://127.0.0.1:3001'), '/');
    }

    /**
     * Normalisasi nomor telepon ke format baku internasional Indonesia (62xxx).
     */
    public function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Mengirimkan pesan WhatsApp melalui microservice Baileys.
     */
    public function sendMessage(string $number, string $message): array
    {
        $target = $this->formatPhoneNumber($number);

        if (empty($target) || strlen($target) < 10) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid.',
            ];
        }

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/send-message", [
                'number'  => $target,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success'   => $result['success'] ?? true,
                    'message'   => $result['message'] ?? 'Pesan terkirim.',
                    'messageId' => $result['messageId'] ?? null,
                    'target'    => $target,
                ];
            }

            $errorResult = $response->json();
            $errorMessage = $errorResult['message'] ?? 'Gagal mengirim pesan melalui WhatsApp Gateway.';

            Log::error("[WhatsAppService] Gateway HTTP {$response->status()}: {$errorMessage}", [
                'target' => $target,
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'status'  => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("[WhatsAppService] Gagal terhubung ke WhatsApp microservice: " . $e->getMessage(), [
                'url' => $this->baseUrl,
                'target' => $target
            ]);

            return [
                'success' => false,
                'message' => 'Microservice WhatsApp tidak dapat dihubungi. Pastikan service berjalan.',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Mengambil status koneksi terkini dari microservice Baileys.
     */
    public function getStatus(): array
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/status");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'online'  => true,
                    'status'  => $data['status'] ?? 'disconnected',
                    'phone'   => $data['phone'] ?? null,
                    'qr'      => $data['qr'] ?? null,
                    'raw'     => $data['raw'] ?? null,
                    'uptime'  => $data['uptime'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            // Service offline / unreachable
        }

        return [
            'online'  => false,
            'status'  => 'disconnected',
            'phone'   => null,
            'qr'      => null,
            'raw'     => null,
            'uptime'  => 0,
            'error'   => 'Service WhatsApp Gateway offline / tidak aktif.',
        ];
    }

    /**
     * Cache status 10 detik agar tidak membebani reload setiap halaman layout.
     */
    public static function getStatusCached(): array
    {
        return Cache::remember('whatsapp_gateway_status', 10, function () {
            return (new static)->getStatus();
        });
    }

    /**
     * Mengambil QR Code Base64 untuk halaman scanning admin.
     */
    public function getQr(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/qr");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning("[WhatsAppService] Gagal mengambil QR: " . $e->getMessage());
        }

        return [
            'success' => false,
            'status'  => 'disconnected',
            'qr'      => null,
            'message' => 'Gagal mengambil QR Code. Pastikan service berjalan.',
        ];
    }

    /**
     * Memutuskan koneksi sesi WhatsApp (Logout & Clean Session).
     */
    public function disconnect(): array
    {
        Cache::forget('whatsapp_gateway_status');

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/disconnect");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("[WhatsAppService] Gagal memutuskan sesi: " . $e->getMessage());
        }

        return [
            'success' => false,
            'message' => 'Gagal menghubungi service untuk memutuskan koneksi.',
        ];
    }

    /**
     * Mengirim notifikasi instan saat pengajuan baru (Cuti, CAR, MPR) diajukan.
     */
    public function sendNewSubmissionNotification(string $type, $pengajuan, User $approver, int $tahap = 1): array
    {
        if (empty($approver->phone_number) || empty($approver->phone_verified_at)) {
            return [
                'success' => false,
                'message' => "Approver {$approver->name} belum memiliki nomor WhatsApp yang terverifikasi.",
            ];
        }

        $pemohon = $pengajuan->user;
        $namaStation = $pemohon->station->name ?? 'Kantor Pusat / Utama';
        $tanggalPengajuan = Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y H:i');

        $typeUpper = strtoupper($type);
        $tahapLabel = "TAHAP {$tahap}";

        $detailText = '';
        $reviewUrl = url('/dashboard');

        if ($type === 'cuti') {
            $perihal = $pengajuan->sub_cuti_id && $pengajuan->subCuti 
                ? $pengajuan->subCuti->nama_sub_cuti 
                : ($pengajuan->jenisCuti->name_cuti ?? 'Cuti/Izin');
            $detailText = "▪ *Jenis:* {$perihal}\n"
                . "▪ *Rentang:* {$pengajuan->tanggal_mulai} s/d {$pengajuan->tanggal_selesai} ({$pengajuan->total_hari} Hari)\n"
                . "▪ *Alasan:* " . ($pengajuan->alasan_cuti ?: '-');
            $reviewUrl = route('admin.persetujuan.cuti');
        } elseif ($type === 'car') {
            $totalEst = 'Rp ' . number_format($pengajuan->details->sum('total_harga') ?? 0, 0, ',', '.');
            $detailText = "▪ *Alasan Pembelian:* " . ($pengajuan->alasan_pembelian ?: '-') . "\n"
                . "▪ *Jumlah Item:* " . $pengajuan->details->count() . " Barang\n"
                . "▪ *Total Estimasi:* {$totalEst}";
            $reviewUrl = route('admin.persetujuan.car');
        } elseif ($type === 'mpr') {
            $totalEst = 'Rp ' . number_format($pengajuan->items->sum('estimasi_harga') ?? 0, 0, ',', '.');
            $detailText = "▪ *Nomor MPR:* {$pengajuan->nomor_mpr}\n"
                . "▪ *Keperluan Urgensi:* " . ($pengajuan->keperluan_urgensi ?: '-') . "\n"
                . "▪ *Jumlah Item:* " . $pengajuan->items->count() . " Barang";
            $reviewUrl = route('admin.persetujuan.mpr');
        }

        $message = "📋 *PENGAJUAN {$typeUpper} BARU MEMERLUKAN PERSETUJUAN ({$tahapLabel})*\n\n"
            . "Halo Bapak/Ibu *{$approver->name}*,\n"
            . "Terdapat dokumen pengajuan baru yang menunggu peninjauan dan persetujuan Anda:\n\n"
            . "▪ *Nama Karyawan:* {$pemohon->name}\n"
            . "▪ *NIP:* " . ($pemohon->nip ?? '-') . "\n"
            . "▪ *Station:* {$namaStation}\n"
            . "▪ *Waktu Pengajuan:* {$tanggalPengajuan} WIB\n"
            . "{$detailText}\n\n"
            . "Silakan tinjau dan tindak lanjuti pengajuan ini melalui sistem ERP:\n"
            . "🔗 {$reviewUrl}\n\n"
            . "_Pesan notifikasi otomatis Sistem ERP META Adhya Tirta Umbulan._";

        return $this->sendMessage($approver->phone_number, $message);
    }

    /**
     * Mengirim notifikasi pengingat / follow-up scheduler ke approver.
     */
    public function sendFollowUpNotification(string $type, $pengajuan, User $approver, int $tahap = 1): array
    {
        if (empty($approver->phone_number) || empty($approver->phone_verified_at)) {
            return [
                'success' => false,
                'message' => "Approver {$approver->name} tidak memiliki nomor WhatsApp valid.",
            ];
        }

        $pemohon = $pengajuan->user;
        $namaStation = $pemohon->station->name ?? 'Kantor Pusat / Utama';
        $tanggalPengajuan = Carbon::parse($pengajuan->created_at)->translatedFormat('d F Y H:i');

        $typeUpper = strtoupper($type);
        $tahapLabel = "TAHAP {$tahap}";

        $detailText = '';
        $reviewUrl = url('/dashboard');

        if ($type === 'cuti') {
            $perihal = $pengajuan->sub_cuti_id && $pengajuan->subCuti 
                ? $pengajuan->subCuti->nama_sub_cuti 
                : ($pengajuan->jenisCuti->name_cuti ?? 'Cuti/Izin');
            $detailText = "▪ *Jenis:* {$perihal}\n"
                . "▪ *Rentang:* {$pengajuan->tanggal_mulai} s/d {$pengajuan->tanggal_selesai} ({$pengajuan->total_hari} Hari)\n"
                . "▪ *Alasan:* " . ($pengajuan->alasan_cuti ?: '-');
            $reviewUrl = route('admin.persetujuan.cuti');
        } elseif ($type === 'car') {
            $totalEst = 'Rp ' . number_format($pengajuan->details->sum('total_harga') ?? 0, 0, ',', '.');
            $detailText = "▪ *Alasan:* " . ($pengajuan->alasan_pembelian ?: '-') . "\n"
                . "▪ *Total Estimasi:* {$totalEst}";
            $reviewUrl = route('admin.persetujuan.car');
        } elseif ($type === 'mpr') {
            $detailText = "▪ *Nomor MPR:* {$pengajuan->nomor_mpr}\n"
                . "▪ *Keperluan:* " . ($pengajuan->keperluan_urgensi ?: '-');
            $reviewUrl = route('admin.persetujuan.mpr');
        }

        $message = "⏳ *PENGINGAT: PENGAJUAN {$typeUpper} MENUNGGU PERSETUJUAN ({$tahapLabel})*\n\n"
            . "Halo Bapak/Ibu *{$approver->name}*,\n"
            . "Pengajuan berikut masih berstatus *PENDING* dan menunggu tindakan persetujuan Anda:\n\n"
            . "▪ *Nama Karyawan:* {$pemohon->name}\n"
            . "▪ *NIP:* " . ($pemohon->nip ?? '-') . "\n"
            . "▪ *Station:* {$namaStation}\n"
            . "▪ *Diajukan Pada:* {$tanggalPengajuan} WIB\n"
            . "{$detailText}\n\n"
            . "Mohon kesediaan Anda untuk segera meninjau dokumen ini pada tautan berikut:\n"
            . "🔗 {$reviewUrl}\n\n"
            . "_Pesan pengingat otomatis Sistem ERP META Adhya Tirta Umbulan._";

        return $this->sendMessage($approver->phone_number, $message);
    }

    /**
     * Mengirimkan kode OTP pemulihan kata sandi via WhatsApp ke nomor telepon pengguna.
     */
    public function sendPasswordResetOtp(User $user, string $otp, int $expiryMinutes = 5): array
    {
        if (empty($user->phone_number)) {
            return [
                'success' => false,
                'message' => "Pengguna {$user->name} belum memiliki nomor WhatsApp terdaftar.",
            ];
        }

        $message = "*[PORTAL RESMI PT META ADHYA TIRTA UMBULAN]*\n\n"
            . "Yth. Bapak/Ibu *{$user->name}*,\n\n"
            . "Kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda.\n"
            . "Berikut adalah Kode Keamanan (OTP) Anda:\n\n"
            . "🔐 *{$otp}*\n\n"
            . "⚠️ *Penting:*\n"
            . "• Kode ini berlaku selama *{$expiryMinutes} menit*.\n"
            . "• Jangan pernah membagikan kode rahasia ini kepada siapa pun, termasuk pihak manajemen atau IT Support.\n"
            . "• Jika Anda tidak pernah meminta perubahan kata sandi, abaikan pesan ini atau segera hubungi IT Support.\n\n"
            . "_Pesan otomatis Keamanan Sistem ERP PT META Adhya Tirta Umbulan._";

        return $this->sendMessage($user->phone_number, $message);
    }
}

