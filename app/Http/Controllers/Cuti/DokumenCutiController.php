<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\Cuti\PengajuanCuti;
use Barryvdh\DomPDF\Facade\Pdf;

class DokumenCutiController extends Controller
{
    // Menampilkan halaman pratinjau surat cuti
    public function viewSuratCuti(int $id)
    {
        $pengajuan = PengajuanCuti::with(['user'])->findOrFail($id);

        if ($pengajuan->status_akhir !== 'approved') {
            return redirect()->back()->with('error', 'Surat cuti belum dapat dicetak karena belum disetujui sepenuhnya.');
        }

        return view('cuti.pembungkus_pdf', [
            'id' => $id,
            'title' => 'Surat Cuti - ' . $pengajuan->user->name,
        ]);
    }

    // Menghasilkan dan mengunduh berkas PDF surat cuti resmi
    public function cetakSuratCuti(int $id)
    {
        $pengajuan = PengajuanCuti::with([
            'user.role',
            'jenisCuti',
            'subCuti',
            'approverTahap1.role',
            'approverTahap2.role'
        ])->findOrFail($id);

        if ($pengajuan->status_akhir !== 'approved') {
            return redirect()->back()->with('error', 'Surat cuti belum dapat dicetak karena belum disetujui sepenuhnya.');
        }

        // Data approver tahap 1
        $approverLevel1 = $pengajuan->approverTahap1;

        // Jika hanya 1 level approval, atasan 2 disamakan dengan atasan 1
        if ($pengajuan->status_tahap_2 === 'not_required' || empty($pengajuan->approver_tahap_2_id)) {
            $approverLevel2 = $approverLevel1;
        } else {
            $approverLevel2 = $pengajuan->approverTahap2;
        }

        $pdf = Pdf::loadView('cuti.cetak', compact('pengajuan', 'approverLevel1', 'approverLevel2'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Cuti_' . str_replace(' ', '_', $pengajuan->user->name) . '.pdf');
    }
}
