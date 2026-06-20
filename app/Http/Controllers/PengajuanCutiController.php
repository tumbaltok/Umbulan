<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\SaldoCuti;
use App\Models\JenisCuti;
use App\Models\SubCuti;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PengajuanCutiController extends Controller
{
    // KARYAWAN: Melihat riwayat cuti milik diri sendiri (API)
    public function index(Request $request)
    {
        $user = $request->user();

        $riwayatCuti = PengajuanCuti::with(['jenisCuti', 'subCuti'])
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
            'sub_cuti_id'   => 'nullable|exists:sub_cutis,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan_cuti' => 'nullable|string',
            $aturanDokumen = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ],[
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai cuti.',
        ]);

        if ($request->sub_cuti_id) {
            $subCuti = \App\Models\SubCuti::find($request->sub_cuti_id);

            // Karena sudah menggunakan $casts boolean di model, kita bisa langsung cek seperti ini
            if ($subCuti && $subCuti->apakah_wajib_dokumen) {
                $aturanDokumen = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
            }
        }

        $request->validate([
            'dokumen_pendukung' => $aturanDokumen
        ], [
            'dokumen_pendukung.required' => 'Dokumen pendukung wajib diunggah untuk jenis cuti yang Anda pilih.'
        ]);

        $user = $request->user();
        $jenisCuti = JenisCuti::findOrFail($request->jenis_cuti_id);
        $namaCutiUtama = strtolower($jenisCuti->name_cuti ?? '');

        // Cari teks nama sub cuti jika sub_cuti_id dikirim
        $namaSubCuti = '';
        if ($request->sub_cuti_id) {
            $subDb = SubCuti::find($request->sub_cuti_id);
            $namaSubCuti = $subDb ? strtolower($subDb->nama_sub_cuti) : '';
        }

        // 1. ATURAN GENDER UTAMA (Back-end Guard)
        $genderUser = strtolower($user->gender_id ?? $user->gender->name ?? $user->gender ?? '');
        $isPria = ($genderUser === 'pria' || $genderUser === '1' || $genderUser === 'lki-laki' || $genderUser === 'male');

        if ($isPria) {
            if (str_contains($namaCutiUtama, 'melahirkan') || str_contains($namaSubCuti, 'melahirkan') || str_contains($namaSubCuti, 'gugur') || str_contains($namaSubCuti, 'haid')) {
                return response()->json([
                    'message' => 'Ditolak! Jenis perizinan/cuti ini hanya boleh diambil oleh karyawan wanita.',
                ], 403);
            }
        }

        // Hitung total hari cuti
        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $totalHari = $mulai->diffInDays($selesai) + 1;

        // 2. LOGIKA VALIDASI SALDO
        $apakahMemotongSaldo = $this->cekApakahMemotongSaldo($namaCutiUtama, $request->sub_cuti_id);

        if ($apakahMemotongSaldo) {
            $saldo = SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $request->jenis_cuti_id)
                ->where('tahun', Carbon::now()->year)
                ->first();

            if (!$saldo || $saldo->sisa_saldo < $totalHari) {
                return response()->json([
                    'message' => 'Saldo cuti tahunan Anda tidak mencukupi!',
                    'debug_info' => [
                        'total_hari_diajukan' => $totalHari,
                        'saldo_tersisa' => $saldo ? $saldo->sisa_saldo : 0,
                    ]
                ], 400);
            }
        }

        // Proses Dokumen Pendukung
        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $file = $request->file('dokumen_pendukung');
            $namaDokumen = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/dokumen_cuti'), $namaDokumen);
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
            'sub_cuti_id' => $request->sub_cuti_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'total_hari' => $totalHari,
            'alasan_cuti' => $request->alasan_cuti??'',
            'dokumen_pendukung' => $namaDokumen,
            'status_supervisor' => $statusSupervisor,
            'status_manager' => $statusManager,
            'status_akhir' => $statusAkhir,
        ]);

        return response()->json(['message' => 'Pengajuan berhasil dikirim!', 'data' => $pengajuan], 201);
    }

    // ATASAN: Melihat daftar cuti bawahan yang butuh diproses (API)
    public function listPengajuanAtasan(Request $request)
    {
        $atasan = $request->user()->load('role');
        $roleName = strtolower($atasan->role->role_name ?? '');

        if ($roleName === 'supervisor') {
            $daftarCuti = PengajuanCuti::with(['user', 'jenisCuti', 'subCuti'])
                ->whereHas('user', function($query) use ($atasan) {
                    $query->where('station_id', $atasan->station_id);
                })
                ->where('status_supervisor', 'pending')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(['data' => $daftarCuti], 200);
        }

        if ($roleName === 'manager') {
            $daftarCuti = PengajuanCuti::with(['user', 'jenisCuti', 'subCuti'])
                ->where('status_supervisor', 'approved')
                ->where('status_manager', 'pending')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(['data' => $daftarCuti], 200);
        }

        return response()->json(['message' => 'Akses ditolak.'], 403);
    }

    // ATASAN: Menyetujui atau Menolak Cuti (API)
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'aksi' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string'
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
                'catatan_penolakan' => $request->aksi === 'rejected' ? $request->catatan_penolakan : null
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
                'catatan_penolakan' => $request->aksi === 'rejected' ? $request->catatan_penolakan : null
            ]);

            if ($request->aksi === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }
        }

        return response()->json(['message' => 'Status pengajuan berhasil diperbarui oleh ' . $roleName]);
    }

    // Helper Function: Cek Aturan Pemotongan Saldo Utama (Aman Berdasarkan ID Database)
    private function cekApakahMemotongSaldo(string $namaCutiUtama, $subCutiId = null)
    {
        if (str_contains(strtolower($namaCutiUtama), 'cuti')) {
            if ($subCutiId) {
                $sub = SubCuti::find($subCutiId);
                if ($sub) {
                    $namaSub = strtolower($sub->nama_sub_cuti);
                    // Jika sub-cuti mengandung kata-kata pengecualian ini, jangan potong saldo utama
                    if (str_contains($namaSub, 'haid') || str_contains($namaSub, 'ibadah') || str_contains($namaSub, 'haji') || str_contains($namaSub, 'umroh')) {
                        return false;
                    }
                }
            }
            return true;
        }

        return false;
    }

    // Helper Function: Sinkronisasi Sinkron Cuti Ke Saldo & Absen
    private function sinkronisasiCutiDanAbsen(PengajuanCuti $pengajuan)
    {
        $namaCutiUtama = strtolower($pengajuan->jenisCuti->name_cuti ?? '');

        $apakahMemotongSaldo = $this->cekApakahMemotongSaldo($namaCutiUtama, $pengajuan->sub_cuti_id);

        if ($apakahMemotongSaldo) {
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
                    'keterangan' => 'Cuti disetujui: ' . $pengajuan->alasan_cuti,
                    'jam_masuk' => null,
                    'jam_pulang' => null
                ]
            );
        }
    }

    public function show(Request $request, int $id)
    {
        $pengajuan = PengajuanCuti::with(['user', 'jenisCuti', 'subCuti'])->findOrFail($id);
        return response()->json(['data' => $pengajuan], 200);
    }

    // Menampilkan Formulir Pengajuan Cuti (Web View) - DIPERBAIKI: Menggunakan with('subCutis') agar javascript dapet datanya
    public function createView()
    {
        $jenisCuti = JenisCuti::with('subCutis')->get();
        return view('cuti.create', compact('jenisCuti'));
    }

    // Memproses Simpan Data dari Form HTML Web
    public function storeWeb(Request $request)
    {
        $request->validate([
            'jenis_cuti_id' => 'required|exists:jenis_cutis,id',
            'sub_cuti_id'   => 'nullable|exists:sub_cutis,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan_cuti' => 'nullable|string',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $jenisCuti = JenisCuti::findOrFail($request->jenis_cuti_id);
        $namaCutiUtama = strtolower($jenisCuti->name_cuti ?? '');

        $namaSubCuti = '';
        if ($request->sub_cuti_id) {
            $subDb = SubCuti::find($request->sub_cuti_id);
            $namaSubCuti = $subDb ? strtolower($subDb->nama_sub_cuti) : '';
        }

        // ATURAN GENDER UTAMA (Back-end Guard)
        $genderUser = strtolower($user->gender_id ?? $user->gender->name ?? $user->gender ?? '');
        $isPria = ($genderUser === 'pria' || $genderUser === '1' || $genderUser === 'lki-laki' || $genderUser === 'male');

        if ($isPria) {
            if (str_contains($namaCutiUtama, 'melahirkan') || str_contains($namaSubCuti, 'melahirkan') || str_contains($namaSubCuti, 'gugur') || str_contains($namaSubCuti, 'haid')) {
                return back()->withErrors(['error' => 'Ditolak! Jenis perizinan/cuti ini hanya boleh diambil oleh karyawan wanita.'])->withInput();
            }
        }

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $totalHari = $mulai->diffInDays($selesai) + 1;

        $apakahMemotongSaldo = $this->cekApakahMemotongSaldo($namaCutiUtama, $request->sub_cuti_id);

        if ($apakahMemotongSaldo) {
            $saldo = SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $request->jenis_cuti_id)
                ->where('tahun', Carbon::now()->year)
                ->first();

            if (!$saldo || $saldo->sisa_saldo < $totalHari) {
                $sisa = $saldo ? $saldo->sisa_saldo : 0;
                return back()->withErrors(['error' => "Saldo cuti tidak mencukupi! Sisa saldo Anda: {$sisa} hari, total diajukan: {$totalHari} hari."])->withInput();
            }
        }

        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $file = $request->file('dokumen_pendukung');
            $namaDokumen = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/dokumen_cuti'), $namaDokumen);
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

        PengajuanCuti::create([
            'user_id' => $user->id,
            'jenis_cuti_id' => $request->jenis_cuti_id,
            'sub_cuti_id' => $request->sub_cuti_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'total_hari' => $totalHari,
            'alasan_cuti' => $request->alasan_cuti??'',
            'dokumen_pendukung' => $namaDokumen,
            'status_supervisor' => $statusSupervisor,
            'status_manager' => $statusManager,
            'status_akhir' => $statusAkhir,
        ]);

        return redirect()->route('dashboard')->with('success', 'Pengajuan cuti/ijin berhasil dikirim!');
    }

    // Menampilkan Halaman Riwayat Cuti (Web View)
    public function riwayatView(Request $request)
    {
        // Mengambil data riwayat cuti khusus untuk user yang sedang login
        $pengajuanCuti = DB::table('pengajuan_cutis')
            ->leftJoin('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->where('pengajuan_cutis.user_id', $request->user()->id)
            ->select('pengajuan_cutis.*', 'jenis_cutis.name_cuti', 'sub_cutis.nama_sub_cuti')
            ->orderBy('pengajuan_cutis.created_at', 'desc')
            ->get();

        return view('cuti.riwayat', compact('pengajuanCuti'));
    }

    // Menampilkan Halaman List Pengajuan Masuk untuk Atasan (Web View)
    public function listAtasanView()
    {
        $user = Auth::user();

        $query = DB::table('pengajuan_cutis')
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->select('pengajuan_cutis.*', 'users.name as user_name', 'jenis_cutis.name_cuti', 'users.station_id')
            ->orderBy('pengajuan_cutis.created_at', 'desc');

        if ($user->role_id == 3) { // Supervisor
            $query->where('pengajuan_cutis.status_supervisor', 'pending')
                ->where('users.station_id', $user->station_id);
        } elseif ($user->role_id == 2) { // Manager
            $query->where('pengajuan_cutis.status_manager', 'pending')
                ->where('pengajuan_cutis.status_supervisor', 'approved');
        } else {
            $query->where('pengajuan_cutis.status_akhir', 'pending');
        }

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan', compact('daftarPengajuan'));
    }

    // Memproses Aksi Penyetujuan Bertingkat (Web View)
    public function prosesPersetujuan(Request $request, int $id)
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

            if ($tindakan === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }
        }

        return redirect()->back()->with('success', 'Status pengajuan cuti karyawan berhasil diperbarui!');
    }

    public function cetakSuratCuti(int $id)
    {
        $pengajuan = PengajuanCuti::with(['user', 'jenisCuti', 'subCuti'])->findOrFail($id);

        if ($pengajuan->status_manager !== 'approved') {
            return redirect()->back()->with('error', 'Surat cuti belum dapat dicetak karena belum disetujui sepenuhnya.');
        }

        $pdf = Pdf::loadView('cuti.cetak', compact('pengajuan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Cuti_' . str_replace(' ', '_', $pengajuan->user->name) . '.pdf');
    }

    public function ambilSubCuti(int $id)
    {
        $jenis = JenisCuti::with('subCutis')->findOrFail($id);
        return response()->json($jenis->subCutis);
    }

    // Dipastikan mengembalikan view dengan data ber-relasi subCutis
    public function create()
    {
        $jenisCuti = JenisCuti::with('subCutis')->get();
        return view('cuti.create', compact('jenisCuti'));
    }
}
