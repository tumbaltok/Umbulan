<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanMpr;
use App\Models\PengajuanMprDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

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

        return view('mpr.riwayat', compact('riwayatMpr'));
    }

    // KARYAWAN: Tampilan Form Pengajuan MPR
    public function create()
    {
        return view('mpr.create');
    }

    // KIRIM WA NOTIFIKASI
    private function sendWhatsAppNotification(?string $targetPhone, string $message)
    {
        if (!$targetPhone) return false;
        $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (isset($cleanPhone[0]) && $cleanPhone[0] === '0') {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $cleanPhone,
                'message' => $message,
                'all' => 'true'
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Gagal mengirim WA: " . $e->getMessage());
            return false;
        }
    }

    // KARYAWAN: Simpan Pengajuan MPR Baru
    public function store(Request $request)
    {
        $request->validate([
            'keperluan_urgensi' => 'required|string',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'items'             => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string',
            'items.*.jumlah'    => 'required|numeric|min:1',
            'items.*.satuan'    => 'required|string',
        ]);

        $user = Auth::user();
        
        // Upload Dokumen
        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $namaDokumen = $request->file('dokumen_pendukung')->store('dokumen_mpr', 'public');
        }

        // Penentuan Status Berdasarkan Role Pengaju
        $roleName = strtolower($user->role->role_name ?? '');
        $statusSupervisor = 'pending';
        $statusManager    = 'pending';
        $statusAkhir      = 'pending';

        if ($roleName === 'manager') {
            $statusSupervisor = 'approved';
            $statusManager    = 'approved';
            $statusAkhir      = 'approved';
        } elseif ($roleName === 'supervisor') {
            $statusSupervisor = 'approved';
            $statusManager    = 'pending';
            $statusAkhir      = 'pending';
        }

        // Generate Nomor Surat MPR
        $nomorMpr = 'MPR/' . date('Y/m/') . sprintf("%03d", PengajuanMpr::whereMonth('created_at', date('m'))->count() + 1);

        DB::beginTransaction();
        try {
            $mpr = PengajuanMpr::create([
                'user_id' => $user->id,
                'nomor_mpr' => $nomorMpr,
                'tanggal_pengajuan' => Carbon::now()->format('Y-m-d'),
                'keperluan_urgensi' => $request->keperluan_urgensi,
                'dokumen_pendukung' => $namaDokumen,
                'status_supervisor' => $statusSupervisor,
                'status_manager' => $statusManager,
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

            // Notifikasi WhatsApp ke Atasan
            if ($statusAkhir === 'pending') {
                $targetAtasan = User::where('station_id', $user->station_id)
                    ->whereHas('role', function($query) {
                        $query->whereIn(DB::raw('LOWER(role_name)'), ['supervisor', 'manager']);
                    })
                    ->whereNotNull('phone_verified_at')
                    ->get();

                $namaStation = $user->station->name ?? 'Pusat / Utama';
                $templatePesan = "📢 *NOTIFIKASI PENGAJUAN MPR (Material Purchase Request)*\n\n"
                    . "Halo Bapak/Ibu Atasan,\n"
                    . "Terdapat pengajuan pembelian barang (MPR) baru yang membutuhkan persetujuan Anda.\n\n"
                    . "▪ *Nomor MPR:* {$nomorMpr}\n"
                    . "▪ *Nama Karyawan:* {$user->name}\n"
                    . "▪ *Station:* {$namaStation}\n"
                    . "▪ *Keperluan:* {$request->keperluan_urgensi}\n\n"
                    . "Silakan kelola pengajuan ini melalui menu *Persetujuan MPR* pada website.\n"
                    . "Link: " . url('/admin/persetujuan/mpr') . "\n\n"
                    . "_Pesan otomatis sistem META AdhyaTirta Umbulan._";

                foreach ($targetAtasan as $atasan) {
                    if ($atasan->phone_number) {
                        $this->sendWhatsAppNotification($atasan->phone_number, $templatePesan);
                    }
                }
            }

            return redirect()->route('mpr.riwayat')->with('success', 'Pengajuan MPR berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan pengajuan MPR: ' . $e->getMessage()])->withInput();
        }
    }

    // ATASAN: Menampilkan List Pengajuan Masuk
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $roleName = strtolower($atasan->role->role_name ?? '');

        $query = PengajuanMpr::with(['user', 'items'])->orderBy('created_at', 'desc');

        if ($roleName === 'supervisor') {
            $query->where('status_supervisor', 'pending')
                ->whereHas('user', function($q) use ($atasan) {
                    $q->where('station_id', $atasan->station_id);
                });
        } elseif ($roleName === 'manager') {
            $query->where('status_manager', 'pending')
                ->where('status_supervisor', 'approved');
        } elseif ($roleName === 'admin') {
            $query->where('status_akhir', 'pending');
        } else {
            abort(403, 'Akses Ditolak.');
        }

        $daftarPengajuan = $query->get();
        return view('admin.persetujuan.mpr', compact('daftarPengajuan'));
    }

    // ATASAN: Memproses Aksi Setuju / Tolak
    public function prosesPersetujuan(Request $request, $id)
    {
        $request->validate([
            'tindakan' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string'
        ]);

        $atasan = Auth::user();
        $tindakan = $request->tindakan;
        $pengajuan = PengajuanMpr::findOrFail($id);
        $roleName = strtolower($atasan->role->role_name ?? '');

        if ($roleName === 'supervisor') {
            $pengajuan->update([
                'status_supervisor' => $tindakan,
                'status_akhir' => $tindakan === 'rejected' ? 'rejected' : 'pending',
                'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
            ]);
        } elseif ($roleName === 'manager') {
            $pengajuan->update([
                'status_manager' => $tindakan,
                'status_akhir' => $tindakan,
                'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
            ]);
        }

        return redirect()->back()->with('success', 'Status pengajuan MPR berhasil diperbarui.');
    }

    public function cetakPdf(int $id)
    {
        $mpr = PengajuanMpr::with(['user.role', 'user.station', 'items'])->findOrFail($id);

        // Amankan agar cetak PDF hanya untuk pengajuan yang sudah disetujui (opsional)
        if ($mpr->status_akhir === 'rejected') {
            return redirect()->back()->with('error', 'Dokumen MPR yang ditolak tidak dapat dicetak.');
        }

        $data = [
            'mpr' => $mpr,
            'title' => 'Cetak MPR - ' . $mpr->nomor_mpr
        ];

        $pdf = Pdf::loadView('mpr.cetak', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('MPR-' . str_replace('/', '-', $mpr->nomor_mpr) . '.pdf');
    }
}