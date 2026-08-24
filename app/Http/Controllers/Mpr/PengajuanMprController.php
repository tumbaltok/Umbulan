<?php

namespace App\Http\Controllers\Mpr;

use App\Http\Controllers\Controller;
use App\Models\Mpr\PengajuanMpr;
use App\Models\Mpr\PengajuanMprDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengajuanMprController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $riwayatMpr = PengajuanMpr::with('items')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mpr.mprriwayat', compact('riwayatMpr'));
    }

    public function create()
    {
        return view('mpr.mprcreate');
    }

    public function store(Request $request)
    {
        $request->validate([
            'keperluan_urgensi' => 'required|string',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.satuan' => 'required|string',
        ]);

        $user = Auth::user();

        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $namaDokumen = $request->file('dokumen_pendukung')->store('dokumen_mpr', 'public');
        }

        if (empty($user->atasan_role_id)) {
            $statusTahap1 = 'approved';
            $statusTahap2 = 'approved';
            $statusAkhir  = 'approved';
        } else {
            $statusTahap1 = 'pending';
            $statusTahap2 = 'pending';
            $statusAkhir  = 'pending';
        }

        // Format nomor MPR: MPR/YYYY/MM/XXX
        $now = Carbon::now();
        $urutan = PengajuanMpr::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count() + 1;

        $nomorMpr = 'MPR/' . $now->format('Y/m/') . sprintf('%03d', $urutan);

        DB::beginTransaction();
        try {
            $mpr = PengajuanMpr::create([
                'user_id'           => $user->id,
                'nomor_mpr'         => $nomorMpr,
                'tanggal_pengajuan' => $now->format('Y-m-d'),
                'keperluan_urgensi' => $request->keperluan_urgensi,
                'dokumen_pendukung' => $namaDokumen,
                'status_tahap_1'    => $statusTahap1,
                'approver_tahap_1_id' => $statusTahap1 === 'approved' ? $user->id : null,
                'status_tahap_2'    => $statusTahap2,
                'approver_tahap_2_id' => $statusTahap2 === 'approved' ? $user->id : null,
                'status_akhir'      => $statusAkhir,
            ]);

            foreach ($request->items as $item) {
                PengajuanMprDetail::create([
                    'pengajuan_mpr_id' => $mpr->id,
                    'nama_barang'      => $item['nama_barang'],
                    'jumlah'           => $item['jumlah'],
                    'satuan'           => $item['satuan'],
                    'estimasi_harga'   => $item['estimasi_harga'] ?? 0,
                    'keterangan_item'  => $item['keterangan_item'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('mpr.riwayat')->with('success', 'Pengajuan MPR berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menyimpan pengajuan MPR: '.$e->getMessage()])->withInput();
        }
    }
}
