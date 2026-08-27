<?php

namespace App\Console\Commands;

use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResetSaldoTahunan extends Command
{
    protected $signature = 'saldo:reset-tahunan {--year= : Tahun yang ingin di-generate/reset (default: tahun berjalan)}';

    protected $description = 'Generate atau reset saldo cuti tahunan (12 hari) untuk seluruh karyawan';

    public function handle()
    {
        $tahun = (int) ($this->option('year') ?: Carbon::now()->year);

        $jenisCutiTahunan = JenisCuti::where('kode_cuti', 'CT')
            ->orWhere('id', User::CUTI_TAHUNAN_ID)
            ->first();

        $jenisCutiTahunanId = $jenisCutiTahunan ? $jenisCutiTahunan->id : User::CUTI_TAHUNAN_ID;

        $karyawan = User::all();
        $count = 0;

        foreach ($karyawan as $user) {
            SaldoCuti::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'jenis_cuti_id' => $jenisCutiTahunanId,
                    'tahun' => $tahun,
                ],
                [
                    'kuota_awal' => 12,
                    'sisa_saldo' => 12,
                    'bulan' => null,
                ]
            );
            $count++;
        }

        $this->info("Saldo cuti tahunan (12 hari) berhasil diinisialisasi/reset untuk {$count} karyawan pada tahun {$tahun}.");

        return 0;
    }
}
