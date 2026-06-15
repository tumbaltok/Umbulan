<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\SaldoCuti;
use App\Models\JenisCuti;
use Carbon\Carbon;
use App\Models\Absensi;
use PhpParser\Node\Stmt\ElseIf_;

// use Illuminate\Support\Facades\Auth;

class PengajuanCutiController extends Controller
{
    // KARYAWAN: Melihat riwayat cuti milik diri sendiri
    public function index(Request $request)
    {
        $user = $request->user();

        // Mengambil cuti yang diajukan oleh user yang sedang login saat ini
        $riwayatCuti = PengajuanCuti::with('jenisCuti')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat pengajuan cuti berhasil diambil.',
            'data' => $riwayatCuti
        ], 200);
    }

    // KARYAWAN: Mengajukan Cuti
    public function store(Request $request)
    {
        $request->validate([
            'jenis_cuti_id' => 'required|exists:jenis_cutis,id',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan_cuti' => 'required|string',
        ]);

        $user = $request->user();

        // Ambil data jenis cuti untuk validasi nama/tipe cuti secara dinamis
        $jenisCuti = JenisCuti::findOrFail($request->jenis_cuti_id);
        $namaCuti = strtolower($jenisCuti->name_cuti??'');

        // 1. ATURAN: Cuti Melahirkan hanya boleh diambil oleh wanita
        if (str_contains($namaCuti, 'melahirkan')) {
            $genderUser = strtolower($user->gender->name ?? '');
            if (!in_array($genderUser, ['wanita'])) {
                return response()->json([
                    'message' => 'Ditolak! Cuti melahirkan hanya boleh diambil oleh karyawan wanita.',
                    'debug_gender_terbaca' => 'Karena kamu seorang '.$genderUser
                ], 403);
            }
        }

        // Hitung total hari cuti
        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $totalHari = $mulai->diffInDays($selesai) + 1;

        // Cuti Sakit tidak memerlukan pengecekan saldo cuti (bebas/unlimited)
        $isCutiSakit = str_contains($namaCuti, 'sakit');
        $isCutiMelahirkan = str_contains($namaCuti, 'melahirkan');

        if (!$isCutiSakit&&!$isCutiMelahirkan) {
            // Cek Saldo Cuti Karyawan selain cuti sakit & melahirkan
            $saldo = SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $request->jenis_cuti_id)
                ->where('tahun', Carbon::now()->year)
                ->first();

            if (!$saldo || $saldo->sisa_saldo < $totalHari) {
                return response()->json([
                    'message' => 'Saldo cuti tidak mencukupi!',
                    'debug_info' => [
                        'total_hari_diajukan' => $totalHari,
                        'saldo_yang_ditemukan' => $saldo ? $saldo->sisa_saldo : 0,
                        'tahun_berjalan' => Carbon::now()->year,
                        'Status_Saldo_Ditemukan' => $saldo ? 'Ada' : 'Tidak Ditemukan di Database'
                    ]
                ], 400);
            }
        }

        $jenisCuti = JenisCuti::find($request->jenis_cuti_id);
        $roleName = strtolower($user->role->role_name ?? $user->role ?? '');

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

        // Simpan Pengajuan
        $pengajuan = PengajuanCuti::create([
            'user_id' => $user->id,
            'jenis_cuti_id' => $request->jenis_cuti_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'total_hari' => $totalHari,
            'alasan_cuti' => $request->alasan_cuti,
            'status_supervisor' => $statusSupervisor,
            'status_manager' => $statusManager,
            'status_akhir' => $statusAkhir,
        ]);

        return response()->json(['message' => 'Cuti ' . $jenisCuti->name_cuti . ' berhasil diajukan!', 'data' => $pengajuan], 201);
    }

    // ATASAN: Melihat daftar cuti bawahan yang butuh diproses
    public function listPengajuanAtasan(Request $request)
    {
        $atasan = $request->user()->load('role');
        $roleName = strtolower($atasan->role->role_name ?? '');

        // Jika Supervisor, hanya tampilkan cuti dari karyawan stasiun yang sama
        if ($roleName === 'supervisor') {
            $daftarCuti = PengajuanCuti::with(['user', 'jenisCuti'])
                ->whereHas('user', function($query) use ($atasan) {
                    $query->where('station_id', $atasan->station_id);
                })
                ->where('status_supervisor', 'pending')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(['data' => $daftarCuti], 200);
        }

        // Jika Manager, tampilkan semua yang sudah di-approve Supervisor (atau jika SPV kosong)
        if ($roleName === 'manager') {
            $daftarCuti = PengajuanCuti::with(['user', 'jenisCuti'])
                ->where('status_supervisor', 'approved')
                ->where('status_manager', 'pending')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(['data' => $daftarCuti], 200);
        }

        return response()->json(['message' => 'Akses ditolak.'], 403);
    }

    // ATASAN: Menyetujui atau Menolak Cuti
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'aksi' => 'required|in:approved,rejected', // Pilihan harus approved atau rejected
            'catatan' => 'nullable|string'
        ]);

        // Eager load relasi 'user' dan 'jenisCuti'
        $pengajuan = PengajuanCuti::with(['user', 'jenisCuti'])->findOrFail($id);

        // Memuat relasi 'role' pada atasan yang sedang login
        $atasan = $request->user()->load('role');
        $roleName = strtolower($atasan->role->role_name ?? '');

        // JIKA YANG LOGIN ADALAH SUPERVISOR
        if ($roleName === 'supervisor') {

            // Cek stasiun kerja (Harus sama dengan karyawan)
            if ($atasan->station_id !== $pengajuan->user->station_id) {
                return response()->json([
                    'message' => 'Ditolak! Anda hanya bisa memproses cuti karyawan di stasiun yang sama.'
                ], 403);
            }

            $pengajuan->update([
                'status_supervisor' => $request->aksi,
                'status_akhir' => $request->aksi === 'rejected' ? 'rejected' : 'pending',
                'catatan_penolakan' => $request->aksi === 'rejected' ? $request->catatan : null
            ]);
        }

        // JIKA YANG LOGIN ADALAH MANAGER
        if ($roleName === 'manager') {

            // Pengamanan tambahan: mencegah manager menyetujui jika sudah ditolak supervisor sebelumnya
            if ($pengajuan->status_supervisor === 'rejected') {
                return response()->json(['message' => 'Ditolak! Pengajuan ini sudah ditolak oleh Supervisor sebelumnya.'], 400);
            }

            // Cegah manager approve jika SPV belum approve (masih pending)
            if ($request->aksi === 'approved' && $pengajuan->status_supervisor === 'pending') {
                return response()->json(['message' => 'Ditolak! Menunggu persetujuan Supervisor terlebih dahulu.'], 400);
            }

            $pengajuan->update([
                'status_manager' => $request->aksi,
                'status_akhir' => $request->aksi, // Jika manager approve/reject, langsung jadi status akhir
                'catatan_penolakan' => $request->aksi === 'rejected' ? $request->catatan : null
            ]);

            // Potong saldo cuti otomatis jika disetujui oleh Manager
            if ($request->aksi === 'approved') {

                // Ambil nama cuti dari relasi
                $namaCuti = strtolower($pengajuan->jenisCuti->nama ?? $pengajuan->jenisCuti->name ?? '');
                $isCutiSakit = str_contains($namaCuti, 'sakit');
                $isCutiMelahirkan = str_contains($namaCuti, 'melahirkan');

                // ATURAN: Hanya potong saldo jika jenis cutinya BUKAN cuti sakit
                if (!$isCutiSakit&&!$isCutiMelahirkan) {
                    $saldo = SaldoCuti::where('user_id', $pengajuan->user_id)
                        ->where('jenis_cuti_id', $pengajuan->jenis_cuti_id)
                        ->where('tahun', Carbon::parse($pengajuan->tanggal_mulai)->year)
                        ->first();

                    if ($saldo) {
                        $saldo->decrement('sisa_saldo', $pengajuan->total_hari);
                    }
                }
                $tanggalMulai = Carbon::parse($pengajuan->tanggal_mulai);
                $tanggalSelesai = Carbon::parse($pengajuan->tanggal_selesai);

                // Lakukan looping dari tanggal mulai sampai tanggal selesai cuti
                for ($date = $tanggalMulai; $date->lte($tanggalSelesai); $date->addDay()) {

                    // Format tanggal menjadi Y-m-d (contoh: 2026-06-10)
                    $tanggalCuti = $date->format('Y-m-d');

                    // Gunakan updateOrCreate agar tidak duplikat jika absen sudah terlanjur dibuat
                    // Sesuaikan nama kolom tabel absensi Anda di bawah ini
                    \App\Models\Absensi::updateOrCreate(
                        [
                            'user_id' => $pengajuan->user_id,
                            'tanggal' => $tanggalCuti
                        ],
                        [
                            'status_kehadiran' => 'Cuti', // Menyatakan karyawan sedang cuti resmi
                            'keterangan' => 'Cuti disetujui: ' . ($pengajuan->jenisCuti->name_cuti ?? 'Cuti Tahunan'),
                            'jam_masuk' => null, // Tidak perlu jam masuk karena cuti
                            'jam_pulang' => null
                        ]
                    );
                }
            }
            return response()->json(['message' => 'Manager berhasil memperbarui status pengajuan.']);
        }
        return response()->json([
            'message' => 'Gagal! Akun Anda tidak dikenali sebagai Supervisor maupun Manager.',
            'debug_role_terbaca' => $roleName
        ], 403);
    }

    public function show(Request $request, int $id)
    {
        $pengajuan = PengajuanCuti::with(['user', 'jenisCuti'])->findOrFail($id);
        return response()->json(['data' => $pengajuan], 200);
    }
}
