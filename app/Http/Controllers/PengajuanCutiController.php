<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\SaldoCuti;
use App\Models\JenisCuti;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PengajuanCutiController extends Controller
{
    // KARYAWAN: Melihat riwayat cuti milik diri sendiri (API)
    public function index(Request $request)
    {
        $user = $request->user();

        $riwayatCuti = PengajuanCuti::with('jenisCuti')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat pengajuan cuti berhasil diambil.',
            'data' => $riwayatCuti
        ], 200);
    }

    // KARYAWAN: Mengajukan Cuti (API Dasar)
    public function store(Request $request)
    {
        $request->validate([
            'jenis_cuti_id' => 'required|exists:jenis_cutis,id',
            'tanggal_mulai' => 'required|date', // Dihapus after_or_equal agar bisa mengajukan cuti susulan/sakit
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan_cuti' => 'required|string',
            'dokumen_pendukung' => 'nullable' // Di API menerima nama file hasil upload
        ]);

        $user = $request->user();

        // Ambil data jenis cuti untuk validasi nama/tipe cuti secara dinamis
        $jenisCuti = JenisCuti::findOrFail($request->jenis_cuti_id);
        $namaCuti = strtolower($jenisCuti->name_cuti ?? '');

        // 1. ATURAN: Cuti Melahirkan hanya boleh diambil oleh wanita
        if (str_contains($namaCuti, 'melahirkan')) {
            $genderUser = strtolower($user->gender->name ?? $user->gender ?? '');
            if (!in_array($genderUser, ['wanita', 'perempuan'])) {
                return response()->json([
                    'message' => 'Ditolak! Cuti melahirkan hanya boleh diambil oleh karyawan wanita.',
                    'debug_gender_terbaca' => 'Karena kamu seorang ' . $genderUser
                ], 403);
            }
        }

        // Hitung total hari cuti
        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $totalHari = $mulai->diffInDays($selesai) + 1;

        // Cuti Sakit & Melahirkan tidak memerlukan pengecekan saldo cuti (bebas)
        $isCutiSakit = str_contains($namaCuti, 'sakit');
        $isCutiMelahirkan = str_contains($namaCuti, 'melahirkan');

        if (!$isCutiSakit && !$isCutiMelahirkan) {
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
                    ]
                ], 400);
            }
        }

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
            'dokumen_pendukung' => $request->dokumen_pendukung,
            'status_supervisor' => $statusSupervisor,
            'status_manager' => $statusManager,
            'status_akhir' => $statusAkhir,
        ]);

        return response()->json(['message' => 'Cuti ' . $jenisCuti->name_cuti . ' berhasil diajukan!', 'data' => $pengajuan], 201);
    }

    // ATASAN: Melihat daftar cuti bawahan yang butuh diproses (API)
    public function listPengajuanAtasan(Request $request)
    {
        $atasan = $request->user()->load('role');
        $roleName = strtolower($atasan->role->role_name ?? '');

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

    // ATASAN: Menyetujui atau Menolak Cuti (API/Postman)
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'aksi' => 'required|in:approved,rejected',
            'catatan' => 'nullable|string'
        ]);

        $pengajuan = PengajuanCuti::with(['user', 'jenisCuti'])->findOrFail($id);
        $atasan = $request->user()->load('role');
        $roleName = strtolower($atasan->role->role_name ?? '');

        if ($roleName === 'supervisor') {
            if ($atasan->station_id !== $pengajuan->user->station_id) {
                return response()->json(['message' => 'Ditolak! Karyawan berbeda stasiun.'], 403);
            }

            $pengajuan->update([
                'status_supervisor' => $request->aksi,
                'status_akhir' => $request->aksi === 'rejected' ? 'rejected' : 'pending',
                'catatan_penolakan' => $request->aksi === 'rejected' ? $request->catatan : null
            ]);
        }

        if ($roleName === 'manager') {
            if ($pengajuan->status_supervisor === 'rejected') {
                return response()->json(['message' => 'Ditolak! Sudah ditolak oleh Supervisor.'], 400);
            }
            if ($request->aksi === 'approved' && $pengajuan->status_supervisor === 'pending') {
                return response()->json(['message' => 'Ditolak! Menunggu persetujuan Supervisor.'], 400);
            }

            $pengajuan->update([
                'status_manager' => $request->aksi,
                'status_akhir' => $request->aksi,
                'catatan_penolakan' => $request->aksi === 'rejected' ? $request->catatan : null
            ]);

            // Panggil fungsi pembantu untuk memotong saldo & sinkronisasi absensi
            if ($request->aksi === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }
        }

        return response()->json(['message' => 'Status pengajuan berhasil diperbarui oleh ' . $roleName]);
    }

    // Helper Function: Mencegah duplikasi penulisan kode potong saldo & absen
    private function sinkronisasiCutiDanAbsen($pengajuan)
    {
        $namaCuti = strtolower($pengajuan->jenisCuti->name_cuti ?? '');
        $isCutiSakit = str_contains($namaCuti, 'sakit');
        $isCutiMelahirkan = str_contains($namaCuti, 'melahirkan');

        if (!$isCutiSakit && !$isCutiMelahirkan) {
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

        for ($date = $tanggalMulai; $date->lte($tanggalSelesai); $date->addDay()) {
            Absensi::updateOrCreate(
                ['user_id' => $pengajuan->user_id, 'tanggal' => $date->format('Y-m-d')],
                [
                    'status_kehadiran' => 'Cuti',
                    'keterangan' => 'Cuti disetujui: ' . ($pengajuan->jenisCuti->name_cuti ?? 'Cuti Resmi'),
                    'jam_masuk' => null,
                    'jam_pulang' => null
                ]
            );
        }
    }

    public function show(Request $request, int $id)
    {
        $pengajuan = PengajuanCuti::with(['user', 'jenisCuti'])->findOrFail($id);
        return response()->json(['data' => $pengajuan], 200);
    }

    // 1. Menampilkan Formulir Pengajuan Cuti (Web View)
    public function createView()
    {
        $jenisCuti = JenisCuti::all();
        return view('cuti.create', compact('jenisCuti'));
    }

    // 2. Memproses Simpan Data dari Form HTML Web (Mendukung File Upload)
    public function storeWeb(Request $request)
    {
        // 1. Validasi input khusus dari Form Web Browser
        $request->validate([
            'jenis_cuti_id' => 'required|exists:jenis_cutis,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan_cuti' => 'required|string',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        // 2. Ambil data jenis cuti untuk mengecek aturan gender & tipe cuti
        $jenisCuti = \App\Models\JenisCuti::findOrFail($request->jenis_cuti_id);
        $namaCuti = strtolower($jenisCuti->name_cuti ?? '');

        // Aturan Gender untuk Cuti Melahirkan
        if (str_contains($namaCuti, 'melahirkan')) {
            $genderUser = strtolower($user->gender->name ?? $user->gender ?? '');
            if (!in_array($genderUser, ['wanita', 'perempuan'])) {
                return back()->withErrors(['error' => 'Ditolak! Cuti melahirkan hanya boleh diambil oleh karyawan wanita.'])->withInput();
            }
        }

        // 3. Hitung total hari cuti secara otomatis menggunakan Carbon
        $mulai = \Carbon\Carbon::parse($request->tanggal_mulai);
        $selesai = \Carbon\Carbon::parse($request->tanggal_selesai);
        $totalHari = $mulai->diffInDays($selesai) + 1;

        // 4. Validasi Saldo Cuti (Kecuali Cuti Sakit & Melahirkan)
        $isCutiSakit = str_contains($namaCuti, 'sakit');
        $isCutiMelahirkan = str_contains($namaCuti, 'melahirkan');

        if (!$isCutiSakit && !$isCutiMelahirkan) {
            $saldo = \App\Models\SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $request->jenis_cuti_id)
                ->where('tahun', \Carbon\Carbon::now()->year)
                ->first();

            if (!$saldo || $saldo->sisa_saldo < $totalHari) {
                $sisa = $saldo ? $saldo->sisa_saldo : 0;
                return back()->withErrors(['error' => "Saldo cuti tidak mencukupi! Sisa saldo Anda: {$sisa} hari, total diajukan: {$totalHari} hari."])->withInput();
            }
        }

        // 5. Proses Upload File Berkas Fisik jika ada
        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $file = $request->file('dokumen_pendukung');
            $namaDokumen = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/dokumen_cuti'), $namaDokumen);
        }

        // 6. Tentukan status awal berdasarkan Jabatan/Role Karyawan yang login
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

        // 7. EKSEKUSI MUTLAK: Simpan langsung ke database menggunakan Model Eloquent
        \App\Models\PengajuanCuti::create([
            'user_id' => $user->id,
            'jenis_cuti_id' => $request->jenis_cuti_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'total_hari' => $totalHari,
            'alasan_cuti' => $request->alasan_cuti,
            'dokumen_pendukung' => $namaDokumen, // Menyimpan nama file fisik yang valid
            'status_supervisor' => $statusSupervisor,
            'status_manager' => $statusManager,
            'status_akhir' => $statusAkhir,
        ]);

        return redirect()->route('dashboard')->with('success', 'Cuti ' . $jenisCuti->name_cuti . ' berhasil diajukan!');
    }

    // 3. Menampilkan Halaman Riwayat Cuti (Web View)
    public function riwayatView(Request $request)
    {
        $user = $request->user();

        $riwayatCuti = DB::table('pengajuan_cutis')
            ->leftJoin('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->where('pengajuan_cutis.user_id', $user->id)
            ->select('pengajuan_cutis.*', 'jenis_cutis.name_cuti')
            ->orderBy('pengajuan_cutis.created_at', 'desc')
            ->get();

        return view('cuti.riwayat', compact('riwayatCuti'));
    }

    // 4. Menampilkan Halaman List Pengajuan Masuk untuk Atasan (Web View)
    public function listAtasanView()
    {
        $user = Auth::user();

        // 1. Ambil data pengajuan cuti dengan Join tabel lengkap
        $query = DB::table('pengajuan_cutis')
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->select('pengajuan_cutis.*', 'users.name as user_name', 'jenis_cutis.name_cuti', 'users.station_id')
            ->orderBy('pengajuan_cutis.created_at', 'desc');

        // 2. Filter Logika Berdasarkan Jabatan/Role Atasan
        if ($user->role_id == 3) {
            // JIKA LOGIN SEBAGAI SUPERVISOR:
            // Tampilkan yang status_supervisor-nya masih pending DAN karyawan berada di stasiun yang sama
            $query->where('pengajuan_cutis.status_supervisor', 'pending')
                ->where('users.station_id', $user->station_id);

        } elseif ($user->role_id == 2) {
            // JIKA LOGIN SEBAGAI MANAGER:
            // Tampilkan yang status_manager-nya pending DAN sudah disetujui Supervisor terlebih dahulu
            $query->where('pengajuan_cutis.status_manager', 'pending')
                ->where('pengajuan_cutis.status_supervisor', 'approved');

        } else {
            // JIKA ADMIN UTAMA / ROLE LAIN:
            $query->where('pengajuan_cutis.status_akhir', 'pending');
        }

        // 3. Eksekusi query ke database
        $daftarPengajuan = $query->get();

        // Pastikan baris dd() di bawah ini sudah dihapus/diberi komentar agar halaman HTML muncul kembali!
        // dd($daftarPengajuan);

        return view('admin.persetujuan', compact('daftarPengajuan'));
    }

    // 5. Memproses Aksi Penyetujuan Bertingkat (Web View)
    public function prosesPersetujuan(Request $request, $id)
    {
        $request->validate([
            'tindakan' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string'
        ]);

        $user = Auth::user();
        $tindakan = $request->tindakan;
        $pengajuan = PengajuanCuti::findOrFail($id);

        if ($user->role_id == 3) { // Supervisor
            $pengajuan->update([
                'status_supervisor' => $tindakan,
                'status_akhir' => $tindakan === 'rejected' ? 'rejected' : 'pending',
                'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
            ]);
        } elseif ($user->role_id == 2) { // Manager
            if ($pengajuan->status_supervisor === 'rejected') {
                return redirect()->back()->with('error', 'Pengajuan sudah ditolak oleh Supervisor.');
            }

            $pengajuan->update([
                'status_manager' => $tindakan,
                'status_akhir' => $tindakan,
                'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
            ]);

            // KUNCI PERBAIKAN: Jalankan potong saldo & pengisian absen jika disetujui Manager lewat Web!
            if ($tindakan === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }
        }

        return redirect()->back()->with('success', 'Status pengajuan cuti karyawan berhasil diperbarui!');
    }
}
