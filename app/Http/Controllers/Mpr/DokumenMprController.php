<?php

namespace App\Http\Controllers\Mpr;

use App\Http\Controllers\Controller;
use App\Models\Mpr\PengajuanMpr;
use Barryvdh\DomPDF\Facade\Pdf;

class MprDokumenController extends Controller
{
    // CETAK PDF MPR
    public function cetakPdf(int $id)
    {
        $mpr = PengajuanMpr::with(['user.role', 'user.station', 'supervisor', 'manager', 'items'])->findOrFail($id);

        if ($mpr->status_akhir === 'rejected') {
            return redirect()->back()->with('error', 'Dokumen MPR yang ditolak tidak dapat dicetak.');
        }

        $data = [
            'mpr' => $mpr,
            'title' => 'Cetak MPR - '.$mpr->nomor_mpr,
        ];

        $pdf = Pdf::loadView('mpr.mprcetak', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('MPR-'.str_replace('/', '-', $mpr->nomor_mpr).'.pdf');
    }
}
