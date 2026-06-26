<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\PengajuanCuti;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Bawaan Laravel: Menampilkan quotes inspiratif
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Reset Saldo Haid otomatis setiap tanggal 1 awal bulan jam 00:00
Schedule::command('saldo:reset-haid')->monthlyOn(1, '00:00');

// 2. Reset Saldo Tahunan otomatis setiap tanggal 1 Januari jam 00:00
Schedule::command('saldo:reset-tahunan')->yearlyOn(1, 1, '00:00');


// 3. JADWAL: Pengingat WhatsApp Cuti Pending (Setiap 10 Menit di Jam Kerja)
Schedule::call(function () {
    // Ambil pengajuan yang secara keseluruhan masih 'pending'
    $pengajuanPending = PengajuanCuti::with(['user', 'jenisCuti', 'subCuti'])
        ->where('status_akhir', 'pending')
        ->get();

    if ($pengajuanPending->isEmpty()) {
        return;
    }

    foreach ($pengajuanPending as $pengajuan) {
        $user = $pengajuan->user;
        if (!$user) {
            continue;
        }

        // Tentukan Role Atasan mana yang harus dihubungi berdasarkan kondisi persetujuan
        $targetRole = null;

        // KONDISI 1: Jika Supervisor masih pending, maka targetnya adalah SUPERVISOR
        if ($pengajuan->status_supervisor === 'pending') {
            $targetRole = 'supervisor';
        }
        // KONDISI 2: Jika Supervisor sudah approve tapi Manager masih pending, targetnya adalah MANAGER
        elseif ($pengajuan->status_supervisor === 'approved' && $pengajuan->status_manager === 'pending') {
            $targetRole = 'manager';
        }

        // Jika tidak masuk dalam kedua kondisi di atas, lewati pengajuan ini
        if (!$targetRole) {
            continue;
        }

        // Cari atasan yang sesuai dengan targetRole di station yang sama
        $targetAtasan = User::where('station_id', $user->station_id)
            ->whereHas('role', function($query) use ($targetRole) {
                $query->where(DB::raw('LOWER(role_name)'), $targetRole);
            })
            ->whereNotNull('phone_verified_at')
            ->get();

        $namaStation = $user->station->nama_stasiun ?? 'Pusat / Utama';
        $perihal = $pengajuan->sub_cuti_id && $pengajuan->subCuti ? $pengajuan->subCuti->nama_sub_cuti : ($pengajuan->jenisCuti->name_cuti ?? 'Cuti/Izin');

        // Template Pesan WhatsApp (Disesuaikan dengan Jabatan Atasan saat ini)
        $labelAtasan = strtoupper($targetRole);
        $templatePesan = "⏳ *PENGINGAT: PENGAJUAN " . strtoupper($perihal) . " PERLU PERSETUJUAN {$labelAtasan}*\n\n"
            . "Halo Bapak/Ibu {$labelAtasan},\n"
            . "Mohon segera tinjau dokumen pengajuan berikut yang menunggu persetujuan Anda.\n\n"
            . "▪ *Nama Karyawan:* {$user->name}\n"
            . "▪ *NIP:* " . ($user->nip ?? '-') . "\n"
            . "▪ *Station:* {$namaStation}\n"
            . "▪ *Tanggal:* {$pengajuan->tanggal_mulai} s/d {$pengajuan->tanggal_selesai} ({$pengajuan->total_hari} Hari)\n"
            . "▪ *Alasan:* " . ($pengajuan->alasan_cuti ?? '-') . "\n\n"
            . "Silakan kelola pengajuan ini melalui menu *Persetujuan Cuti* pada website.\n"
            . "Link: " . url('/admin/persetujuan') . "\n\n"
            . "_Pesan pengingat otomatis sistem Tirta Umbulan._";

        // Kirim ke nomor HP masing-masing atasan hasil filter
        foreach ($targetAtasan as $atasan) {
            $targetPhone = $atasan->phone_number;
            if (!$targetPhone) {
                continue;
            }

            // Normalisasi nomor HP
            $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
            if (isset($cleanPhone[0]) && $cleanPhone[0] === '0') {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }

            try {
                Http::withHeaders([
                    'Authorization' => env('FONNTE_TOKEN'),
                ])->post('https://api.fonnte.com/send', [
                    'target' => $cleanPhone,
                    'message' => $templatePesan,
                    'all' => 'true'
                ]);
            } catch (\Exception $e) {
                Log::error("Gagal mengirim WA scheduler berjenjang: " . $e->getMessage());
            }
        }
    }
})
// ->everyMinute();
->everyTenMinutes()
->between('08:00', '16:00'); // Batasan Pukul (Setiap Hari)
