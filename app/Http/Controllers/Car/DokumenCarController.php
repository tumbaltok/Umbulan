<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use Barryvdh\DomPDF\Facade\Pdf;

class CarDokumenController extends Controller
{
    public function print(int $id)
    {
        $car = PengajuanCar::with([
            'user.role',
            'details',
            'approverTahap1.role',
            'approverTahap2.role'
        ])->findOrFail($id);

        if ($car->status_akhir !== 'approved') {
            return redirect()->back()->with('error', 'Dokumen CAR belum disetujui secara penuh.');
        }

        $approverLevel1 = $car->approverTahap1;

        // Jika 1 level approval, samakan data penandatangan Tahap 2 dengan Tahap 1
        if ($car->status_tahap_2 === 'not_required' || empty($car->approver_tahap_2_id)) {
            $approverLevel2 = $approverLevel1;
        } else {
            $approverLevel2 = $car->approverTahap2;
        }

        $pdf = Pdf::loadView('car.carcetak', compact('car', 'approverLevel1', 'approverLevel2'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('CAR_' . sprintf('%03d', $car->id) . '.pdf');
    }
}
