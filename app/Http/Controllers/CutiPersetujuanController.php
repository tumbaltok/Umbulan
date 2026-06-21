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

    // List Pengajuan Atasan (API)
    public function listPengajuanAtasan(Request $request)
    {
        $atasan = $request->user()->load('role');
        $roleName = strtolower($atasan->role->role_name ?? '');

        if ($roleName === 'supervisor') {
            $daftarCuti = PengajuanCuti::with(['user', 'jenisCuti', 'subCuti'])
                ->whereHas('user', function($query) use ($atasan) {
                    $query->where('station_id', $atasan->station_id);
                })
                ->where('status_supervisor', 'pending')->orderBy('created_at', 'asc')->get();

            return response()->json(['data' => $daftarCuti], 200);
        }

        if ($roleName === 'manager') {
            $daftarCuti = PengajuanCuti::with(['user', 'jenisCuti', 'subCuti'])
                ->where('status_supervisor', 'approved')->where('status_manager', 'pending')
                ->orderBy('created_at', 'asc')->get();

            return response()->json(['data' => $daftarCuti], 200);
        }

        return response()->json(['message' => 'Akses ditolak.'], 403);
    }

    // Approve/Reject (API)
    // ATASAN: Menyetujui atau Menolak Cuti (API)
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'aksi' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string'
        ]);

        $pengajuan = PengajuanCuti::with(['user', 'jenisCuti'])->findOrFail($id);
        $atasan = $request->user()->load('role');
        $roleName = strtolower($atasan->role->role_name ?? '');

        if ($roleName === 'supervisor') {
            if ($atasan->station_id !== $pengajuan->user->station_id) {
                return response()->json(['message' => 'Ditolak! Karyawan berbeda stasiun.'], 403);
            }

            $pengajuan->update([
                'status_supervisor' => $request->aksi,
                'status_akhir' => $request->aksi === 'rejected' ? 'rejected' : 'approved',
                'catatan_penolakan' => $request->aksi === 'rejected' ? $request->catatan_penolakan : null
            ]);
        }

        if ($roleName === 'manager') {
            if ($pengajuan->status_supervisor === 'rejected') {
                return response()->json(['message' => 'Ditolak! Sudah ditolak oleh Supervisor.'], 400);
            }
            if ($request->aksi === 'approved' && $pengajuan->status_supervisor === 'pending') {
                return response()->json(['message' => 'Ditolak! Menunggu persetujuan Supervisor.'], 400);
            }

            $pengajuan->update([
                'status_manager' => $request->aksi,
                'status_akhir' => $request->aksi,
                'catatan_penolakan' => $request->aksi === 'rejected' ? $request->catatan_penolakan : null
            ]);

            if ($request->aksi === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }
        }

        return response()->json(['message' => 'Status pengajuan berhasil diperbarui oleh ' . $roleName]);
    }
}
