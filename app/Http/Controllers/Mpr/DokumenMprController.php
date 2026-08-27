<?php

namespace App\Http\Controllers\Mpr;

use App\Http\Controllers\Controller;
use App\Models\Mpr\PengajuanMpr;
use App\Models\User\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DokumenMprController extends Controller
{
    // CETAK PDF MPR
    public function cetakPdf(int $id)
    {
        $mpr = PengajuanMpr::with(['user.role', 'user.station', 'supervisor', 'manager', 'items'])->findOrFail($id);

        if ($mpr->status_akhir === 'rejected') {
            return redirect()->back()->with('error', 'Dokumen MPR yang ditolak tidak dapat dicetak.');
        }

        // 1. Logo Perusahaan META
        $logoBase64 = null;
        $logoCandidates = [
            public_path('images/logo.png'),
            public_path('images/logo-circle.png'),
            public_path('images/iconfav.png'),
        ];
        foreach ($logoCandidates as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $ext = strtolower(pathinfo($cand, PATHINFO_EXTENSION));
                $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($cand));
                break;
            }
        }

        // 2. Data Pemohon & Departemen
        $requesterRole = $mpr->user->role->role_name ?? ($mpr->user->job_title ?? 'Supervisor');
        $departmentName = !empty($mpr->department) ? $mpr->department : 'Operation';

        // 3. Delivery Point
        $deliveryPoint = !empty($mpr->delivery_point) ? $mpr->delivery_point : 'Site Umbulan';

        // 4. Latest MPR Issued Date
        $latestMprDate = null;
        if (!empty($mpr->latest_mpr_date)) {
            $latestMprDate = Carbon::parse($mpr->latest_mpr_date)->translatedFormat('j F Y');
        } else {
            $latestMpr = PengajuanMpr::where('user_id', $mpr->user_id)
                ->where('id', '!=', $mpr->id)
                ->where('tanggal_pengajuan', '<=', $mpr->tanggal_pengajuan)
                ->where('status_akhir', 'approved')
                ->orderBy('tanggal_pengajuan', 'desc')
                ->first();

            $latestMprDate = $latestMpr
                ? Carbon::parse($latestMpr->tanggal_pengajuan)->translatedFormat('j F Y')
                : null;
        }

        // 5. Signers & Signatures
        $isApproved = ($mpr->status_akhir === 'approved');

        // A. Requester
        $requesterSignature = $this->imageToBase64($mpr->user->signature ?? null);

        // B. Operation Manager
        $operationManager = $mpr->approverTahap1 
            ?? $mpr->supervisor 
            ?? $mpr->user->supervisor 
            ?? User::whereHas('roles', fn($q) => $q->where('role_name', 'LIKE', '%OPERATIONAL%'))->first();
        
        $operationManagerName = $operationManager->name ?? 'Moch Anwar';
        $operationManagerSignature = ($isApproved || $mpr->status_tahap_1 === 'approved') 
            ? $this->imageToBase64($operationManager->signature ?? null) 
            : null;

        // C. Procurement
        $procurement = User::whereHas('roles', fn($q) => $q->where('role_name', 'LIKE', '%PROCUREMENT%'))->first();
        $procurementName = $procurement->name ?? 'Reki M.';
        $procurementSignature = $isApproved 
            ? $this->imageToBase64($procurement->signature ?? null) 
            : null;

        // D. Director
        $director = $mpr->approverTahap2 
            ?? $mpr->manager 
            ?? User::whereHas('roles', fn($q) => $q->where('role_name', 'LIKE', '%GENERAL MANAGER%')->orWhere('role_name', 'LIKE', '%DIRECTOR%'))->first();
        
        $directorName = $director->name ?? 'R. Herta Aridani';
        $directorSignature = ($isApproved || $mpr->status_tahap_2 === 'approved') 
            ? $this->imageToBase64($director->signature ?? null) 
            : null;

        // E. President Director / Executive
        $presdir = User::whereHas('roles', fn($q) => $q->where('role_name', 'LIKE', '%PRESIDENT%')->orWhere('role_name', 'LIKE', '%EXCECUTIVE%'))->first();
        $presidentDirectorName = $presdir->name ?? 'Yan Kuryana';
        $presidentDirectorSignature = $isApproved 
            ? $this->imageToBase64($presdir->signature ?? null) 
            : null;

        $data = [
            'mpr' => $mpr,
            'title' => 'Cetak MPR - ' . $mpr->nomor_mpr,
            'logoBase64' => $logoBase64,
            'requesterRole' => $requesterRole,
            'departmentName' => $departmentName,
            'deliveryPoint' => $deliveryPoint,
            'latestMprDate' => $latestMprDate,
            'isApproved' => $isApproved,
            'requesterSignature' => $requesterSignature,
            'operationManagerName' => $operationManagerName,
            'operationManagerSignature' => $operationManagerSignature,
            'procurementName' => $procurementName,
            'procurementSignature' => $procurementSignature,
            'directorName' => $directorName,
            'directorSignature' => $directorSignature,
            'presidentDirectorName' => $presidentDirectorName,
            'presidentDirectorSignature' => $presidentDirectorSignature,
        ];

        $pdf = Pdf::loadView('mpr.mprcetak', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('MPR-' . str_replace('/', '-', $mpr->nomor_mpr) . '.pdf');
    }

    private function imageToBase64(?string $relativePath): ?string
    {
        if (empty($relativePath)) {
            return null;
        }

        $possiblePaths = [
            public_path('storage/' . $relativePath),
            storage_path('app/public/' . $relativePath),
            public_path($relativePath),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_file($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp',
                    'gif' => 'image/gif',
                    default => 'image/png',
                };
                $data = file_get_contents($path);
                return 'data:' . $mime . ';base64,' . base64_encode($data);
            }
        }

        return null;
    }
}
