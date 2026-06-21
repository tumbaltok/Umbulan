<?php

namespace App\Traits;

use App\Models\SubCuti;
use App\Models\SaldoCuti;
use App\Models\Absensi;
use App\Models\PengajuanCuti;
use Carbon\Carbon;

trait CutiHelperTrait
{
    private function cekApakahMemotongSaldo(string $namaCutiUtama, $subCutiId = null)
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

    private function sinkronisasiCutiDanAbsen(PengajuanCuti $pengajuan)
    {
        $namaCutiUtama = strtolower($pengajuan->jenisCuti->name_cuti ?? '');
        $apakahMemotongSaldo = $this->cekApakahMemotongSaldo($namaCutiUtama, $pengajuan->sub_cuti_id);

        if ($apakahMemotongSaldo) {
            $saldo = SaldoCuti::where('user_id', $pengajuan->user_id)
                ->where('jenis_cuti_id', $pengajuan->jenis_cuti_id)
                ->where('tahun', Carbon::parse($pengajuan->tanggal_mulai)->year)
                ->first();

            if ($saldo) {
                $saldo->decrement('sisa_saldo', $pengajuan->total_hari);
            }
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
