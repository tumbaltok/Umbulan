<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Models\Car\PengajuanCar;
use App\Models\Car\PengajuanCarDetail;
use App\Models\User\Station;
use App\Models\User\User;
use App\Jobs\SendWhatsAppNotification;
use App\Services\ApprovalRoutingService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengajuanCarController extends Controller
{
    // Menampilkan riwayat pengajuan CAR milik pengguna saat ini
    public function index()
    {
        $user = Auth::user();
        $riwayatCar = PengajuanCar::with('details')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('car.carriwayat', compact('riwayatCar'));
    }

    // Menampilkan form pembuatan pengajuan CAR baru
    public function create()
    {
        $user = Auth::user();
        if (!$user->isAccountComplete()) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda wajib melengkapi verifikasi email, nomor WhatsApp, biometrik wajah, tanda tangan digital (TTD), dan jadwal kerja sebelum dapat membuat pengajuan.');
        }

        // Format nomor CAR: [No] / META / PAS / CAR / [Romawi] / [Tahun]
        $tahunSekarang = date('Y');
        $bulanAngka = (int) date('m');
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $bulanRomawi = $romawi[$bulanAngka] ?? 'I';

        $totalTahunIni = PengajuanCar::whereYear('created_at', $tahunSekarang)->count();
        $nomorUrut = $totalTahunIni + 1;
        $nomorCar = "{$nomorUrut} / META / PAS / CAR / {$bulanRomawi} / {$tahunSekarang}";

        $daftarStasiun = Station::orderBy('name', 'asc')->get();

        // Statistik ringkas & Pengajuan CAR terakhir user
        $recentCars = PengajuanCar::with('details')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $totalCarUser = PengajuanCar::where('user_id', $user->id)
            ->whereYear('created_at', $tahunSekarang)
            ->count();

        $carApprovedCount = PengajuanCar::where('user_id', $user->id)
            ->whereYear('created_at', $tahunSekarang)
            ->where('status_akhir', 'approved')
            ->count();

        $carPendingCount = PengajuanCar::where('user_id', $user->id)
            ->where('status_akhir', 'pending')
            ->count();

        $approvalRoute = app(ApprovalRoutingService::class)->resolveApprovalRoute($user, 'car');

        return view('car.carcreate', compact(
            'nomorCar',
            'daftarStasiun',
            'recentCars',
            'totalCarUser',
            'carApprovedCount',
            'carPendingCount',
            'approvalRoute'
        ));
    }

    // Menyimpan formulir pengajuan CAR beserta rincian item pengadaan
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAccountComplete()) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda wajib melengkapi verifikasi email, nomor WhatsApp, biometrik wajah, tanda tangan digital (TTD), dan jadwal kerja sebelum dapat membuat pengajuan.');
        }

        $request->validate([
            'nomor_car'                 => 'nullable|string|max:100',
            'tanggal_pengajuan'         => 'nullable|date',
            'alasan_pembelian'          => 'required|string',
            'note_explanation'          => 'nullable|string',
            'receiving_account'         => 'required|string|max:255',
            'items'                     => 'required|array|min:1',
            'items.*.nama_barang'       => 'required|string|max:255',
            'items.*.jumlah'            => 'required|numeric|min:1',
            'items.*.satuan'            => 'required|string|max:50',
            'items.*.estimasi_harga'    => 'required|numeric|min:0',
            'items.*.ongkir'            => 'nullable|numeric|min:0',
            'items.*.dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $approvalService = app(ApprovalRoutingService::class);
        $route = $approvalService->resolveApprovalRoute($user, 'car');

        if (!$route['success']) {
            return back()->withErrors([
                'error' => $route['error'] ?? 'Alur persetujuan (Approver 1) untuk jabatan Anda belum dikonfigurasi oleh Administrator. Pengajuan CAR tidak dapat diproses demi keamanan finansial.'
            ])->withInput();
        }

        $levels          = $route['levels'];
        $approver1RoleId = $route['approver_1_role_id'];
        $approver2RoleId = $route['approver_2_role_id'];
        $statusTahap1    = $route['status_tahap_1'];
        $statusTahap2    = $route['status_tahap_2'];
        $statusAkhir     = $route['status_akhir'];

        DB::beginTransaction();
        try {
            // [M-01 FIX] Generate nomor CAR secara atomik di dalam transaksi dengan row-locking
            $tahunSekarang = date('Y');
            $bulanAngka = (int) date('m');
            $romawi = [
                1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
                7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
            ];
            $bulanRomawi = $romawi[$bulanAngka] ?? 'I';
            $totalTahunIni = PengajuanCar::whereYear('created_at', $tahunSekarang)->lockForUpdate()->count();
            $nomorUrut = $totalTahunIni + 1;
            $nomorCarAuto = "{$nomorUrut} / META / PAS / CAR / {$bulanRomawi} / {$tahunSekarang}";

            $carHeader = PengajuanCar::create([
                'user_id'               => $user->id,
                'nomor_car'             => $request->nomor_car ?: $nomorCarAuto,
                'tanggal_pengajuan'     => $request->tanggal_pengajuan ?: now()->toDateString(),
                'alasan_pembelian'      => $request->alasan_pembelian,
                'note_explanation'      => $request->note_explanation ?: $request->alasan_pembelian,
                'receiving_account'     => $request->receiving_account,
                'total_approval_levels' => $levels,
                'status_tahap_1'        => $statusTahap1,
                'approver_tahap_1_id'   => $statusTahap1 === 'approved' ? $user->id : null,
                'status_tahap_2'        => $statusTahap2,
                'approver_tahap_2_id'   => $statusTahap2 === 'approved' ? $user->id : null,
                'status_akhir'          => $statusAkhir,
            ]);

            foreach ($request->items as $index => $item) {
                $pathDokumen = null;
                if ($request->hasFile("items.{$index}.dokumen_pendukung")) {
                    $file = $request->file("items.{$index}.dokumen_pendukung");
                    $pathDokumen = $file->store('dokumen_car', 'public');
                }

                $qty = (float) $item['jumlah'];
                $harga = (float) $item['estimasi_harga'];
                $ongkir = (float) ($item['ongkir'] ?? 0);

                $carHeader->details()->create([
                    'nama_barang'              => $item['nama_barang'],
                    'jumlah'                   => $qty,
                    'satuan'                   => $item['satuan'],
                    'estimasi_harga'           => $harga,
                    'ongkir'                   => $ongkir,
                    'total_harga'              => ($qty * $harga) + $ongkir,
                    'dokumen_nota_or_proposal' => $pathDokumen,
                ]);
            }

            DB::commit();

            // Kirim notifikasi WhatsApp ke Atasan
            if ($statusTahap1 === 'pending' && !empty($approver1RoleId)) {
                try {
                    $approvers = $approvalService->getApproversForStage($user, 'car', 1);

                    foreach ($approvers as $approver) {
                        SendWhatsAppNotification::sendNewSubmission('car', $carHeader, $approver, 1);
                    }
                } catch (\Exception $waEx) {
                    Log::error('Gagal mengirim notifikasi WA CAR baru: ' . $waEx->getMessage());
                }
            }

            return redirect()->route('car.riwayat')->with('success', 'Pengajuan CAR berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan pengajuan CAR: ' . $e->getMessage()])->withInput();
        }
    }
}
