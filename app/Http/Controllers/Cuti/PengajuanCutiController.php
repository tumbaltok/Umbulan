<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\Cuti\SubCuti;
use App\Models\User\User;
use App\Services\CalendarScheduleService;
use App\Services\ScheduleService;
use App\Traits\CutiHelperTrait;
use App\Jobs\SendWhatsAppNotification;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PengajuanCutiController extends Controller
{
    use CutiHelperTrait;

    protected ScheduleService $scheduleService;
    protected CalendarScheduleService $calendarScheduleService;

    public function __construct(ScheduleService $scheduleService, CalendarScheduleService $calendarScheduleService)
    {
        $this->scheduleService = $scheduleService;
        $this->calendarScheduleService = $calendarScheduleService;
    }

    // Menghitung jumlah hari kerja efektif (mengecualikan hari libur normal / roster)
    private function hitungHariKerjaEfektif(User $user, Carbon $tanggalMulai, Carbon $tanggalSelesai): int
    {
        $totalHariKerja = 0;
        $currentDate = $tanggalMulai->copy();

        $holidays = $this->calendarScheduleService->getNationalHolidays($tanggalMulai->year);

        while ($currentDate->lte($tanggalSelesai)) {
            $dateString = $currentDate->format('Y-m-d');
            $daySchedule = $this->scheduleService->getTodaySchedule($user, $dateString);

            if ($user->schedule_type !== 'roster') {
                // Jadwal Reguler (5 Hari & 6 Hari): Hari libur dan tanggal merah nasional tidak dihitung
                $isNationalHoliday = isset($holidays[$dateString]);
                if (!$daySchedule['is_day_off'] && !$isNationalHoliday) {
                    $totalHariKerja++;
                }
            } else {
                // Jadwal Roster: Hanya hari kerja aktif yang dihitung (libur roster dilewati)
                if (!$daySchedule['is_day_off'] && ($daySchedule['shift_type'] ?? '') !== 'libur') {
                    $totalHariKerja++;
                }
            }

            $currentDate->addDay();
        }

        return $totalHariKerja;
    }

    // Menampilkan formulir pengajuan cuti/izin baru
    public function create()
    {
        $user = Auth::user();
        if (!$user->isAccountComplete()) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda wajib melengkapi verifikasi email, nomor WhatsApp, biometrik wajah, tanda tangan digital (TTD), dan jadwal kerja sebelum dapat membuat pengajuan.');
        }

        $jenisCuti = JenisCuti::with('subCutis')->get();

        $cutiTahunan = JenisCuti::where('kode_cuti', 'CT')
            ->orWhere('name_cuti', 'LIKE', '%Tahunan%')
            ->first();

        $tahunSekarang = Carbon::now()->year;
        $saldoTahunan = null;
        if ($cutiTahunan) {
            $saldoTahunan = SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $cutiTahunan->id)
                ->where('tahun', $tahunSekarang)
                ->first();
        }

        $sisaSaldo = $saldoTahunan ? $saldoTahunan->sisa_saldo : 0;
        $kuotaAwal = $saldoTahunan ? $saldoTahunan->kuota_awal : ($cutiTahunan->kuota_default ?? 12);
        $cutiTerpakai = max(0, $kuotaAwal - $sisaSaldo);

        // Pengajuan cuti tahunan yang masih berstatus pending
        $pendingHari = (int) DB::table('pengajuan_cutis')
            ->where('user_id', $user->id)
            ->where('jenis_cuti_id', $cutiTahunan?->id ?? 4)
            ->where('status_akhir', 'pending')
            ->sum('total_hari');

        // Kalender hari libur nasional tahun ini dan tahun depan
        $holidays = $this->calendarScheduleService->getNationalHolidays($tahunSekarang);
        try {
            $nextYearHolidays = $this->calendarScheduleService->getNationalHolidays($tahunSekarang + 1);
            if (is_array($nextYearHolidays)) {
                $holidays = array_merge($holidays, $nextYearHolidays);
            }
        } catch (\Throwable $th) {
            // Abaikan jika kalender tahun depan belum tersedia
        }

        // Tanggal libur roster jika user memiliki pola shift roster
        $rosterOffDates = [];
        if ($user->schedule_type === 'roster' && !empty($user->roster_start_date)) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->addMonths(2)->endOfMonth();
            $curr = $startDate->copy();
            while ($curr->lte($endDate)) {
                $sched = $this->scheduleService->getTodaySchedule($user, $curr->format('Y-m-d'));
                if ($sched['is_day_off'] || ($sched['shift_type'] ?? '') === 'libur') {
                    $rosterOffDates[] = $curr->format('Y-m-d');
                }
                $curr->addDay();
            }
        }

        // 3 riwayat pengajuan cuti terakhir untuk intisari di sidebar
        $recentLeaves = PengajuanCuti::with(['jenisCuti', 'subCuti'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('cuti.cuticreate', compact(
            'jenisCuti',
            'sisaSaldo',
            'cutiTahunan',
            'saldoTahunan',
            'kuotaAwal',
            'cutiTerpakai',
            'pendingHari',
            'holidays',
            'rosterOffDates',
            'recentLeaves'
        ));
    }

    // Menyimpan pengajuan cuti/izin baru dari antarmuka web
    public function storeWeb(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAccountComplete()) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda wajib melengkapi verifikasi email, nomor WhatsApp, biometrik wajah, tanda tangan digital (TTD), dan jadwal kerja sebelum dapat membuat pengajuan.');
        }

        $aturanDokumen = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';
        $request->validate([
            'jenis_cuti_id' => 'required|exists:jenis_cutis,id',
            'sub_cuti_id'   => 'nullable|exists:sub_cutis,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan_cuti'   => 'nullable|string',
        ]);

        if ($request->sub_cuti_id) {
            $subCuti = SubCuti::find($request->sub_cuti_id);
            if ($subCuti && $subCuti->apakah_wajib_dokumen) {
                $aturanDokumen = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
            }
        }

        $request->validate([
            'dokumen_pendukung' => $aturanDokumen,
        ], [
            'dokumen_pendukung.required' => 'Dokumen pendukung wajib diunggah untuk jenis cuti yang Anda pilih.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $tanggalMulaiBaru = Carbon::parse($request->tanggal_mulai)->format('Y-m-d');
        $tanggalSelesaiBaru = Carbon::parse($request->tanggal_selesai)->format('Y-m-d');

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);

        $totalHari = $this->hitungHariKerjaEfektif($user, $mulai, $selesai);

        if ($totalHari === 0) {
            $pesanPeringatan = ($user->schedule_type === 'roster')
                ? '⚠️ Rentang tanggal yang Anda pilih bertepatan dengan Hari Libur Roster (Off Day) Anda dan tidak memotong kuota.'
                : '⚠️ Rentang tanggal yang Anda pilih bertepatan dengan Hari Libur Resmi / Tanggal Merah dan tidak memotong kuota.';

            return back()->withErrors(['error' => $pesanPeringatan])->withInput();
        }

        $jenisCutiId = $request->jenis_cuti_id;
        $subCutiId = $request->sub_cuti_id;
        $tahunSekarang = Carbon::parse($request->tanggal_mulai)->year;
        $bulanSekarang = Carbon::parse($request->tanggal_mulai)->month;

        if ($subCutiId) {
            $subDb = SubCuti::find($subCutiId);
            if ($subDb) {
                $namaSub = strtolower($subDb->nama_sub_cuti);

                // [L-04 FIX] Validasi Gender: Cuti Haid & Melahirkan hanya untuk Karyawan Perempuan (gender_id = 2)
                if (str_contains($namaSub, 'haid') || str_contains($namaSub, 'lahir')) {
                    if ((int)$user->gender_id !== 2) {
                        return back()->withErrors(['error' => 'Pengajuan ' . $subDb->nama_sub_cuti . ' hanya diperuntukkan bagi karyawan perempuan.'])->withInput();
                    }
                }

                if (str_contains($namaSub, 'haid')) {
                    // [SEC-06 FIX] Validasi Lintas Bulan Kalender (Cross-Month Boundary Guard)
                    $mulaiBulan = Carbon::parse($request->tanggal_mulai)->month;
                    $selesaiBulan = Carbon::parse($request->tanggal_selesai)->month;
                    if ($mulaiBulan !== $selesaiBulan) {
                        return back()->withErrors(['error' => 'Pengajuan Cuti Haid tidak boleh melintasi pergantian bulan kalender. Silakan buat pengajuan terpisah untuk masing-masing bulan.'])->withInput();
                    }
                }
            }
        }

        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $namaDokumen = $request->file('dokumen_pendukung')->store('dokumen_cuti', 'public');
        }

        // PENENTUAN STATUS AWAL BERDASARKAN DYNAMIC APPROVAL RULES DI ROLE PEMOHON
        $isTopLevel = $user->isTopLevel();

        // Cari rule cuti dari seluruh roles yang dimiliki user
        $cutiRules = [];
        $rules = [];
        foreach ($user->roles as $r) {
            if (!empty($r->approval_rules['cuti'])) {
                $cutiRules = $r->approval_rules['cuti'];
                $rules = $r->approval_rules;
                break;
            }
        }
        if (empty($cutiRules) && $user->role) {
            $rules = $user->role->approval_rules ?? [];
            $cutiRules = $rules['cuti'] ?? [];
        }

        $levels = (int) ($cutiRules['levels'] ?? ($rules['approval_levels'] ?? 1));
        $approver1RoleId = $cutiRules['approver_1_role_id'] ?? ($rules['approver_level_1_role_id'] ?? null);
        $approver2RoleId = $cutiRules['approver_2_role_id'] ?? ($rules['approver_level_2_role_id'] ?? null);

        if (empty($approver1RoleId) && $isTopLevel) {
            // Top Level (misal GM/Direksi tanpa approver) otomatis disetujui
            $statusTahap1 = 'approved';
            $statusTahap2 = 'not_required';
            $statusAkhir  = 'approved';
        } elseif (empty($approver1RoleId)) {
            // [SEC-04 FIX] Fail-Closed: Tolak jika alur approval belum dikonfigurasi
            return back()->withErrors([
                'error' => 'Alur persetujuan cuti untuk jabatan Anda belum dikonfigurasi oleh Administrator. Silakan hubungi admin HRD.'
            ])->withInput();
        } elseif ($levels === 2 && !empty($approver2RoleId)) {
            // Alur 2 Step Berjenjang
            $statusTahap1 = 'pending';
            $statusTahap2 = 'pending';
            $statusAkhir  = 'pending';
        } else {
            // Alur 1 Step
            $statusTahap1 = 'pending';
            $statusTahap2 = 'not_required';
            $statusAkhir  = 'pending';
        }

        DB::beginTransaction();
        try {
            // [SEC-06 FIX] Validasi Kuota Cuti Haid Atomik dengan Row-Locking di dalam transaksi DB
            if ($subCutiId && isset($subDb) && str_contains(strtolower($subDb->nama_sub_cuti), 'haid')) {
                $totalHaidBulanIni = DB::table('pengajuan_cutis')
                    ->where('user_id', $user->id)
                    ->where('sub_cuti_id', $subCutiId)
                    ->whereIn(DB::raw('LOWER(status_akhir)'), ['pending', 'approved'])
                    ->whereMonth('tanggal_mulai', $bulanSekarang)
                    ->whereYear('tanggal_mulai', $tahunSekarang)
                    ->lockForUpdate()
                    ->sum('total_hari');

                if (((int)$totalHaidBulanIni + $totalHari) > 2) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Batas jatah kuota Cuti Haid maksimal adalah 2 hari per bulan. Sisa kuota tidak mencukupi.'])->withInput();
                }
            }
            // [C-02 FIX] Validasi kuota saldo DI DALAM transaksi DB dengan pessimistic row-locking
            if ($this->alurPotongSaldo($jenisCutiId, $subCutiId)) {
                $cutiTahunanId = $this->getCutiTahunanId();
                $saldo = SaldoCuti::where('user_id', $user->id)
                    ->where('jenis_cuti_id', $cutiTahunanId)
                    ->where('tahun', $tahunSekarang)
                    ->lockForUpdate()
                    ->first();

                if (! $saldo) {
                    DB::rollBack();
                    return redirect()->back()->withErrors(['error' => 'Sisa kuota cuti tahunan Anda belum diatur oleh admin.'])->withInput();
                }

                $this->validasiDanCekSaldo($user->id, $jenisCutiId, $subCutiId, $tahunSekarang, $totalHari);
            }

            // [C-02 FIX] Pengecekan cuti bentrok di dalam transaksi ber-lock
            $cutiBentrok = DB::table('pengajuan_cutis')
                ->where('user_id', $user->id)
                ->whereIn(DB::raw('LOWER(status_akhir)'), ['pending', 'approved'])
                ->where(function ($query) use ($tanggalMulaiBaru, $tanggalSelesaiBaru) {
                    $query->where(function ($q) use ($tanggalMulaiBaru) {
                        $q->where('tanggal_mulai', '<=', $tanggalMulaiBaru)->where('tanggal_selesai', '>=', $tanggalMulaiBaru);
                    })
                        ->orWhere(function ($q) use ($tanggalSelesaiBaru) {
                            $q->where('tanggal_mulai', '<=', $tanggalSelesaiBaru)->where('tanggal_selesai', '>=', $tanggalSelesaiBaru);
                        })
                        ->orWhere(function ($q) use ($tanggalMulaiBaru, $tanggalSelesaiBaru) {
                            $q->where('tanggal_mulai', '>=', $tanggalMulaiBaru)->where('tanggal_selesai', '<=', $tanggalSelesaiBaru);
                        });
                })
                ->lockForUpdate()
                ->first();

            if ($cutiBentrok) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Ditolak! Terdapat pengajuan yang berstatus sama di tanggal tersebut.'])->withInput();
            }

            $pengajuan = PengajuanCuti::create([
                'user_id'             => $user->id,
                'jenis_cuti_id'       => $jenisCutiId,
                'sub_cuti_id'         => $subCutiId,
                'tanggal_mulai'       => $request->tanggal_mulai,
                'tanggal_selesai'     => $request->tanggal_selesai,
                'total_hari'          => $totalHari,
                'alasan_cuti'         => $request->alasan_cuti ?? '',
                'dokumen_pendukung'   => $namaDokumen,
                'status_tahap_1'      => $statusTahap1,
                'approver_tahap_1_id' => $statusTahap1 === 'approved' ? $user->id : null,
                'status_tahap_2'      => $statusTahap2,
                'approver_tahap_2_id' => $statusTahap2 === 'approved' ? $user->id : null,
                'status_akhir'        => $statusAkhir,
            ]);

            if ($statusAkhir === 'approved') {
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }

            DB::commit();

            // KIRIM NOTIFIKASI INSTAN WHATSAPP KE ATASAN TAHAP 1
            if ($statusTahap1 === 'pending' && !empty($approver1RoleId)) {
                try {
                    $approvers = User::whereHas('roles', fn($q) => $q->where('roles.id', $approver1RoleId))
                        ->where('id', '!=', $user->id)
                        ->whereNotNull('phone_verified_at')
                        ->get();

                    foreach ($approvers as $approver) {
                        SendWhatsAppNotification::sendNewSubmission('cuti', $pengajuan, $approver, 1);
                    }
                } catch (\Exception $waEx) {
                    Log::error('Gagal mengirim notifikasi WA cuti baru: ' . $waEx->getMessage());
                }
            }

            return redirect()->route('cuti.riwayat')->with('success', 'Pengajuan cuti/ijin berhasil dikirim!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal memproses pengajuan cuti user ID {$user->id}: " . $e->getMessage());

            // Tampilkan pesan validasi bisnis yang ramah, hindari ekspos internal SQL/sistem
            $errorMsg = ($e instanceof \Exception && $e->getCode() === 0 && !str_contains($e->getMessage(), 'SQLSTATE'))
                ? $e->getMessage()
                : 'Terjadi kesalahan sistem saat memproses pengajuan cuti Anda. Silakan coba kembali.';

            return back()->withErrors(['error' => $errorMsg])->withInput();
        }
    }

    // Menampilkan riwayat seluruh pengajuan cuti milik pengguna saat ini
    public function riwayatView(Request $request)
    {
        $pengajuanCuti = DB::table('pengajuan_cutis')
            ->leftJoin('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->where('pengajuan_cutis.user_id', $request->user()->id)
            ->select('pengajuan_cutis.*', 'jenis_cutis.name_cuti', 'sub_cutis.nama_sub_cuti')
            ->orderBy('pengajuan_cutis.created_at', 'desc')
            ->get();

        return view('cuti.cutiriwayat', compact('pengajuanCuti'));
    }

    // Mengambil rincian data pengajuan cuti via JSON [C-03 FIX IDOR]
    public function detailCutiJSON(int $id)
    {
        $cuti = PengajuanCuti::with(['jenisCuti', 'subCuti'])->findOrFail($id);
        $user = Auth::user();

        // [C-03 FIX] Validasi Kepemilikan & Hak Akses:
        // Hanya pemilik pengajuan, Atasan terkait, atau Admin Level 1 yang berhak mengakses
        $isOwner = ((int)$cuti->user_id === (int)$user->id);
        $isAdmin = $user->isLevel1();
        $isApprover = $user->isLevel2()
            || ((int)$cuti->approver_tahap_1_id === (int)$user->id)
            || ((int)$cuti->approver_tahap_2_id === (int)$user->id);

        if (!$isOwner && !$isAdmin && !$isApprover) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk melihat rincian pengajuan ini.',
            ], 403);
        }

        return response()->json([
            'name_cuti' => $cuti->jenisCuti->name_cuti ?? '-',
            'nama_sub_cuti' => $cuti->subCuti->nama_sub_cuti ?? null,
            'tanggal_mulai_formatted' => Carbon::parse($cuti->tanggal_mulai)->format('d M Y'),
            'tanggal_selesai_formatted' => Carbon::parse($cuti->tanggal_selesai)->format('d M Y'),
            'total_hari' => $cuti->total_hari,
            'alasan_cuti' => $cuti->alasan_cuti,
            'status_tahap_1' => $cuti->status_tahap_1,
            'status_tahap_2' => $cuti->status_tahap_2,
            'status_akhir' => $cuti->status_akhir,
            'catatan_penolakan' => $cuti->catatan_penolakan,
            'dokumen_pendukung' => $cuti->dokumen_pendukung,
        ]);
    }

    // Menampilkan halaman pratinjau surat cuti
    public function viewSuratCuti(int $id)
    {
        $pengajuan = PengajuanCuti::with(['user'])->findOrFail($id);
        $user = Auth::user();

        // Validasi Otorisasi: Hanya pemilik, approver terkait, atau Admin Level 1
        $isOwner = ((int)$pengajuan->user_id === (int)$user->id);
        $isAdmin = $user->isLevel1();
        $isApprover = $user->isLevel2()
            || ((int)$pengajuan->approver_tahap_1_id === (int)$user->id)
            || ((int)$pengajuan->approver_tahap_2_id === (int)$user->id);

        if (!$isOwner && !$isAdmin && !$isApprover) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak untuk melihat surat cuti ini.');
        }

        if ($pengajuan->status_akhir !== 'approved') {
            return redirect()->back()->with('error', 'Surat cuti belum dapat dilihat karena belum disetujui sepenuhnya.');
        }

        return view('cuti.pembungkus_pdf', [
            'id' => $id,
            'title' => 'Surat Cuti - ' . $pengajuan->user->name,
        ]);
    }

    // Menghasilkan dokumen PDF cetak surat cuti [C-04 FIX IDOR]
    public function cetakSuratCuti(int $id)
    {
        $pengajuan = PengajuanCuti::with([
            'user.role',
            'user.station',
            'jenisCuti',
            'subCuti',
            'approverTahap1.role',
            'approverTahap2.role'
        ])->findOrFail($id);

        $user = Auth::user();

        // [C-04 FIX] Validasi Otorisasi: Hanya pemilik pengajuan, approver terkait, atau Admin Level 1
        $isOwner = ((int)$pengajuan->user_id === (int)$user->id);
        $isAdmin = $user->isLevel1();
        $isApprover = $user->isLevel2()
            || ((int)$pengajuan->approver_tahap_1_id === (int)$user->id)
            || ((int)$pengajuan->approver_tahap_2_id === (int)$user->id);

        if (!$isOwner && !$isAdmin && !$isApprover) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak untuk mencetak surat cuti ini.');
        }

        if ($pengajuan->status_akhir !== 'approved') {
            return redirect()->back()->with('error', 'Surat cuti belum disetujui sepenuhnya.');
        }

        $approverLevel1 = $pengajuan->approverTahap1;

        if ($pengajuan->status_tahap_2 === 'not_required' || empty($pengajuan->approver_tahap_2_id)) {
            $approverLevel2 = $approverLevel1;
        } else {
            $approverLevel2 = $pengajuan->approverTahap2;
        }

        $data = [
            'id'             => $id,
            'title'          => 'Surat Cuti - ' . $pengajuan->user->name,
            'pengajuan'      => $pengajuan,
            'approverLevel1' => $approverLevel1,
            'approverLevel2' => $approverLevel2,
        ];

        $pdf = Pdf::loadView('cuti.cuticetak', $data)->setPaper('a4', 'portrait');

        return $pdf->stream('Surat-Cuti-' . str_replace(' ', '_', $pengajuan->user->name) . '.pdf');
    }

    // Mengambil daftar sub-cuti berdasarkan ID jenis cuti induk (JSON)
    public function handleSubCuti(int $id)
    {
        $jenis = JenisCuti::with('subCutis')->findOrFail($id);

        return response()->json($jenis->subCutis);
    }
}
