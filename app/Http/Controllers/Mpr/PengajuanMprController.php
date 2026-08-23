<?php

namespace App\Http\Controllers\Mpr;

use App\Http\Controllers\Controller;
use App\Models\Mpr\PengajuanMpr;
use App\Models\Mpr\PengajuanMprDetail;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PengajuanMprController extends Controller
{
    // KARYAWAN: Menampilkan Riwayat Pengajuan MPR
    public function index()
    {
        $user = Auth::user();
        $riwayatMpr = PengajuanMpr::with('items')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mpr.mprriwayat', compact('riwayatMpr'));
    }

    // KARYAWAN: Tampilan Form Pengajuan MPR
    public function create()
    {
        return view('mpr.mprcreate');
    }

    // KIRIM WA NOTIFIKASI
    private function sendWhatsAppNotification(?string $targetPhone, string $message)
    {
        if (! $targetPhone) {
            return false;
        }
        $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (isset($cleanPhone[0]) && $cleanPhone[0] === '0') {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $cleanPhone,
                'message' => $message,
                'all' => 'true',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Gagal mengirim WA: '.$e->getMessage());

            return false;
        }
    }

    // KARYAWAN: Simpan Pengajuan MPR Baru
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
        $roleLevel = $user->role->level ?? 4;

        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $namaDokumen = $request->file('dokumen_pendukung')->store('dokumen_mpr', 'public');
        }

        $statusSupervisor = 'pending';
        $statusManager = 'pending';
        $statusAkhir = 'pending';
        $supervisorId = null;
        $managerId = null;

        if (in_array($roleLevel, [1, 2])) {
            $statusSupervisor = 'approved';
            $statusManager = 'approved';
            $statusAkhir = 'approved';
            $managerId = $user->id;
        } elseif ($roleLevel == 3) {
            $statusSupervisor = 'approved';
            $supervisorId = $user->id;
        }

        $nomorMpr = 'MPR/'.date('Y/m/').sprintf('%03d', PengajuanMpr::whereMonth('created_at', date('m'))->count() + 1);

        DB::beginTransaction();
        try {
            $mpr = PengajuanMpr::create([
                'user_id' => $user->id,
                'nomor_mpr' => $nomorMpr,
                'tanggal_pengajuan' => Carbon::now()->format('Y-m-d'),
                'keperluan_urgensi' => $request->keperluan_urgensi,
                'dokumen_pendukung' => $namaDokumen,
                'status_supervisor' => $statusSupervisor,
                'supervisor_id' => $supervisorId,
                'status_manager' => $statusManager,
                'manager_id' => $managerId,
                'status_akhir' => $statusAkhir,
            ]);

            foreach ($request->items as $item) {
                PengajuanMprDetail::create([
                    'pengajuan_mpr_id' => $mpr->id,
                    'nama_barang' => $item['nama_barang'],
                    'jumlah' => $item['jumlah'],
                    'satuan' => $item['satuan'],
                    'estimasi_harga' => $item['estimasi_harga'] ?? 0,
                    'keterangan_item' => $item['keterangan_item'] ?? null,
                ]);
            }

            DB::commit();

            if ($statusAkhir === 'pending') {
                $targetAtasan = User::where('station_id', $user->station_id)
                    ->whereHas('role', function ($query) {
                        $query->whereIn('level', [1, 2, 3]);
                    })
                    ->whereNotNull('phone_verified_at')
                    ->get();

                $namaStation = $user->station->name ?? 'Pusat / Utama';
                $templatePesan = "📢 *NOTIFIKASI PENGAJUAN MPR (Material Purchase Request)*\n\n"
                    ."Halo Bapak/Ibu Atasan,\n"
                    ."Terdapat pengajuan pembelian barang (MPR) baru yang membutuhkan persetujuan Anda.\n\n"
                    ."▪ *Nomor MPR:* {$nomorMpr}\n"
                    ."▪ *Nama Karyawan:* {$user->name}\n"
                    ."▪ *Station:* {$namaStation}\n"
                    ."▪ *Keperluan:* {$request->keperluan_urgensi}\n\n"
                    ."Silakan kelola pengajuan ini melalui menu *Persetujuan MPR* pada website.\n"
                    .'Link: '.url('/admin/persetujuan/mpr')."\n\n"
                    .'_Pesan otomatis sistem META AdhyaTirta Umbulan._';

                foreach ($targetAtasan as $atasan) {
                    if ($atasan->phone_number) {
                        $this->sendWhatsAppNotification($atasan->phone_number, $templatePesan);
                    }
                }
            }

            return redirect()->route('mpr.riwayat')->with('success', 'Pengajuan MPR berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menyimpan pengajuan MPR: '.$e->getMessage()])->withInput();
        }
    }
}
