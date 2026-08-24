<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\Cuti\PengajuanCuti;
use App\Models\User\User;
use App\Traits\CutiHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersetujuanCutiController extends Controller
{
    use CutiHelperTrait;

    // Menampilkan List Pengajuan Masuk Atasan (Web View)
    public function listAtasanView()
    {
        $atasan = Auth::user();
        $atasanRole = $atasan->role;

        $query = DB::table('pengajuan_cutis')
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->select(
                'pengajuan_cutis.*',
                'users.name as user_name',
                'jenis_cutis.name_cuti',
                'sub_cutis.nama_sub_cuti',
                'users.station_id'
            )
            ->orderBy('pengajuan_cutis.created_at', 'desc');

        // Jika Top Level (BOD / Direksi tanpa parent), tampilkan seluruh pengajuan pending
        if (empty($atasanRole->parent_role_id)) {
            $query->where('pengajuan_cutis.status_akhir', 'pending');
        } else {
            $atasanTreeCode = $atasanRole->tree_code;

            $query->where(function ($q) use ($atasan, $atasanRole, $atasanTreeCode) {
                // 1. ANTREAN TAHAP 1: Bawahan langsung pada rantai komandonya
                $q->where(function ($sub) use ($atasan, $atasanRole, $atasanTreeCode) {
                    $sub->where('pengajuan_cutis.status_tahap_1', 'pending')
                        ->where(function ($uq) use ($atasan, $atasanRole, $atasanTreeCode) {
                            $uq->where('users.atasan_langsung_id', $atasan->id)
                               ->orWhere(function ($rq) use ($atasanTreeCode) {
                                   $rq->where('roles.tree_code', 'LIKE', $atasanTreeCode . '.%')
                                      ->whereRaw("LENGTH(roles.tree_code) - LENGTH(REPLACE(roles.tree_code, '.', '')) = ?", [substr_count($atasanTreeCode, '.') + 1]);
                               });
                        });

                    // Cek Aturan Validasi Stasiun Kerja Level 1
                    $reqStationLvl1 = $atasanRole->approval_rules['require_same_station_level_1'] ?? ($atasanRole->approval_rules['require_same_station'] ?? false);
                    if ($reqStationLvl1) {
                        $sub->where('users.station_id', $atasan->station_id);
                    }
                })
                // 2. ANTREAN TAHAP 2: Bawahan 2 tingkat pada rantai komandonya
                ->orWhere(function ($sub) use ($atasan, $atasanRole, $atasanTreeCode) {
                    $sub->where('pengajuan_cutis.status_tahap_1', 'approved')
                        ->where('pengajuan_cutis.status_tahap_2', 'pending')
                        ->where(function ($uq) use ($atasan, $atasanTreeCode) {
                            $uq->where('users.atasan_dua_id', $atasan->id)
                               ->orWhere('roles.tree_code', 'LIKE', $atasanTreeCode . '.%');
                        });

                    // Cek Aturan Validasi Stasiun Kerja Level 2
                    $reqStationLvl2 = $atasanRole->approval_rules['require_same_station_level_2'] ?? false;
                    if ($reqStationLvl2) {
                        $sub->where('users.station_id', $atasan->station_id);
                    }
                });
            });
        }

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan.persetujuancuti', compact('daftarPengajuan'));
    }

    // Memproses Aksi Penyetujuan Bertingkat (Web View)
    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan'          => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string',
        ]);

        $atasan = Auth::user();
        $tindakan = $request->tindakan;
        $pengajuan = PengajuanCuti::findOrFail($id);

        // A. PENOLAKAN
        if ($tindakan === 'rejected') {
            $pengajuan->update([
                'status_tahap_1'    => $pengajuan->status_tahap_1 === 'pending' ? 'rejected' : $pengajuan->status_tahap_1,
                'status_tahap_2'    => $pengajuan->status_tahap_2 === 'pending' ? 'rejected' : $pengajuan->status_tahap_2,
                'status_akhir'      => 'rejected',
                'catatan_penolakan' => $request->catatan_penolakan,
            ]);

            return redirect()->back()->with('success', 'Pengajuan cuti karyawan telah ditolak.');
        }

        // B. PERSETUJUAN TAHAP 1
        if ($pengajuan->status_tahap_1 === 'pending') {
            $pemohonUser = User::find($pengajuan->user_id);
            $userRole = $pemohonUser ? $pemohonUser->role : null;
            $approvalRules = $userRole->approval_rules ?? [];
            $requiredLevels = (int) ($approvalRules['approval_levels'] ?? 1);

            $updateData = [
                'status_tahap_1'      => 'approved',
                'approver_tahap_1_id' => $atasan->id,
            ];

            // Jika pengajuan HANYA butuh 1 tingkat approval -> LANGSUNG APPROVED AKHIR
            if ($requiredLevels === 1) {
                $updateData['status_tahap_2'] = 'not_required';
                $updateData['status_akhir']   = 'approved';
            }

            $pengajuan->update($updateData);

            if (($updateData['status_akhir'] ?? '') === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }

            return redirect()->back()->with('success', 'Persetujuan Cuti Tahap 1 berhasil diproses.');
        }

        // C. PERSETUJUAN TAHAP 2 (FINAL)
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
