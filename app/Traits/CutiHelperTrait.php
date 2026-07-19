<?php

namespace App\Traits;

use App\Models\SubCuti;
use App\Models\SaldoCuti;
use App\Models\Absensi;
use App\Models\PengajuanCuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait CutiHelperTrait
{
    /**
     * Mengecek apakah jenis cuti tertentu memotong saldo jatah cuti tahunan.
     */
    public function alurPotongSaldo(string $namaCutiUtama, $subCutiId = null)
    {
        if (str_contains(strtolower($namaCutiUtama), 'cuti')) {
            if ($subCutiId) {
                $sub = SubCuti::find($subCutiId);
                if ($sub) {
                    $namaSub = strtolower($sub->nama_sub_cuti);
                    if (str_contains($namaSub, 'haid') || str_contains($namaSub, 'ibadah') || str_contains($namaSub, 'haji') || str_contains($namaSub, 'umroh')) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Validasi sisa jatah saldo efektif (Database Saldo - Antrean Pending).
     *
     * @throws \Exception
     */
    public function validasiDanCekSaldo($userId, $jenisCutiId, $subCutiId, $tahun, $totalHari)
    {
        $saldo = SaldoCuti::where('user_id', $userId)
            ->where('jenis_cuti_id', $jenisCutiId)
            ->where('tahun', $tahun)
            ->first();

        $sisaSaldoDatabase = $saldo ? (int)$saldo->sisa_saldo : 0;

        $totalCutiPending = DB::table('pengajuan_cutis')
            ->where('user_id', $userId)
            ->where('jenis_cuti_id', $jenisCutiId)
            ->where('sub_cuti_id', $subCutiId)
            ->where('status_akhir', 'pending')
            ->sum('total_hari');

        $saldoEfektif = $sisaSaldoDatabase - $totalCutiPending;

        if ($saldoEfektif <= 0 || $saldoEfektif < $totalHari) {
            throw new \Exception(
                $saldoEfektif <= 0
                    ? "Sisa kuota jatah anda sudah habis atau sedang masuk antrean persetujuan."
                    : "Sisa kuota jatah anda tidak mencukupi. Sisa efektif saat ini: {$saldoEfektif} hari, Anda mengajukan {$totalHari} hari."
            );
        }
    }

    /**
     * Memotong saldo asli di database saat pengajuan otomatis disetujui (misal oleh Manager).
     */
    private function potongSaldoDatabase(PengajuanCuti $pengajuan)
    {
        $saldo = SaldoCuti::where('user_id', $pengajuan->user_id)
            ->where('jenis_cuti_id', $pengajuan->jenis_cuti_id)
            ->where('tahun', Carbon::parse($pengajuan->tanggal_mulai)->year)
            ->lockForUpdate()
            ->first();

        if ($saldo) {
            $saldo->decrement('sisa_saldo', $pengajuan->total_hari);
        }
    }

    /**
     * Menyinkronkan pemotongan saldo dan absensi jika cuti langsung disetujui.
     */
    private function sinkronisasiCutiDanAbsen(PengajuanCuti $pengajuan)
    {
        $namaCutiUtama = strtolower($pengajuan->jenisCuti->name_cuti ?? '');
        $apakahMemotongSaldo = $this->alurPotongSaldo($namaCutiUtama, $pengajuan->sub_cuti_id);

        if ($apakahMemotongSaldo) {
            $this->potongSaldoDatabase($pengajuan);
        }

        $tanggalMulai = Carbon::parse($pengajuan->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($pengajuan->tanggal_selesai);

        for ($date = $tanggalMulai->copy(); $date->lte($tanggalSelesai); $date->addDay()) {
            Absensi::updateOrCreate(
                [
                    'user_id' => $pengajuan->user_id,
                    'tanggal' => $date->format('Y-m-d')
                ],
                [
                    'status_kehadiran' => 'Cuti',
                    'keterangan' => 'Cuti disetujui: ' . $pengajuan->alasan_cuti,
                    'jam_masuk' => null,
                    'jam_pulang' => null
                ]
            );
        }
    }
}
