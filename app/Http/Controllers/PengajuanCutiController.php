<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\SaldoCuti;
use App\Models\JenisCuti;
use App\Models\SubCuti;
use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Traits\CutiHelperTrait;

class PengajuanCutiController extends Controller
{

    use CutiHelperTrait;

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

    /**
     * FUNGSI UTALITAS: Cek apakah pengajuan ini masuk kategori potong kuota.
     * Memotong saldo jika Jenis Cuti Utama adalah Cuti Tahunan (ID: 4) ATAU Sub-Cutinya adalah 'Haid'.
     */
    private function alurPotongSaldo(int $jenisCutiId, ?int $subCutiId): bool
    {
        if ($jenisCutiId === User::CUTI_TAHUNAN_ID) {
            return true;
        }

        if ($subCutiId) {
            $subCuti = SubCuti::find($subCutiId);
            if ($subCuti && strtolower($subCuti->nama_sub_cuti) === 'haid') {
                return true;
            }
        }

        return false;
    }

    private function sendWhatsAppNotification(?string $targetPhone, string $message)
    {
        if (!$targetPhone) {
            return false;
        }

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
            Log::error("Gagal mengirim WA ke atasan: " . $e->getMessage());
            return false;
        }
    }

    // KARYAWAN: Mengajukan Cuti (API)
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
        $tanggalMulaiBaru = Carbon::parse($request->tanggal_mulai)->format('Y-m-d');
        $tanggalSelesaiBaru = Carbon::parse($request->tanggal_selesai)->format('Y-m-d');

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $totalHari = $mulai->diffInDays($selesai) + 1;

        $jenisCutiId = $request->jenis_cuti_id;
        $subCutiId = $request->sub_cuti_id;
        $tahunSekarang = $mulai->year;
        $bulanSekarang = $mulai->month;

        $jenisCuti = JenisCuti::findOrFail($jenisCutiId);
        $namaCutiUtama = strtolower($jenisCuti->name_cuti ?? '');

        // 1. VALIDASI: Cek kuota sisa jatah efektif antrean menggunakan Trait
        if ($this->alurPotongSaldo($namaCutiUtama, $subCutiId)) {
            try {
                $this->validasiDanCekSaldo($user->id, $jenisCutiId, $subCutiId, $tahunSekarang, $totalHari);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 400);
            }
        }

        // 2. VALIDASI PROTEKSI: Batasi total pengajuan Cuti Haid maks 2 hari dalam sebulan
        if ($subCutiId) {
            $subDb = SubCuti::find($subCutiId);
            if ($subDb && strtolower($subDb->nama_sub_cuti) === 'haid') {
                $totalHaidBulanIni = PengajuanCuti::where('user_id', $user->id)
                    ->where('sub_cuti_id', $subCutiId)
                    ->whereIn('status_akhir', ['pending', 'approved'])
                    ->whereMonth('tanggal_mulai', $bulanSekarang)
                    ->whereYear('tanggal_mulai', $tahunSekarang)
                    ->sum('total_hari');

                if (($totalHaidBulanIni + $totalHari) > 2) {
                    return response()->json([
                        'message' => "Ditolak! Batas kuota Cuti Haid adalah 2 hari per bulan. Anda bulan ini sudah mengajukan/mengambil {$totalHaidBulanIni} hari."
                    ], 400);
                }
            }
        }

        // 3. VALIDASI: Cek bentrok tanggal
        $cutiBentrok = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->whereIn('status_akhir', ['pending', 'approved'])
            ->where(function ($query) use ($tanggalMulaiBaru, $tanggalSelesaiBaru) {
                $query->where(function ($q) use ($tanggalMulaiBaru) {
                    $q->where('tanggal_mulai', '<=', $tanggalMulaiBaru)
                      ->where('tanggal_selesai', '>=', $tanggalMulaiBaru);
                })
                ->orWhere(function ($q) use ($tanggalSelesaiBaru) {
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

        // 4. VALIDASI: Pembatasan Gender Karyawan Pria
        $namaSubCuti = '';
        if ($subCutiId) {
            $subDb = SubCuti::find($subCutiId);
            $namaSubCuti = $subDb ? strtolower($subDb->nama_sub_cuti) : '';
        }

        $genderUser = strtolower($user->gender->name ?? '');
        $isPria = in_array($genderUser, ['pria', '1', 'laki-laki', 'male']);
        if ($isPria) {
            if (str_contains($namaCutiUtama, 'melahirkan') || str_contains($namaSubCuti, 'melahirkan') || str_contains($namaSubCuti, 'gugur') || str_contains($namaSubCuti, 'haid')) {
                return response()->json([
                    'message' => 'Ditolak! Jenis perizinan/cuti ini hanya boleh diambil oleh karyawan wanita.',
                ], 403);
            }
        }

        // 5. PROSES UPLOAD DOKUMEN
        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $namaDokumen = $request->file('dokumen_pendukung')->store('dokumen_cuti', 'public');
        }

        // 6. PENENTUAN STATUS PERSETUJUAN BERDASARKAN ROLE
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

        // 7. EKSEKUSI DATABASE TRANSACTION
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanCuti::create([
                'user_id' => $user->id,
                'jenis_cuti_id' => $jenisCutiId,
                'sub_cuti_id' => $subCutiId,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'total_hari' => $totalHari,
                'alasan_cuti' => $request->alasan_cuti ?? '',
                'dokumen_pendukung' => $namaDokumen,
                'status_supervisor' => $statusSupervisor,
                'status_manager' => $statusManager,
                'status_akhir' => $statusAkhir,
            ]);

            // Jika role pengaju adalah Manager, otomatis memotong jatah saldo & input tabel absensi
            if ($statusAkhir === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }

            DB::commit();
            return response()->json(['message' => 'Pengajuan berhasil dikirim!', 'data' => $pengajuan], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    // KARYAWAN: Mengajukan Cuti via Web UI Form (WEB)
    public function storeWeb(Request $request)
    {
        $aturanDokumen = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';
        $request->validate([
            'jenis_cuti_id' => 'required|exists:jenis_cutis,id',
            'sub_cuti_id'   => 'nullable|exists:sub_cutis,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan_cuti' => 'nullable|string',
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

        $user = Auth::user();
        $tanggalMulaiBaru = Carbon::parse($request->tanggal_mulai)->format('Y-m-d');
        $tanggalSelesaiBaru = Carbon::parse($request->tanggal_selesai)->format('Y-m-d');

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);
        $totalHari = $mulai->diffInDays($selesai) + 1;

        $jenisCutiId = $request->jenis_cuti_id;
        $subCutiId = $request->sub_cuti_id;
        $tahunSekarang = Carbon::parse($request->tanggal_mulai)->year;
        $bulanSekarang = Carbon::parse($request->tanggal_mulai)->month;

        // VALIDASI PROTEKSI WEB: Batasi total pengajuan Cuti Haid maks 2 hari dalam sebulan
        if ($subCutiId) {
            $subDb = SubCuti::find($subCutiId);
            if ($subDb && strtolower($subDb->nama_sub_cuti) === 'haid') {
                $totalHaidBulanIni = PengajuanCuti::where('user_id', $user->id)
                    ->where('sub_cuti_id', $subCutiId)
                    ->whereIn('status_akhir', ['pending', 'approved'])
                    ->whereMonth('tanggal_mulai', $bulanSekarang)
                    ->whereYear('tanggal_mulai', $tahunSekarang)
                    ->sum('total_hari');

                if (($totalHaidBulanIni + $totalHari) > 2) {
                    return back()->withErrors(['error' => "Maaf, Batas jatah kuota Cuti Haid maksimal adalah 2 hari per bulan. Jatah yang telah Anda ajukan bulan ini: {$totalHaidBulanIni} hari."])->withInput();
                }
            }
        }

        $saldo = SaldoCuti::where('user_id', $user->id)
            ->where('jenis_cuti_id', $jenisCutiId)
            ->where('tahun', $tahunSekarang)
            ->first();

        if (!$saldo) {
            return redirect()->back()->withErrors(['error' => 'Sisa kuota cuti Anda belum diatur oleh admin untuk tahun ini!'])->withInput();
        }

        // Hitung sisa saldo efektif (Antrean Pending)
        if ($this->alurPotongSaldo($jenisCutiId, $subCutiId)) {
            $sisaSaldoDatabase = (int)$saldo->sisa_saldo;

            $totalCutiPending = DB::table('pengajuan_cutis')
                ->where('user_id', $user->id)
                ->where('jenis_cuti_id', $jenisCutiId)
                ->where('sub_cuti_id', $subCutiId)
                ->where('status_akhir', 'pending')
                ->sum('total_hari');

            $saldoEfektif = $sisaSaldoDatabase - $totalCutiPending;

            if ($saldoEfektif <= 0 || $saldoEfektif < $totalHari) {
                $pesanError = $saldoEfektif <= 0
                    ? "Maaf, kuota cuti Anda sudah habis atau seluruhnya sedang dalam antrean persetujuan."
                    : "Maaf, sisa kuota cuti efektif Anda hanya tinggal {$saldoEfektif} hari (terpotong antrean), sedangkan Anda mengajukan {$totalHari} hari.";
                return back()->withErrors(['error' => $pesanError])->withInput();
            }
        }

        // Cek bentrok tanggal
        $cutiBentrok = DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->whereIn('status_akhir', ['pending', 'approved'])
            ->where(function ($query) use ($tanggalMulaiBaru, $tanggalSelesaiBaru) {
                $query->where(function ($q) use ($tanggalMulaiBaru) {
                    $q->where('tanggal_mulai', '<=', $tanggalMulaiBaru)
                      ->where('tanggal_selesai', '>=', $tanggalMulaiBaru);
                })
                ->orWhere(function ($q) use ($tanggalSelesaiBaru) {
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
            return back()->withErrors(['error' => 'Ditolak! Anda sudah memiliki pengajuan cuti yang masih berstatus Pending/Approved pada tanggal tersebut.'])->withInput();
        }

        $jenisCuti = JenisCuti::findOrFail($jenisCutiId);
        $namaCutiUtama = strtolower($jenisCuti->name_cuti ?? '');

        $namaSubCuti = '';
        if ($subCutiId) {
            $subDb = SubCuti::find($subCutiId);
            $namaSubCuti = $subDb ? strtolower($subDb->nama_sub_cuti) : '';
        }

        $genderUser = strtolower($user->gender->name ?? '');
        $isPria = in_array($genderUser, ['pria', '1', 'laki-laki', 'male']);
        if ($isPria) {
            if (str_contains($namaCutiUtama, 'melahirkan') || str_contains($namaSubCuti, 'melahirkan') || str_contains($namaSubCuti, 'gugur') || str_contains($namaSubCuti, 'haid')) {
                return back()->withErrors(['error' => 'Ditolak! Jenis perizinan/cuti ini hanya boleh diambil oleh karyawan wanita.'])->withInput();
            }
        }

        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $namaDokumen = $request->file('dokumen_pendukung')->store('dokumen_cuti', 'public');
        }

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

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanCuti::create([
                'user_id' => $user->id,
                'jenis_cuti_id' => $jenisCutiId,
                'sub_cuti_id' => $subCutiId,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'total_hari' => $totalHari,
                'alasan_cuti' => $request->alasan_cuti ?? '',
                'dokumen_pendukung' => $namaDokumen,
                'status_supervisor' => $statusSupervisor,
                'status_manager' => $statusManager,
                'status_akhir' => $statusAkhir,
            ]);

            if ($statusAkhir === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }

            DB::commit();

            if ($statusAkhir === 'pending') {
                // PERBAIKAN: Cari target atasan di station yang sama berdasarkan nama Role-nya (Supervisor & Manager)
                $targetAtasan = User::where('station_id', $user->station_id)
                    ->whereHas('role', function($query) {
                        $query->whereIn(DB::raw('LOWER(role_name)'), ['supervisor', 'manager']);
                    })
                    ->whereNotNull('phone_verified_at')
                    ->get();

                // $namaStation = $user->station->nama_stasiun ?? 'Pusat / Utama';
                $namaStation = $user->station->name ?? 'Pusat / Utama';
                $perihal = $subCutiId && isset($subDb) ? $subDb->nama_sub_cuti : ($jenisCuti->name_cuti ?? 'Cuti/Izin');

                $templatePesan = "📢 *NOTIFIKASI PENGAJUAN " . strtoupper($perihal) . "*\n\n"
                    . "Halo Bapak/Ibu Atasan,\n"
                    . "Terdapat dokumen pengajuan baru yang membutuhkan persetujuan Anda.\n\n"
                    . "▪ *Nama Karyawan:* {$user->name}\n"
                    . "▪ *NIP:* " . ($user->nip ?? '-') . "\n"
                    . "▪ *Station:* {$namaStation}\n"
                    . "▪ *Tanggal:* {$request->tanggal_mulai} s/d {$request->tanggal_selesai} ({$totalHari} Hari)\n"
                    . "▪ *Alasan:* " . ($request->alasan_cuti ?? '-') . "\n\n"
                    . "Silakan kelola pengajuan ini melalui menu *Persetujuan Cuti* pada website.\n"
                    . "Link: " . url('/admin/persetujuan') . "\n\n"
                    . "_Pesan otomatis sistem META AdhyaTirta Umbulan._";

                foreach ($targetAtasan as $atasan) {
                    if ($atasan->phone_number) {
                        $this->sendWhatsAppNotification($atasan->phone_number, $templatePesan);
                    }
                }
            }

            return redirect()->route('dashboard')->with('success', 'Pengajuan cuti/ijin berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()])->withInput();
        }
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
            if ($pengajuan->status_manager === 'approved') {
                return response()->json(['message' => 'Pengajuan ini sudah disetujui sebelumnya.'], 400);
            }

            DB::beginTransaction();
            try {
                $pengajuan->update([
                    'status_manager' => $request->aksi,
                    'status_akhir' => $request->aksi,
                    'catatan_penolakan' => $request->aksi === 'rejected' ? $request->catatan_penolakan : null
                ]);

                if ($request->aksi === 'approved') {
                    $this->sinkronisasiCutiDanAbsen($pengajuan);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Gagal menyetujui: ' . $e->getMessage()], 400);
            }
        }

        return response()->json(['message' => 'Status pengajuan berhasil diperbarui oleh ' . $roleName]);
    }

    // DINAMIS: Mendukung pemotongan saldo bulanan (Cuti Haid lewat SubCuti) & Tahunan
    private function sinkronisasiCutiDanAbsen(PengajuanCuti $pengajuan)
    {
        if ($this->alurPotongSaldo($pengajuan->jenis_cuti_id, $pengajuan->sub_cuti_id)) {

            $saldo = SaldoCuti::where('user_id', $pengajuan->user_id)
                ->where('jenis_cuti_id', $pengajuan->jenis_cuti_id)
                ->where('tahun', Carbon::parse($pengajuan->tanggal_mulai)->year)
                ->lockForUpdate()
                ->first();

            if ($saldo) {
                $sisaJatah = (int)$saldo->sisa_saldo;
                $jumlahHariDipotong = (int)$pengajuan->total_hari;

                if ($sisaJatah <= 0 || $sisaJatah < $jumlahHariDipotong) {
                    throw new \Exception("Sisa saldo jatah cuti tidak mencukupi (Sisa: {$sisaJatah} hari, Diajukan: {$jumlahHariDipotong} hari).");
                }

                $saldo->decrement('sisa_saldo', $jumlahHariDipotong);
            } else {
                throw new \Exception("Data saldo jatah cuti/perizinan karyawan belum terdaftar di database.");
            }
        }

        $tanggalMulai = Carbon::parse($pengajuan->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($pengajuan->tanggal_selesai);

        for ($date = $tanggalMulai->copy(); $date->lte($tanggalSelesai); $date->addDay()) {
            Absensi::updateOrCreate(
                [
                    'user_id' => $pengajuan->user_id,
                    'tanggal' => $date->format('Y-m-d')
                ],
                [
                    'status_kehadiran' => 'Cuti',
                    'keterangan' => 'Cuti disetujui: ' . $pengajuan->alasan_cuti,
                    'jam_masuk' => null,
                    'jam_pulang' => null
                ]
            );
        }

        return true;
    }

    // Menampilkan Halaman Riwayat Cuti (Web View)
    public function riwayatView(Request $request)
    {
        $pengajuanCuti = DB::table('pengajuan_cutis')
            ->leftJoin('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->where('pengajuan_cutis.user_id', $request->user()->id)
            ->select(
                'pengajuan_cutis.*',
                'jenis_cutis.name_cuti',
                'sub_cutis.nama_sub_cuti'
            )
            ->orderBy('pengajuan_cutis.created_at', 'desc')
            ->get();

        $pengajuanCuti->each(function ($item) {
            $item->tanggal_mulai_formatted = Carbon::parse($item->tanggal_mulai)->format('d M Y');
            $item->tanggal_selesai_formatted = Carbon::parse($item->tanggal_selesai)->format('d M Y');
            $item->nama_sub_cuti = $item->nama_sub_cuti ?? '-';
        });

        return view('cuti.riwayat', compact('pengajuanCuti'));
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

        if (!$cuti) {
            return response()->json(['message' => 'Data detail tidak ditemukan'], 404);
        }

        $cuti->tanggal_mulai_formatted = Carbon::parse($cuti->tanggal_mulai)->format('d M Y');
        $cuti->tanggal_selesai_formatted = Carbon::parse($cuti->tanggal_selesai)->format('d M Y');

        return response()->json($cuti);
    }

    // Menampilkan Halaman List Pengajuan Masuk untuk Atasan (Web View)
    public function listPengajuan()
    {
        $atasan = Auth::user();

        // Mengamankan jika relasi role kosong
        $roleName = $atasan->role ? strtolower($atasan->role->role_name) : '';

        $query = DB::table('pengajuan_cutis')
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->select(
                'pengajuan_cutis.*',
                'pengajuan_cutis.id as id', // Mengunci agar 'id' yang terbaca tetap ID Pengajuan Cuti
                'users.name as user_name',
                'jenis_cutis.name_cuti',
                'sub_cutis.nama_sub_cuti',
                'users.station_id'
            )
            ->orderBy('pengajuan_cutis.created_at', 'desc');

        // Pengecekan berbasis role_name
        if ($roleName === 'supervisor') {
            $query->where('pengajuan_cutis.status_supervisor', 'pending')
                ->where('users.station_id', $atasan->station_id);

        } elseif ($roleName === 'manager') {
            $query->where('pengajuan_cutis.status_manager', 'pending')
                ->where('pengajuan_cutis.status_supervisor', 'approved');

        } elseif($roleName  === 'admin') {
            $query->where(function($q) {
                $q->where('pengajuan_cutis.status_supervisor', 'pending')
                ->orWhere('pengajuan_cutis.status_manager', 'pending');
            })
            ->where('pengajuan_cutis.status_supervisor', '!=', 'rejected')
            ->where('pengajuan_cutis.status_manager', '!=', 'rejected');
        }else {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $daftarPengajuan = $query->get();

        return view('admin.persetujuan.cuti', compact('daftarPengajuan'));
    }

    // ATASAN: Memproses Aksi Penyetujuan Bertingkat (Web View)
    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string'
        ]);

        $atasan = Auth::user();
        $tindakan = $request->tindakan;
        $pengajuan = PengajuanCuti::findOrFail($id);

        // Pembenaran 1 & 2: Amankan tipe data role dan ubah ke huruf kecil semua
        $roleName = $atasan->role ? strtolower($atasan->role->role_name) : '';

        if ($roleName === 'supervisor') {
            $pengajuan->update([
                'status_supervisor' => $tindakan,
                'status_akhir' => $tindakan === 'rejected' ? 'rejected' : 'pending',
                'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
            ]);
            return redirect()->back()->with('success', 'Status pengajuan cuti berhasil diperbarui');

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
                    'status_manager' => $tindakan,
                    'status_akhir' => $tindakan,
                    'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
                ]);

                // Pembenaran 3: Sinkronisasi absen & saldo hanya jika disetujui oleh Manager
                if ($tindakan === 'approved') {
                    $this->sinkronisasiCutiDanAbsen($pengajuan);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
            }
            return redirect()->back()->with('success', 'Status pengajuan cuti berhasil diperbarui');

        } else {
            return redirect()->back()->with('error', 'Gagal! Anda tidak memiliki hak akses sebagai atasan untuk mengubah status ini.');
        }
    }


    public function cetakSuratCuti(int $id)
    {
        $pengajuan = PengajuanCuti::with(['user'])->findOrFail($id);

        if ($pengajuan->status_manager !== 'approved') {
            return redirect()->back()->with('error', 'Surat cuti belum dapat dicetak karena belum disetujui sepenuhnya.');
        }

        $data = [
            'id' => $id,
            'title' => 'Surat Cuti - ' . $pengajuan->user->name,
            'pengajuan' => $pengajuan
        ];

        $pdf = Pdf::loadView('cuti.cetak', $data)
                ->setPaper('a4', 'portrait');

        return $pdf->stream('Surat-Cuti-' . $pengajuan->id . '.pdf');
    }

    public function handleSubCuti(int $id)
    {
        $jenis = JenisCuti::with('subCutis')->findOrFail($id);
        return response()->json($jenis->subCutis);
    }

    public function create()
    {
        $user = Auth::user();
        $jenisCuti = JenisCuti::with('subCutis')->get();

        $saldoTahunan = SaldoCuti::where('user_id', $user->id)
            ->where('jenis_cuti_id', 4)
            ->where('tahun', Carbon::now()->year)
            ->first();

        $sisaSaldo = $saldoTahunan ? $saldoTahunan->sisa_saldo : 0;

        return view('cuti.create', compact('jenisCuti', 'sisaSaldo'));
    }
}
