<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\SaldoCuti;
use App\Models\JenisCuti;
use App\Models\SubCuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\CutiHelperTrait;

class CutiKaryawanController extends Controller
{
    use CutiHelperTrait;

    // Halaman buat cuti (Web)
    public function create()
    {
        $jenisCuti = JenisCuti::with('subCutis')->get();
        return view('cuti.create', compact('jenisCuti'));
    }

    // Mengambil Sub Cuti lewat AJAX/API
    public function ambilSubCuti(int $id)
    {
        $jenis = JenisCuti::with('subCutis')->findOrFail($id);
        return response()->json($jenis->subCutis);
    }

    // Riwayat Cuti (Web View)
    public function riwayatView(Request $request)
    {
        $pengajuanCuti = DB::table('pengajuan_cutis')
            ->leftJoin('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->where('pengajuan_cutis.user_id', $request->user()->id)
            ->select('pengajuan_cutis.*', 'jenis_cutis.name_cuti', 'sub_cutis.nama_sub_cuti')
            ->orderBy('pengajuan_cutis.created_at', 'desc')
            ->get();

        $pengajuanCuti->each(function ($item) {
            $item->tanggal_mulai_formatted = Carbon::parse($item->tanggal_mulai)->format('d M Y');
            $item->tanggal_selesai_formatted = Carbon::parse($item->tanggal_selesai)->format('d M Y');
            $item->nama_sub_cuti = $item->nama_sub_cuti ?? '-';
        });

        return view('cuti.riwayat', compact('pengajuanCuti'));
    }

    // Riwayat Cuti (API JSON)
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

    // Simpan Pengajuan Cuti (Web Form)
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

        $namaSubCuti = $request->sub_cuti_id ? strtolower(SubCuti::find($request->sub_cuti_id)->nama_sub_cuti ?? '') : '';
        $genderUser = strtolower($user->gender_id ?? $user->gender->name ?? $user->gender ?? '');
        $isPria = ($genderUser === 'pria' || $genderUser === '1' || $genderUser === 'laki-laki' || $genderUser === 'male');

        if ($isPria && (str_contains($namaCutiUtama, 'melahirkan') || str_contains($namaSubCuti, 'melahirkan') || str_contains($namaSubCuti, 'gugur') || str_contains($namaSubCuti, 'haid'))) {
            return back()->withErrors(['error' => 'Ditolak! Jenis perizinan/cuti ini hanya boleh diambil oleh karyawan wanita.'])->withInput();
        }

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $totalHari = $mulai->diffInDays($selesai) + 1;

        if ($this->alurPotongSaldo($namaCutiUtama, $request->sub_cuti_id)) {
            $saldo = SaldoCuti::where('user_id', $user->id)->where('jenis_cuti_id', $request->jenis_cuti_id)->where('tahun', Carbon::now()->year)->first();
            $sisa = $saldo ? (int)$saldo->sisa_saldo : 0;

            if ($sisa <= 0) return back()->withErrors(['error' => 'Sisa kuota cuti anda sudah habis (0 hari).'])->withInput();
            if ($sisa < $totalHari) return back()->withErrors(['error' => "Sisa kuota cuti anda hanya tinggal {$sisa} hari, sedangkan Anda mengajukan {$totalHari} hari."])->withInput();
        }

        $namaDokumen = $request->hasFile('dokumen_pendukung') ? $request->file('dokumen_pendukung')->store('dokumen_cuti', 'public') : null;
        $roleName = strtolower($user->role->role_name ?? $user->role ?? '');

        $status = ($roleName === 'manager') ? 'approved' : (($roleName === 'supervisor') ? 'approved' : 'pending');
        $statusManager = ($roleName === 'manager') ? 'approved' : 'pending';

        PengajuanCuti::create([
            'user_id' => $user->id,
            'jenis_cuti_id' => $request->jenis_cuti_id,
            'sub_cuti_id' => $request->sub_cuti_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'total_hari' => $totalHari,
            'alasan_cuti' => $request->alasan_cuti ?? '',
            'dokumen_pendukung' => $namaDokumen,
            'status_supervisor' => $status,
            'status_manager' => $statusManager,
            'status_akhir' => $statusManager,
        ]);

        return redirect()->route('dashboard')->with('success', 'Pengajuan cuti/ijin berhasil dikirim!');
    }

