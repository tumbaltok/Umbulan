<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\Cuti\PengajuanCuti;
use App\Traits\CutiHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersetujuanCutiController extends Controller
{
    use CutiHelperTrait;

    public function listAtasanView()
    {
        /** @var \App\Models\User\User $atasan */
        $atasan = Auth::user();
        $atasanRoleId = $atasan->role_id;
        $parentRoleId = $atasan->role ? $atasan->role->parent_role_id : null;

        $query = PengajuanCuti::query()
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->select(
                'pengajuan_cutis.*',
                'users.name as user_name',
                'jenis_cutis.name_cuti',
                'sub_cutis.nama_sub_cuti'
            )
            ->orderBy('pengajuan_cutis.created_at', 'desc');

        if (empty($parentRoleId)) {
            // Top Level / Director: Melihat pengajuan yang sudah di-approve Tahap 1 dan butuh persetujuan Tahap 2
            $query->where('pengajuan_cutis.status_tahap_1', 'approved')
                ->where('pengajuan_cutis.status_tahap_2', 'pending');
        } else {
            // Atasan Langsung (SPV): Hanya melihat pengajuan yang masih pending di Tahap 1
            $query->where('roles.parent_role_id', $atasanRoleId)
                ->where('pengajuan_cutis.status_tahap_1', 'pending');
        }

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan.persetujuancuti', compact('daftarPengajuan'));
    }

    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan'          => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string',
        ]);

        $atasan = Auth::user();
        $tindakan = $request->tindakan;
        $pengajuan = PengajuanCuti::findOrFail($id);

        // 1. PENOLAKAN PENGAJUAN
        if ($tindakan === 'rejected') {
            $pengajuan->update([
                'status_tahap_1'    => $pengajuan->status_tahap_1 === 'pending' ? 'rejected' : $pengajuan->status_tahap_1,
                'status_tahap_2'    => $pengajuan->status_tahap_2 === 'pending' ? 'rejected' : $pengajuan->status_tahap_2,
                'status_akhir'      => 'rejected',
                'catatan_penolakan' => $request->catatan_penolakan,
            ]);

            return redirect()->back()->with('success', 'Pengajuan cuti telah ditolak.');
        }

        // 2. PERSETUJUAN TAHAP 1
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

            if ($isSingleLevel) {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }

            return redirect()->back()->with('success', 'Persetujuan Cuti Tahap 1 berhasil diproses.');
        }

        // 3. PERSETUJUAN TAHAP 2 (FINAL)
        if ($pengajuan->status_tahap_2 === 'pending') {
            DB::beginTransaction();
            try {
                $pengajuan->update([
                    'status_tahap_2'      => 'approved',
                    'approver_tahap_2_id' => $atasan->id,
                    'status_akhir'        => 'approved',
                ]);

                $this->sinkronisasiCutiDanAbsen($pengajuan);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
            }

            return redirect()->back()->with('success', 'Persetujuan Cuti Tahap 2 (Final) berhasil diproses.');
        }

        return redirect()->back()->with('error', 'Pengajuan sudah diproses sebelumnya.');
    }
}
