<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SaldoCuti;
use App\Models\User; // 1. Impor Model User
use Carbon\Carbon;

class ResetSaldoHaidBulanan extends Command
{
    protected $signature = 'saldo:reset-haid';
    protected $description = 'Reset kuota izin haid bulanan karyawan otomatis';

    public function handle()
    {
        $bulanSekarang = Carbon::now()->month;
        $tahunSekarang = Carbon::now()->year;

        // Ambil ID Cuti Haid dari database terlebih dahulu
        $jenisCutiHaid = \App\Models\JenisCuti::where('kode_cuti', 'HAID')
            ->orWhere('name_cuti', 'LIKE', '%Haid%')
            ->first();

        if (!$jenisCutiHaid) {
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
