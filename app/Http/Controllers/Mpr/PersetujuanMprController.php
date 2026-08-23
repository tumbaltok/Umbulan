<?php

namespace App\Http\Controllers\Mpr;

use App\Http\Controllers\Controller;
use App\Models\Mpr\PengajuanMpr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersetujuanMprController extends Controller
{
    // ATASAN & ADMIN: Menampilkan List Pengajuan Masuk (Model Bypass)
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $roleLevel = $atasan->role->level ?? 4;

        $query = PengajuanMpr::with(['user', 'items'])->orderBy('created_at', 'desc');

        if (in_array($roleLevel, [1, 2, 3])) {
            $query->where('status_akhir', 'pending');

            if ($roleLevel == 3) {
                $query->whereHas('user', function ($q) use ($atasan) {
                    $q->where('station_id', $atasan->station_id);
                });
            }
        } else {
            abort(403, 'Akses Ditolak.');
        }

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan.persetujuanmpr', compact('daftarPengajuan'));
    }

    // ATASAN & ADMIN: Memproses Aksi Setuju / Tolak
    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string',
        ]);

        $atasan = Auth::user();
        $tindakan = $request->tindakan;
        $pengajuan = PengajuanMpr::findOrFail($id);
        $roleLevel = $atasan->role->level ?? 4;

        if (!in_array($roleLevel, [1, 2, 3])) {
            return redirect()->back()->with('error', 'Akses ditolak. Level akun Anda tidak mencukupi.');
        }

        DB::beginTransaction();
        try {
            if ($roleLevel == 3) { // Supervisor
                $pengajuan->update([
                    'status_supervisor' => $tindakan,
                    'supervisor_id' => $tindakan === 'approved' ? $atasan->id : null,
                    'status_manager' => $tindakan,
                    'status_akhir' => $tindakan,
                    'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null,
                ]);
            } else { // Manager / Admin / Direksi (Level 1 & 2)
                $pengajuan->update([
                    'status_supervisor' => $pengajuan->status_supervisor === 'pending' ? $tindakan : $pengajuan->status_supervisor,
                    'status_manager' => $tindakan,
                    'manager_id' => $tindakan === 'approved' ? $atasan->id : null,
                    'status_akhir' => $tindakan,
                    'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Status pengajuan MPR berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses persetujuan: '.$e->getMessage());
        }
    }
}
