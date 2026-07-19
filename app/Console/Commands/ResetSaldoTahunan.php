<?php

namespace App\Console\Commands;

use App\Models\SaldoCuti;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResetSaldoTahunan extends Command
{
    protected $signature = 'saldo:reset-tahunan';
    protected $description = 'Generate saldo cuti tahunan untuk tahun depan';

    public function handle()
    {
        $tahunDepan = Carbon::now()->addYear()->year;

        // Perbaikan 1: Gunakan konstanta terpusat dari Model User (ID: 4)
        $jenisCutiTahunanId = User::CUTI_TAHUNAN_ID;

        // Perbaikan 2: Ambil semua user (karena tidak ada kolom status_aktif di skema tabel user Anda)
        $karyawan = User::all();

        foreach ($karyawan as $user) {
            SaldoCuti::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'jenis_cuti_id' => $jenisCutiTahunanId,
                    'tahun' => $tahunDepan
                ],
                [
                    // Perbaikan 3: Masukkan kuota_awal sesuai blueprint migrasi
                    'kuota_awal' => 12,
                    'sisa_saldo' => 12
                ]
            );
        }

        $this->info('Saldo tahunan berhasil dibuat untuk tahun ' . $tahunDepan);
    }
}