    // Simpan Pengajuan Cuti (API)
    // Simpan Pengajuan Cuti (API Dasar)
    public function store(Request $request)
    {
        $aturanDokumen = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';

        $request->validate([
            'jenis_cuti_id' => 'required|exists:jenis_cutis,id',
            'sub_cuti_id'   => 'nullable|exists:sub_cutis,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan_cuti' => 'nullable|string',
        ],[
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai cuti.',
        ]);

        if ($request->sub_cuti_id) {
            $subCuti = SubCuti::find($request->sub_cuti_id);

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
        $tanggalMulaiBaru = \Carbon\Carbon::parse($request->tanggal_mulai)->format('Y-m-d');
        $tanggalSelesaiBaru = \Carbon\Carbon::parse($request->tanggal_selesai)->format('Y-m-d');

        $cutiBentrok = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->whereRaw('LOWER(status_akhir) = ?', ['pending'])
                      ->orWhereRaw('LOWER(status_akhir) = ?', ['approved']);
            })
            ->where(function ($query) use ($tanggalMulaiBaru, $tanggalSelesaiBaru) {
                $query->where(function ($q) use ($tanggalMulaiBaru, $tanggalSelesaiBaru) {
                    $q->where('tanggal_mulai', '<=', $tanggalMulaiBaru)
                      ->where('tanggal_selesai', '>=', $tanggalMulaiBaru);
                })
                ->orWhere(function ($q) use ($tanggalMulaiBaru, $tanggalSelesaiBaru) {
                    $q->where('tanggal_mulai', '<=', $tanggalSelesaiBaru)
                      ->where('tanggal_selesai', '>=', $tanggalSelesaiBaru);
                })
                ->orWhere(function ($q) use ($tanggalMulaiBaru, $tanggalSelesaiBaru) {
                    $q->where('tanggal_mulai', '>=', $tanggalMulaiBaru)
                      ->where('tanggal_selesai', '<=', $tanggalSelesaiBaru);
                });
            })
            ->first();

        if ($cutiBentrok) {
            return response()->json([
                'message' => 'Ditolak! Anda sudah memiliki pengajuan cuti yang masih berstatus Pending/Approved pada tanggal tersebut.'
            ], 400);
        }

        $jenisCuti = JenisCuti::findOrFail($request->jenis_cuti_id);
        $namaCutiUtama = strtolower($jenisCuti->name_cuti ?? '');

        $namaSubCuti = '';
        if ($request->sub_cuti_id) {
            $subDb = SubCuti::find($request->sub_cuti_id);
            $namaSubCuti = $subDb ? strtolower($subDb->nama_sub_cuti) : '';
        }

        $genderUser = strtolower($user->gender_id ?? $user->gender->name ?? $user->gender ?? '');
        $isPria = ($genderUser === 'pria' || $genderUser === '1' || $genderUser === 'lki-laki' || $genderUser === 'male');

        if ($isPria) {
            if (str_contains($namaCutiUtama, 'melahirkan') || str_contains($namaSubCuti, 'melahirkan') || str_contains($namaSubCuti, 'gugur') || str_contains($namaSubCuti, 'haid')) {
                return response()->json([
                    'message' => 'Ditolak! Jenis perizinan/cuti ini hanya boleh diambil oleh karyawan wanita.',
                ], 403);
            }
        }

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $totalHari = $mulai->diffInDays($selesai) + 1;

        $apakahMemotongSaldo = $this->alurPotongSaldo($namaCutiUtama, $request->sub_cuti_id);

        if ($apakahMemotongSaldo) {
            $saldo = SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $request->jenis_cuti_id)
                ->where('tahun', Carbon::now()->year)
                ->first();

            $sisa = $saldo ? $saldo->sisa_saldo : 0;

            if ($sisa <= 0) {
                return response()->json([
                    'message' => 'Sisa kuota cuti anda sudah habis (0 hari).',
                ], 400);
            }

            if ($sisa < $totalHari) {
                return response()->json([
                    'message' => "Sisa kuota cuti anda hanya tinggal {$sisa} hari, sedangkan Anda mengajukan {$totalHari} hari.",
                    'debug_info' => [
                        'total_hari_diajukan' => $totalHari,
                        'saldo_tersisa' => $sisa,
                    ]
                ], 400);
            }
        }

        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $file = $request->file('dokumen_pendukung');
            $path = $file->store('dokumen_cuti', 'public');
            $namaDokumen = $path;
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

    public function show(Request $request, int $id)
    {
        $pengajuan = PengajuanCuti::with(['user', 'jenisCuti', 'subCuti'])->findOrFail($id);
        return response()->json(['data' => $pengajuan], 200);
    }

    public function detailCutiJSON(int $id, Request $request)
    {
        $cuti = DB::table('pengajuan_cutis')
            ->leftJoin('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->where('pengajuan_cutis.id', $id)
            ->where('pengajuan_cutis.user_id', $request->user()->id)
            ->select('pengajuan_cutis.*', 'jenis_cutis.name_cuti', 'sub_cutis.nama_sub_cuti')
            ->first();

        if (!$cuti) return response()->json(['message' => 'Data detail tidak ditemukan'], 404);

        $cuti->tanggal_mulai_formatted = Carbon::parse($cuti->tanggal_mulai)->format('d M Y');
        $cuti->tanggal_selesai_formatted = Carbon::parse($cuti->tanggal_selesai)->format('d M Y');

        return response()->json($cuti);
    }
}
