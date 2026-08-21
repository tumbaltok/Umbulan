<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User\Station;

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

        return view('car.carriwayat', compact('riwayatCar'));
    }

    // KARYAWAN: Menampilkan form pengajuan CAR baru
    public function create()
    {
        $daftarStasiun = Station::orderBy('name', 'asc')->get();

        return view('car.carcreate', compact('daftarStasiun'));
    }

    // KARYAWAN: Mengirim form pengajuan CAR baru (Multi-Item)
    public function store(Request $request)
    {
        // Validasi format array dari form multi-item
        $request->validate([
            'alasan_pembelian' => 'required|string',
            'receiving_account' => 'required|string|in:META Umbulan,META Surabaya,META Booster-M',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string|max:255',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.estimasi_harga' => 'required|numeric|min:0',
            'items.*.dokumen_pendukung' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $roleLevel = $user->role->level ?? 4;

        // Auto-approve jika pemohon adalah Atasan / Manajerial
        $statusSupervisor = 'pending';
        $statusManager = 'pending';
        $statusAkhir = 'pending';

        if (in_array($roleLevel, [1, 2])) {
            $statusSupervisor = 'approved';
            $statusManager = 'approved';
            $statusAkhir = 'approved';
        } elseif ($roleLevel == 3) {
            $statusSupervisor = 'approved';
        }

        // Buat data utama (Header) CAR
        $carHeader = PengajuanCar::create([
            'user_id' => $user->id,
            'alasan_pembelian' => $request->alasan_pembelian,
            'receiving_account' => $request->receiving_account,
            'status_supervisor' => $statusSupervisor,
            'supervisor_id' => $statusSupervisor === 'approved' ? $user->id : null,
            'status_manager' => $statusManager,
            'manager_id' => $statusManager === 'approved' ? $user->id : null,
            'status_akhir' => $statusAkhir,
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

    // ATASAN & ADMIN: Melihat daftar pengajuan masuk dari bawahan
    public function listPengajuan()
    {
        $atasan = Auth::user();
        $roleLevel = $atasan->role->level ?? 4; // Default level 4 (Staff)

        $query = PengajuanCar::with(['user.role', 'details']);

        // Jika Atasan Level 3 (Supervisor / Pengawas Lapangan)
        if ($roleLevel == 3) {
            $query->where('status_supervisor', 'pending')
                ->whereHas('user', function ($q) use ($atasan) {
                    $q->where('station_id', $atasan->station_id);
                });
        }
        // Jika Atasan Level 2 (Manager / Kepala Sektor)
        elseif ($roleLevel == 2) {
            $query->where('status_supervisor', 'approved')
                ->where('status_manager', 'pending');
        }
        // Jika Level 1 (Admin / Direksi / Full Akses): Bisa melihat semua antrean pending
        else {
            $query->where('status_akhir', 'pending');
        }

        $daftarPengajuan = $query->latest()->get();

        return view('admin.persetujuan.persetujuanCar', compact('daftarPengajuan'));
    }

    // ATASAN & ADMIN: Menyetujui atau Menolak Pengajuan CAR
    public function prosesPersetujuan(Request $request, int $id)
    {
        $tindakan = $request->input('tindakan') ?? $request->input('aksi');
        $request->merge(['tindakan' => $tindakan]);

        $request->validate([
            'tindakan' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'required_if:tindakan,rejected|string|nullable',
        ]);

        $atasan = Auth::user();
        $pengajuan = PengajuanCar::findOrFail($id);
        $roleLevel = $atasan->role->level ?? 4;

        // TAHAP 1: Approval Pengawas (Level 3 / Supervisor)
        if ($roleLevel == 3) {
            $pengajuan->update([
                'status_supervisor' => $tindakan,
                'supervisor_id' => $tindakan === 'approved' ? $atasan->id : null,
                'status_akhir' => $tindakan === 'rejected' ? 'rejected' : 'pending',
                'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null,
            ]);

            return redirect()->back()->with('success', 'Persetujuan Tahap 1 berhasil diperbarui');

        // TAHAP 2: Approval Final / Anggaran (Level 1 & 2 / Manager, Admin, Direksi)
        } elseif (in_array($roleLevel, [1, 2])) {
            if ($pengajuan->status_supervisor === 'rejected') {
                return redirect()->back()->with('error', 'Pengajuan sudah ditolak pada tingkat pengawas.');
            }

            DB::beginTransaction();
            try {
                $pengajuan->update([
                    // Jika disetujui langsung oleh Level 1/2, otomatis selesaikan status_supervisor jika masih pending
                    'status_supervisor' => $pengajuan->status_supervisor === 'pending' ? 'approved' : $pengajuan->status_supervisor,
                    'status_manager' => $tindakan,
                    'manager_id' => $tindakan === 'approved' ? $atasan->id : null,
                    'status_akhir' => $tindakan,
                    'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null,
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal memproses persetujuan: '.$e->getMessage());
            }

            return redirect()->back()->with('success', 'Persetujuan Final berhasil diperbarui');

        } else {
            return redirect()->back()->with('error', 'Gagal! Hak akses Anda tidak mencukupi untuk menyetujui pengajuan ini.');
        }
    }

    // CETAK PDF FORMULIR CAR
    public function print(int $id)
    {
        // Load relasi pemohon, supervisor, manager, dan rincian barang
        $car = PengajuanCar::with(['user.role', 'supervisor', 'manager', 'details'])->findOrFail($id);

        if ($car->status_manager !== 'approved') {
            return redirect()->back()->with('error', 'Dokumen CAR belum dapat dicetak karena belum disetujui sepenuhnya.');
        }

        $data = [
            'id' => $id,
            'title' => 'Formulir CAR - '.$car->user->name,
            'car' => $car,
        ];

        $pdf = Pdf::loadView('car.carcetak', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('CAR-'.$car->id.'.pdf');
    }
}
