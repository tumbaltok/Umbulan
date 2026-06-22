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
        $jenisCutiTahunanId = 1;

        $karyawan = User::where('status_aktif', true)->get();

        foreach ($karyawan as $user) {
            SaldoCuti::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'jenis_cuti_id' => $jenisCutiTahunanId,
                    'tahun' => $tahunDepan
                ],
                [
                    'sisa_saldo' => 12
                ]
            );
        }
        $this->info('Saldo tahunan berhasil dibuat untuk tahun ' . $tahunDepan);
    }
}
