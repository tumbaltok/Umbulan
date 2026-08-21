<?php

namespace App\Traits;

use App\Models\Absen\Kehadiran;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\Cuti\SubCuti;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait CutiHelperTrait
{
    /**
     * Mengecek apakah jenis cuti tertentu memotong saldo jatah cuti tahunan.
     */
    public function alurPotongSaldo(int $jenisCutiId, ?int $subCutiId = null): bool
    {
        // Jika Jenis Cuti adalah CUTI TAHUNAN (ID: 4)
        if ($jenisCutiId === User::CUTI_TAHUNAN_ID) {
            return true;
        }

        // Cek jika ada sub-cuti khusus
        if ($subCutiId) {
            $sub = SubCuti::find($subCutiId);
            if ($sub) {
                $namaSub = strtolower($sub->nama_sub_cuti);
                if (str_contains($namaSub, 'haid') || str_contains($namaSub, 'ibadah') || str_contains($namaSub, 'haji') || str_contains($namaSub, 'umroh')) {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Validasi sisa jatah saldo efektif (Database Saldo - Antrean Pending).
     *
     * @throws \Exception
     */
    public function validasiDanCekSaldo(int $userId, int $jenisCutiId, ?int $subCutiId, int $tahun, int $totalHari): void
    {
        $saldo = SaldoCuti::where('user_id', $userId)
            ->where('jenis_cuti_id', $jenisCutiId)
            ->where('tahun', $tahun)
            ->first();

        $sisaSaldoDatabase = $saldo ? (int) $saldo->sisa_saldo : 0;

        // Menangani pencarian antrean pending saat sub_cuti_id null maupun berisi ID
        $queryPending = DB::table('pengajuan_cutis')
            ->where('user_id', $userId)
            ->where('jenis_cuti_id', $jenisCutiId)
            ->where('status_akhir', 'pending');

        if ($subCutiId) {
            $queryPending->where('sub_cuti_id', $subCutiId);
        } else {
            $queryPending->whereNull('sub_cuti_id');
        }

        $totalCutiPending = $queryPending->sum('total_hari');
        $saldoEfektif = $sisaSaldoDatabase - $totalCutiPending;

        if ($saldoEfektif <= 0 || $saldoEfektif < $totalHari) {
            throw new \Exception(
                $saldoEfektif <= 0
                    ? 'Maaf, sisa kuota jatah cuti Anda sudah habis (0 hari) atau seluruhnya sedang dalam antrean persetujuan.'
                    : "Sisa kuota jatah cuti Anda tidak mencukupi. Sisa efektif saat ini: {$saldoEfektif} hari, sedangkan Anda mengajukan {$totalHari} hari."
            );
        }
    }

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

    public function sinkronisasiCutiDanAbsen(PengajuanCuti $pengajuan)
    {
        $apakahMemotongSaldo = $this->alurPotongSaldo($pengajuan->jenis_cuti_id, $pengajuan->sub_cuti_id);

        if ($apakahMemotongSaldo) {
            $this->potongSaldoDatabase($pengajuan);
            $pengajuan->update(['is_cut_saldo' => true]);
        }

        $tanggalMulai = Carbon::parse($pengajuan->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($pengajuan->tanggal_selesai);

        for ($date = $tanggalMulai->copy(); $date->lte($tanggalSelesai); $date->addDay()) {
            Kehadiran::updateOrCreate(
                [
                    'user_id' => $pengajuan->user_id,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'shift_type' => 'Cuti',
                    'reason_checkout' => 'Izin Cuti Disetujui',
                ]
            );
        }
    }
}
