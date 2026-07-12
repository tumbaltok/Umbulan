<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCar;
use App\Models\DetailCar;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

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

        // 1. Inisialisasi query dasar beserta relasinya
        $query = PengajuanCar::with(['user.role', 'details']);

        // 2. Filter berdasarkan role siapa yang berhak memproses saat ini
        if ($roleName === 'supervisor') {
            $query->where('status_supervisor', 'pending')
                ->whereHas('user', function($q) use ($atasan) {
                    // Memperbaiki error station_id dengan mencari lewat relasi user
                    $q->where('station_id', $atasan->station_id);
                });
        } elseif ($roleName === 'manager') {
            $query->where('status_supervisor', 'approved')
                ->where('status_manager', 'pending');
        } elseif ($roleName === 'admin') {
            $query->where(function($q) {
                $q->where('status_supervisor', 'pending')
                ->orWhere('status_manager', 'pending');
            })
            ->where('status_supervisor', '!=', 'rejected')
            ->where('status_manager', '!=', 'rejected');
        } else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // 3. Eksekusi query yang sudah disaring oleh filter role di atas
        $daftarPengajuan = $query->get();

        return response()
            ->view('admin.persetujuan.car', compact('daftarPengajuan', 'roleName'))
            ->header('Content-Type', 'text/html')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    // ATASAN & ADMIN: Menyetujui atau Menolak Pengajuan CAR
    public function prosesPersetujuan(Request $request, int $id)
    {
        // 1. Validasi input aksi dan catatan penolakan
        $request->validate([
            'aksi' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'required_if:aksi,rejected|string|nullable'
        ]);

        $atasan = Auth::user();
        $aksi = $request->aksi;

        // Pastikan model yang dipanggil adalah PengajuanCar
        $pengajuan = PengajuanCar::findOrFail($id);

        // Ambil nama role dan paksa ke huruf kecil agar pencocokan string 100% akurat
        $roleName = $atasan->role ? strtolower($atasan->role->role_name) : '';

        // 2. Logika untuk Supervisor
        if ($roleName === 'supervisor') {
            $pengajuan->update([
                'status_supervisor' => $aksi,
                'status_akhir' => $aksi === 'rejected' ? 'rejected' : 'pending',
                'catatan_penolakan' => $aksi === 'rejected' ? $request->catatan_penolakan : null
            ]);
            return redirect()->back()->with('success', 'Status pengajuan CAR berhasil diperbarui');

        // 3. Logika untuk Manager
        } elseif ($roleName === 'manager') {
            if ($pengajuan->status_supervisor === 'rejected') {
                return redirect()->back()->with('error', 'Pengajuan sudah ditolak oleh Supervisor.');
            }
            if ($pengajuan->status_manager === 'approved') {
                return redirect()->back()->with('error', 'Pengajuan ini sudah disetujui sebelumnya.');
            }

            DB::beginTransaction();
            try {
                $pengajuan->update([
                    'status_manager' => $aksi,
                    'status_akhir' => $aksi,
                    'catatan_penolakan' => $aksi === 'rejected' ? $request->catatan_penolakan : null
                ]);

                // Catatan: Sinkronisasi cuti & absen dihapus karena ini konteksnya CAR (barang/perbaikan)

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
            }
            return redirect()->back()->with('success', 'Status pengajuan CAR berhasil diperbarui');

        // 4. Blok Pencegat Utama (Jika akun adalah Admin atau role lainnya)
        } else {
            // Ini akan mengembalikan pesan error merah ke halaman CAR Anda
            return redirect()->back()->with('error', 'Gagal! Anda tidak memiliki hak akses sebagai atasan untuk mengubah status ini.');
        }
    }

    public function print(int $id)
    {
        $car = PengajuanCar::with('user.role', 'details')->findOrFail($id);

        if ($car->status_manager !== 'approved') {
            return redirect()->back()->with('error', 'Dokumen CAR belum dapat dicetak karena belum disetujui sepenuhnya.');
        }

        $data = [
            'id' => $id,
            'title' => 'Formulir CAR - ' . $car->user->name,
            'car' => $car
        ];

        $pdf = Pdf::loadView('car.cetak', $data)
                    ->setPaper('a4', 'portrait');

        return $pdf->stream('CAR-' . $car->id . '.pdf');
    }
}
