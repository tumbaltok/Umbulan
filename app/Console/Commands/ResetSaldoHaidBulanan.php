<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\SaldoCuti;
use Carbon\Carbon;

#[Signature('app:reset-saldo-haid-bulanan')]
#[Description('Command description')]
class ResetSaldoHaidBulanan extends Command
{

    // Perintah yang dipanggil di terminal/scheduler
       protected $signature = 'saldo:reset-haid';
       protected $description = 'Reset atau tambah kuota izin bulanan karyawan otomatis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tahunSekarang = Carbon::now()->year;
           $jenisCutiIdIjin = 5; // Sesuaikan dengan ID Jenis Cuti "Ijin Meninggalkan Pekerjaan" Anda

           // Contoh aksi: Setel ulang sisa_saldo menjadi 2 hari untuk semua karyawan setiap bulan
           SaldoCuti::where('jenis_cuti_id', $jenisCutiIdIjin)
               ->where('tahun', $tahunSekarang)
               ->update(['sisa_saldo' => 2]);

           $this->info('Saldo ijin bulanan berhasil di-reset!');
    }
}
