<?php

namespace App\Http\Controllers;

use App\Models\PengajuanCuti;
use Barryvdh\DomPDF\Facade\Pdf;

class CutiDokumenController extends Controller
{
    // Frame Halaman Preview Cetak (Web View)
    public function viewSuratCuti(int $id)
    {
        $pengajuan = PengajuanCuti::with(['user'])->findOrFail($id);

        if ($pengajuan->status_manager !== 'approved') {
            return redirect()->back()->with('error', 'Surat cuti belum dapat dicetak karena belum disetujui sepenuhnya.');
        }

        return view('cuti.pembungkus_pdf', [
            'id' => $id,
            'title' => 'Surat Cuti - ' . $pengajuan->user->name
        ]);
    }

    // Ekspor atau Download DomPDF (Stream PDF)
    public function cetakSuratCuti(int $id)
    {
        $pengajuan = PengajuanCuti::with(['user', 'jenisCuti', 'subCuti'])->findOrFail($id);

        if ($pengajuan->status_manager !== 'approved') {
            return redirect()->back()->with('error', 'Surat cuti belum dapat dicetak karena belum disetujui sepenuhnya.');
        }

        $pdf = Pdf::loadView('cuti.cetak', compact('pengajuan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Cuti_' . str_replace(' ', '_', $pengajuan->user->name) . '.pdf');
    }


}
