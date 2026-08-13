<?php

namespace App\Http\Controllers\Mpr;

use App\Http\Controllers\Controller;
use App\Models\Mpr\PengajuanMpr;
use App\Models\Mpr\PengajuanMprDetail;
use App\Models\User\User;
use Barryvdh\DomPDF\Facade\Pdf;
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

    // KARYAWAN: Simpan Pengajuan MPR Baru (Bypass Direct)
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

        // Auto-approve jika yang mengajukan adalah Level Manajerial/Atasan Sendiri
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

            // Notifikasi WA ke Semua Atasan di Station Terkait
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

    // ATASAN & ADMIN: Menampilkan List Pengajuan Masuk (Model Bypass)
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $roleLevel = $atasan->role->level ?? 4;

        $query = PengajuanMpr::with(['user', 'items'])->orderBy('created_at', 'desc');

        // MODEL BYPASS: Semua Level Atasan (1, 2, dan 3) bisa melihat semua antrean MPR yang status_akhir-nya masih pending
        if (in_array($roleLevel, [1, 2, 3])) {
            $query->where('status_akhir', 'pending');

            // Khusus Supervisor (Level 3), batasi hanya staf di Station-nya sendiri
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

    // ATASAN & ADMIN: Memproses Aksi Setuju / Tolak (Bypass Direct)
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
            // Karena bersifat Bypass, siapa pun atasan yang menyetujui/menolak, status_akhir LANGSUNG BERUBAH
            if ($roleLevel == 3) { // Supervisor
                $pengajuan->update([
                    'status_supervisor' => $tindakan,
                    'supervisor_id' => $tindakan === 'approved' ? $atasan->id : null,
                    'status_manager' => $tindakan, // Bypass langsung setuju ke tingkat manager
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

    // CETAK PDF MPR
    public function cetakPdf(int $id)
    {
        $mpr = PengajuanMpr::with(['user.role', 'user.station', 'supervisor', 'manager', 'items'])->findOrFail($id);

        if ($mpr->status_akhir === 'rejected') {
            return redirect()->back()->with('error', 'Dokumen MPR yang ditolak tidak dapat dicetak.');
        }

        $data = [
            'mpr' => $mpr,
            'title' => 'Cetak MPR - '.$mpr->nomor_mpr,
        ];

        $pdf = Pdf::loadView('mpr.cetak', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('MPR-'.str_replace('/', '-', $mpr->nomor_mpr).'.pdf');
    }
}