<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersetujuanCarController extends Controller
{
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $atasanRoleId = $atasan->role_id;

        $query = PengajuanCar::with(['user.role', 'details'])
            ->orderBy('created_at', 'desc');

        if (empty($atasan->atasan_role_id)) {
            $query->where('status_akhir', 'pending');
        } else {
            $query->whereHas('user', function ($q) use ($atasanRoleId) {
                $q->where('atasan_role_id', $atasanRoleId);
            })
            ->where(function ($q) {
                $q->where('status_tahap_1', 'pending')
                ->orWhere(function ($sub) {
                    $sub->where('status_tahap_1', 'approved')
                        ->where('status_tahap_2', 'pending');
                });
            });
        }

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan.persetujuanCar', compact('daftarPengajuan'));
    }

    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan'          => 'required|in:approved,rejected',
            'catatan_penolakan' => 'required_if:tindakan,rejected|string|nullable',
        ]);

        $atasan = Auth::user();
        $pengajuan = PengajuanCar::findOrFail($id);
        $tindakan = $request->tindakan;

        if ($tindakan === 'rejected') {
            $pengajuan->update([
                'status_tahap_1'    => $pengajuan->status_tahap_1 === 'pending' ? 'rejected' : $pengajuan->status_tahap_1,
                'status_tahap_2'    => $pengajuan->status_tahap_2 === 'pending' ? 'rejected' : $pengajuan->status_tahap_2,
                'status_akhir'      => 'rejected',
                'catatan_penolakan' => $request->catatan_penolakan,
            ]);

            return redirect()->back()->with('success', 'Pengajuan CAR berhasil ditolak.');
        }

        if ($pengajuan->status_tahap_1 === 'pending') {
            $isSingleLevel = $pengajuan->status_tahap_2 === 'not_required';

            $updateData = [
                'status_tahap_1'      => 'approved',
                'approver_tahap_1_id' => $atasan->id,
            ];

            if ($isSingleLevel) {
                $updateData['status_akhir'] = 'approved';
            }

            $pengajuan->update($updateData);

            return redirect()->back()->with('success', 'Persetujuan CAR Tahap 1 berhasil diproses.');
        }

        if ($pengajuan->status_tahap_2 === 'pending') {
            $pengajuan->update([
                'status_tahap_2'      => 'approved',
                'approver_tahap_2_id' => $atasan->id,
                'status_akhir'        => 'approved',
            ]);

            return redirect()->back()->with('success', 'Persetujuan CAR Tahap 2 (Final) berhasil diproses.');
        }

        return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
    }
}
