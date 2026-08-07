<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\CutiHelperTrait;

class CutiPersetujuanController extends Controller
{
    use CutiHelperTrait;

    // Menampilkan List Pengajuan Masuk Atasan (Web View)
    public function listAtasanView()
    {
        $user = Auth::user();
        $query = DB::table('pengajuan_cutis')
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->select('pengajuan_cutis.*', 'users.name as user_name', 'jenis_cutis.name_cuti', 'sub_cutis.nama_sub_cuti', 'users.station_id')
            ->orderBy('pengajuan_cutis.created_at', 'desc');

        if ($user->role_id == 3) { // Supervisor
            $query->where('pengajuan_cutis.status_supervisor', 'pending')->where('users.station_id', $user->station_id);
        } elseif ($user->role_id == 2) { // Manager
            $query->where('pengajuan_cutis.status_manager', 'pending')->where('pengajuan_cutis.status_supervisor', 'approved');
        } else {
            $query->where('pengajuan_cutis.status_akhir', 'pending');
        }

        $daftarPengajuan = $query->get();
        return view('admin.persetujuan', compact('daftarPengajuan'));
    }

    // Memproses Aksi Penyetujuan Bertingkat (Web View)
    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string'
        ]);

        $user = Auth::user();
        $tindakan = $request->tindakan;
        $pengajuan = PengajuanCuti::findOrFail($id);

        if ($user->role_id == 3) { // Supervisor
            $pengajuan->update([
                'status_supervisor' => $tindakan,
                'status_akhir' => $tindakan === 'rejected' ? 'rejected' : 'pending',
                'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
            ]);
        } elseif ($user->role_id == 2) { // Manager
            if ($pengajuan->status_supervisor === 'rejected') {
                return redirect()->back()->with('error', 'Pengajuan sudah ditolak oleh Supervisor.');
            }

            $pengajuan->update([
                'status_manager' => $tindakan,
                'status_akhir' => $tindakan,
                'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
            ]);

            if ($tindakan === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }
        }

        return redirect()->back()->with('success', 'Status pengajuan cuti karyawan berhasil diperbarui!');
    }
}