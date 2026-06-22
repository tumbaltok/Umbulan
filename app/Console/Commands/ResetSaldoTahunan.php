<?php

namespace App\Console\Commands;

use App\Models\SaldoCuti;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:reset-saldo-tahunan')]
#[Description('Command description')]
class ResetSaldoTahunan extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tahunDepan = Carbon::now()->addYear()->year; // Menyiapkan jatah untuk tahun depan
        $jenisCutiTahunanId = 1; // ID Jenis Cuti Tahunan Anda di database

        // Ambil semua karyawan yang aktif
        $karyawanIds = User::where('status_aktif', true)->pluck('id');

        foreach ($karyawanIds as $userId) {
            // Cek dulu apakah saldo untuk tahun depan sudah dibuat atau belum (mencegah duplikat)
            $exists = SaldoCuti::where('user_id', $userId)
                ->where('jenis_cuti_id', $jenisCutiTahunanId)
                ->where('tahun', $tahunDepan)
                ->exists();

            if (!$exists) {
                // Tambahkan 12 hari jatah cuti baru untuk tahun depan
                SaldoCuti::create([
                    'user_id' => $userId,
                    'jenis_cuti_id' => $jenisCutiTahunanId,
                    'tahun' => $tahunDepan,
                    'sisa_saldo' => 12, // Kuota cuti tahunan
                ]);
            }
        }
    }
}
