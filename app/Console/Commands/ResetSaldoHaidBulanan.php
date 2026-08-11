<?php

namespace App\Console\Commands;

use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResetSaldoHaidBulanan extends Command
{
    protected $signature = 'saldo:reset-haid';

    protected $description = 'Reset kuota izin haid bulanan karyawan otomatis';

    public function handle()
    {
        $bulanSekarang = Carbon::now()->month;
        $tahunSekarang = Carbon::now()->year;

        $jenisCutiHaid = JenisCuti::where('kode_cuti', 'HAID')
            ->orWhere('name_cuti', 'LIKE', '%Haid%')
            ->first();

        if (! $jenisCutiHaid) {
            $this->error('Jenis cuti Haid tidak ditemukan!');

            return 1;
        }

        SaldoCuti::where('jenis_cuti_id', $jenisCutiHaid->id)
            ->where('tahun', $tahunSekarang)
            ->where('bulan', $bulanSekarang)
            ->update(['sisa_saldo' => 2]);

        $this->info('Saldo haid bulanan berhasil di-reset!');

        return 0;
    }
}
