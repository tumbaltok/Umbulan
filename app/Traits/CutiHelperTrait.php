<?php

namespace App\Traits;

use App\Models\Absen\Kehadiran;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\Cuti\SubCuti;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait CutiHelperTrait
{
    // Mendapatkan ID Jenis Cuti Tahunan secara andal
    public function getCutiTahunanId(): int
    {
        $cuti = JenisCuti::where('kode_cuti', 'CT')
            ->orWhere('id', User::CUTI_TAHUNAN_ID)
            ->orWhere('name_cuti', 'LIKE', '%Tahunan%')
            ->first();

        return $cuti ? $cuti->id : User::CUTI_TAHUNAN_ID;
    }

    // Evaluasi apakah jenis atau sub-cuti memotong saldo cuti tahunan
    public function alurPotongSaldo(int $jenisCutiId, ?int $subCutiId = null): bool
    {
        $jenis = JenisCuti::find($jenisCutiId);
        if (!$jenis) {
            return false;
        }

        $cutiTahunanId = $this->getCutiTahunanId();

        // Verifikasi apakah jenis cuti ini adalah Cuti Tahunan
        $isCutiTahunan = ($jenis->id === $cutiTahunanId)
            || ($jenis->kode_cuti === 'CT')
            || (strcasecmp($jenis->name_cuti, 'Cuti') === 0)
            || str_contains(strtolower($jenis->name_cuti), 'tahunan');

        if (!$isCutiTahunan) {
            return false;
        }

        // Pengecualian pemotongan saldo untuk sub-cuti khusus (misal: haid, duka, ibadah, dll)
        if ($subCutiId) {
            $sub = SubCuti::find($subCutiId);
            if ($sub) {
                $namaSub = strtolower($sub->nama_sub_cuti);
                if (
                    str_contains($namaSub, 'haid') ||
                    str_contains($namaSub, 'sakit') ||
                    str_contains($namaSub, 'ibadah') ||
                    str_contains($namaSub, 'haji') ||
                    str_contains($namaSub, 'umroh') ||
                    str_contains($namaSub, 'nikah') ||
                    str_contains($namaSub, 'lahir') ||
                    str_contains($namaSub, 'duka') ||
                    str_contains($namaSub, 'kematian')
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    // Validasi sisa kuota saldo efektif (Saldo database dikurangi total cuti pending)
    public function validasiDanCekSaldo(int $userId, int $jenisCutiId, ?int $subCutiId, int $tahun, int $totalHari): void
    {
        if (!$this->alurPotongSaldo($jenisCutiId, $subCutiId)) {
            return;
        }

        $cutiTahunanId = $this->getCutiTahunanId();

        $saldo = SaldoCuti::where('user_id', $userId)
            ->where('jenis_cuti_id', $cutiTahunanId)
            ->where('tahun', $tahun)
            ->first();

        if (!$saldo) {
            throw new \Exception('Sisa kuota cuti tahunan Anda belum diatur oleh admin.');
        }

        $sisaSaldoDatabase = (int) $saldo->sisa_saldo;

        // Hitung total hari cuti yang masih berstatus pending
        $queryPending = DB::table('pengajuan_cutis')
            ->where('user_id', $userId)
            ->where('jenis_cuti_id', $cutiTahunanId)
            ->where('status_akhir', 'pending');

        $totalCutiPending = (int) $queryPending->sum('total_hari');
        $saldoEfektif = $sisaSaldoDatabase - $totalCutiPending;

        // Tolak pengajuan jika saldo efektif tidak mencukupi
        if ($saldoEfektif <= 0 || $saldoEfektif < $totalHari) {
            throw new \Exception(
                $saldoEfektif <= 0
                    ? 'Maaf, sisa kuota jatah cuti tahunan Anda sudah habis (0 hari) atau seluruhnya sedang dalam antrean persetujuan.'
                    : "Sisa kuota jatah cuti tahunan Anda tidak mencukupi. Sisa efektif saat ini: {$saldoEfektif} hari, sedangkan Anda mengajukan {$totalHari} hari."
            );
        }
    }

    // Kurangi sisa saldo cuti pada database dengan penguncian baris (pessimistic locking)
    private function potongSaldoDatabase(PengajuanCuti $pengajuan): void
    {
        $cutiTahunanId = $this->getCutiTahunanId();

        $saldo = SaldoCuti::where('user_id', $pengajuan->user_id)
            ->where('jenis_cuti_id', $cutiTahunanId)
            ->where('tahun', Carbon::parse($pengajuan->tanggal_mulai)->year)
            ->lockForUpdate()
            ->first();

        if ($saldo) {
            $saldo->decrement('sisa_saldo', $pengajuan->total_hari);
        }
    }

    // Sinkronisasi status pengajuan yang disetujui ke pemotongan saldo dan tabel kehadiran
    public function sinkronisasiCutiDanAbsen(PengajuanCuti $pengajuan): void
    {
        $apakahMemotongSaldo = $this->alurPotongSaldo($pengajuan->jenis_cuti_id, $pengajuan->sub_cuti_id);

        if ($apakahMemotongSaldo) {
            $this->potongSaldoDatabase($pengajuan);
            $pengajuan->update(['is_cut_saldo' => true]);
        }

        $tanggalMulai = Carbon::parse($pengajuan->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($pengajuan->tanggal_selesai);

        // Buat atau perbarui catatan absensi berstatus Cuti pada setiap hari rentang tanggal
        for ($date = $tanggalMulai->copy(); $date->lte($tanggalSelesai); $date->addDay()) {
            Kehadiran::updateOrCreate(
                [
                    'user_id' => $pengajuan->user_id,
                    'date'    => $date->format('Y-m-d'),
                ],
                [
                    'shift_type'      => 'Cuti',
                    'reason_checkout' => 'Izin Cuti Disetujui',
                ]
            );
        }
    }
}
