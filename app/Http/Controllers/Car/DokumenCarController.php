<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use App\Models\User\User;
use Barryvdh\DomPDF\Facade\Pdf;

class DokumenCarController extends Controller
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

        $director = User::whereHas('roles', function ($q) {
            $q->where('role_name', 'LIKE', '%PRESIDENT%')
              ->orWhere('role_name', 'LIKE', '%EXECUTIVE%')
              ->orWhere('role_name', 'LIKE', '%DIRECTOR%');
        })->first();

        // Siapkan Base64 Data URI untuk Logo & Tanda Tangan
        $logoBase64   = $this->imageToBase64(public_path('images/logo.png')) ?? $this->imageToBase64(public_path('images/iconfav.png'));
        $sigRequester = $car->user && $car->user->signature ? $this->imageToBase64(storage_path('app/public/' . $car->user->signature)) : null;
        $sigApprover1 = $approverLevel1 && $approverLevel1->signature ? $this->imageToBase64(storage_path('app/public/' . $approverLevel1->signature)) : null;
        $sigApprover2 = $approverLevel2 && $approverLevel2->signature ? $this->imageToBase64(storage_path('app/public/' . $approverLevel2->signature)) : null;
        $sigDirector  = $director && $director->signature ? $this->imageToBase64(storage_path('app/public/' . $director->signature)) : null;

        $pdf = Pdf::loadView('car.carcetak', compact(
            'car',
            'approverLevel1',
            'approverLevel2',
            'director',
            'logoBase64',
            'sigRequester',
            'sigApprover1',
            'sigApprover2',
            'sigDirector'
        ));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('CAR_' . sprintf('%03d', $car->id) . '.pdf');
    }

    private function imageToBase64(?string $path): ?string
    {
        if ($path && file_exists($path)) {
            $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($type === 'svg') {
                $type = 'svg+xml';
            }
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }
}
