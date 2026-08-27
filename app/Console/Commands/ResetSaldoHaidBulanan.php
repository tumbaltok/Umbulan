<?php

namespace App\Console\Commands;

use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResetSaldoHaidBulanan extends Command
{
    protected $signature = 'saldo:reset-haid';

    protected $description = 'Reset kuota cuti haid bulanan karyawan perempuan otomatis (2 hari)';

    public function handle()
    {
        $bulanSekarang = Carbon::now()->month;
        $tahunSekarang = Carbon::now()->year;

        // Ambil jenis cuti IMPI tempat SubCuti Haid bernaung
        $jenisCutiImpi = JenisCuti::where('kode_cuti', 'IMPI')
            ->orWhere('id', 1)
            ->first();

        $jenisCutiId = $jenisCutiImpi ? $jenisCutiImpi->id : 1;

        // Filter seluruh karyawan perempuan: gender_id = 2 atau nama gender Wanita/Perempuan
        $karyawanPerempuan = User::where(function ($q) {
            $q->where('gender_id', 2)
                ->orWhereHas('gender', function ($g) {
                    $g->whereIn('name', ['Wanita', 'Perempuan', 'wanita', 'perempuan', 'Female', 'female']);
                });
        })->get();

        $count = 0;
        foreach ($karyawanPerempuan as $user) {
            SaldoCuti::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'jenis_cuti_id' => $jenisCutiId,
                    'tahun' => $tahunSekarang,
                ],
                [
                    'kuota_awal' => 2,
                    'sisa_saldo' => 2,
                    'bulan' => $bulanSekarang,
                ]
            );
            $count++;
        }

        $this->info("Saldo haid bulanan berhasil di-reset menjadi 2 hari untuk {$count} karyawan perempuan pada bulan {$bulanSekarang}/{$tahunSekarang}!");

        return 0;
    }
}
