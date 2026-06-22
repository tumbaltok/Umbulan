<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SaldoCuti;
use Carbon\Carbon;

class ResetSaldoHaidBulanan extends Command
{
    protected $signature = 'saldo:reset-haid';
    protected $description = 'Reset kuota izin haid bulanan karyawan otomatis';

    public function handle()
    {
        $bulanSekarang = Carbon::now()->month;
        $tahunSekarang = Carbon::now()->year;
        $jenisCutiIdHaid = 5; // Pastikan ID ini merujuk ke jenis cuti yang tepat

        // Update saldo untuk bulan & tahun berjalan
        SaldoCuti::where('jenis_cuti_id', $jenisCutiIdHaid)
            ->where('tahun', $tahunSekarang)
            ->where('bulan', $bulanSekarang)
            ->update(['sisa_saldo' => 2]);

        $this->info('Saldo haid bulanan berhasil di-reset!');
    }
}
