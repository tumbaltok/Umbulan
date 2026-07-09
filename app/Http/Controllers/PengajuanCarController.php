<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCar;
use App\Models\DetailCar;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PengajuanCarController extends Controller
{
    // KARYAWAN: Melihat riwayat pengajuan CAR milik sendiri
    public function index()
    {
        $user = Auth::user();

        // Memuat relasi details agar data barang multi-item bisa dipanggil di view
        $riwayatCar = PengajuanCar::with('details')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('car.riwayat', compact('riwayatCar'));
    }

    // KARYAWAN: Menampilkan form pengajuan CAR baru
    public function create()
    {
        return view('car.create');
    }

    // KARYAWAN: Mengirim form pengajuan CAR baru (Multi-Item)
    public function store(Request $request)
    {
        // Validasi format array dari form multi-item baru
        $request->validate([
            'alasan_pembelian' => 'required|string',
            'receiving_account' => 'required|string|in:META Umbulan,META Surabaya,META Booster-M',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string|max:255',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.estimasi_harga' => 'required|numeric|min:0',
            'items.*.dokumen_pendukung' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $user = Auth::user();

        // Buat data utama (Header) CAR terlebih dahulu
        $carHeader = PengajuanCar::create([
            'user_id' => $user->id,
            'alasan_pembelian' => $request->alasan_pembelian,
            'receiving_account' => $request->receiving_account,
            'status_supervisor' => 'pending',
            'status_manager' => 'pending',
            'status_akhir' => 'pending',
        ]);

        // Loop dan simpan setiap item barang beserta file nota masing-masing
        foreach ($request->items as $index => $item) {
            $pathDokumen = null;
            if ($request->hasFile("items.{$index}.dokumen_pendukung")) {
                $file = $request->file("items.{$index}.dokumen_pendukung");
                $pathDokumen = $file->store('dokumen_car', 'public');
            }

            // Menghitung subtotal per item
            $total_harga_item = $item['jumlah'] * $item['estimasi_harga'];

            // Asumsi nama fungsi relasi di model PengajuanCar Anda adalah details()
            $carHeader->details()->create([
                'nama_barang' => $item['nama_barang'],
                'jumlah' => $item['jumlah'],
                'estimasi_harga' => $item['estimasi_harga'],
                'total_harga' => $total_harga_item,
                'dokumen_nota_or_proposal' => $pathDokumen,
            ]);
        }

        return redirect()->route('car.riwayat')->with('success', 'Pengajuan uang barang (CAR) multi-item berhasil diajukan.');
    }

    // ATASAN & ADMIN: Melihat daftar pengajuan masuk dari bawahan satu Station
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $roleName = $atasan->role ? strtolower($atasan->role->role_name) : '';

        // Memuat relasi 'user' dan 'details' (item barang) untuk ditampilkan di tabel persetujuan
        $query = PengajuanCar::with(['user', 'details'])
            ->whereHas('user', function($q) use ($atasan) {
                $q->where('station_id', $atasan->station_id)
                  ->where('id', '!=', $atasan->id);
            });

        // Filter berdasarkan role siapa yang berhak memproses saat ini
        if ($roleName === 'supervisor') {
            $query->where('status_supervisor', 'pending');
        } elseif ($roleName === 'manager') {
            $query->where('status_supervisor', 'approved')
                  ->where('status_manager', 'pending');
        } elseif ($roleName === 'admin') {
            // Admin bisa melihat seluruh antrean yang status akhirnya masih pending
            $query->where('status_akhir', 'pending');
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $daftarPengajuan = $query->get();

        return response()
            ->view('admin.persetujuan.car', compact('daftarPengajuan', 'roleName'))
            ->header('Content-Type', 'text/html')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    // ATASAN & ADMIN: Menyetujui atau Menolak Pengajuan CAR
    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'aksi' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'required_if:aksi,rejected|string|nullable'
        ]);

        $atasan = Auth::user();
        $pengajuan = PengajuanCar::findOrFail($id);

        // PERBAIKAN AMAN: Antisipasi jika kolom di tabel roles bernama 'name' atau 'role_name'
        $roleName = '';
        if ($atasan->role) {
            $roleName = strtolower($atasan->role->role_name ?? $atasan->role->name ?? '');
        }

        // Kasus: PENOLAKAN (Rejected)
        if ($request->aksi === 'rejected') {
            if ($roleName === 'supervisor') {
                $pengajuan->update([
                    'status_supervisor' => 'rejected',
                    'status_manager' => 'rejected',
                    'status_akhir' => 'rejected',
                    'catatan_penolakan' => $request->catatan_penolakan
                ]);
            } elseif ($roleName === 'manager') {
                $pengajuan->update([
                    'status_supervisor' => $pengajuan->status_supervisor ?? 'approved',
                    'status_manager' => 'rejected',
                    'status_akhir' => 'rejected',
                    'catatan_penolakan' => $request->catatan_penolakan
                ]);
            } else {
                $pengajuan->update([
                    'status_supervisor' => $pengajuan->status_supervisor === 'pending' ? 'rejected' : $pengajuan->status_supervisor,
                    'status_manager' => 'rejected',
                    'status_akhir' => 'rejected',
                    'catatan_penolakan' => $request->catatan_penolakan
                ]);
            }

            return redirect()->back()->with('success', 'Pengajuan CAR berhasil ditolak.');
        }

        // Kasus: PERSETUJUAN (Approved)
        if ($roleName === 'supervisor') {
            $pengajuan->update([
                'status_supervisor' => 'approved',
                'status_manager' => 'pending',
                'status_akhir' => 'pending'
            ]);
        } elseif ($roleName === 'manager') {
            $pengajuan->update([
                'status_supervisor' => 'approved',
                'status_manager' => 'approved',
                'status_akhir' => 'approved'
            ]);
        } elseif ($roleName === 'admin') {
            $pengajuan->update([
                'status_supervisor' => 'approved',
                'status_manager' => 'approved',
                'status_akhir' => 'approved'
            ]);
        } else {
            if ($pengajuan->status_supervisor === 'pending') {
                $pengajuan->update([
                    'status_supervisor' => 'approved'
                ]);
            } else {
                $pengajuan->update([
                    'status_supervisor' => 'approved',
                    'status_manager' => 'approved',
                    'status_akhir' => 'approved'
                ]);
            }
        }

        return redirect()->back()->with('success', 'Status pengajuan CAR berhasil diperbarui.');
    }

    public function print(int $id)
    {
        // Ambil data pengajuan beserta relasi detail barangnya
        $car = PengajuanCar::with('user.role', 'details')->findOrFail($id);

        // Load view khusus cetak dan set ukuran kertas A4 potrait
        $pdf = Pdf::loadView('car.cetak', compact('car'))
                  ->setPaper('a4', 'portrait');

        // Streaming langsung ke browser agar bisa diunduh atau dicetak
        return $pdf->stream('CAR-' . $car->id . '.pdf');
    }
}
