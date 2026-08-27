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

    private function hitungHariKerjaEfektif(User $user, Carbon $tanggalMulai, Carbon $tanggalSelesai): int
    {
        $totalHariKerja = 0;
        $currentDate = $tanggalMulai->copy();

        $holidays = $this->calendarScheduleService->getNationalHolidays($tanggalMulai->year);

        while ($currentDate->lte($tanggalSelesai)) {
            $dateString = $currentDate->format('Y-m-d');
            $daySchedule = $this->scheduleService->getTodaySchedule($user, $dateString);

            if ($user->schedule_type === 'normal') {
                $isNationalHoliday = isset($holidays[$dateString]);
                if (! $daySchedule['is_day_off'] && ! $isNationalHoliday) {
                    $totalHariKerja++;
                }
            } else {
                if (! $daySchedule['is_day_off']) {
                    $totalHariKerja++;
                }
            }

            $currentDate->addDay();
        }

        return $totalHariKerja;
    }

    private function sendWhatsAppNotification(?string $targetPhone, string $message)
    {
        if (! $targetPhone) {
            return false;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (isset($cleanPhone[0]) && $cleanPhone[0] === '0') {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $cleanPhone,
                'message' => $message,
                'all' => 'true',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Gagal mengirim WA: '.$e->getMessage());

            return false;
        }
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->isAccountComplete()) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda wajib melengkapi verifikasi nomor WhatsApp, verifikasi email, pengaturan jadwal kerja, dan biometrik wajah sebelum dapat membuat pengajuan.');
        }

        $jenisCuti = JenisCuti::with('subCutis')->get();

        $cutiTahunan = JenisCuti::where('kode_cuti', 'CT')
            ->orWhere('name_cuti', 'LIKE', '%Tahunan%')
            ->first();

        $saldoTahunan = null;
        if ($cutiTahunan) {
            $saldoTahunan = SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $cutiTahunan->id)
                ->where('tahun', Carbon::now()->year)
                ->first();
        }

        $sisaSaldo = $saldoTahunan ? $saldoTahunan->sisa_saldo : 0;

        return view('cuti.cuticreate', compact('jenisCuti', 'sisaSaldo'));
    }

    public function storeWeb(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAccountComplete()) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda wajib melengkapi verifikasi nomor WhatsApp, verifikasi email, pengaturan jadwal kerja, dan biometrik wajah sebelum dapat membuat pengajuan.');
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
            return back()->withErrors(['error' => 'Tanggal yang Anda pilih seluruhnya adalah hari libur.'])->withInput();
        }

        $jenisCutiId = $request->jenis_cuti_id;
        $subCutiId = $request->sub_cuti_id;
        $tahunSekarang = Carbon::parse($request->tanggal_mulai)->year;
        $bulanSekarang = Carbon::parse($request->tanggal_mulai)->month;

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
                    return back()->withErrors(['error' => 'Batas jatah kuota Cuti Haid maksimal adalah 2 hari per bulan.'])->withInput();
                }
            }
        }

        if ($this->alurPotongSaldo($jenisCutiId, $subCutiId)) {
            $saldo = SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $jenisCutiId)
                ->where('tahun', $tahunSekarang)
                ->first();

            if (! $saldo) {
                return redirect()->back()->withErrors(['error' => 'Sisa kuota cuti Anda belum diatur oleh admin.'])->withInput();
            }

            try {
                $this->validasiDanCekSaldo($user->id, $jenisCutiId, $subCutiId, $tahunSekarang, $totalHari);
            } catch (\Exception $e) {
                return back()->withErrors(['error' => $e->getMessage()])->withInput();
            }
        }

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
            ->first();

        if ($cutiBentrok) {
            return back()->withErrors(['error' => 'Ditolak! Terdapat pengajuan yang berstatus sama di tanggal tersebut.'])->withInput();
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

            return redirect()->route('cuti.riwayat')->with('success', 'Pengajuan cuti/ijin berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: '.$e->getMessage()])->withInput();
        }
    }

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

    public function detailCutiJSON(int $id)
    {
        $cuti = PengajuanCuti::with(['jenisCuti', 'subCuti'])->findOrFail($id);

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

    public function handleSubCuti(int $id)
    {
        $jenis = JenisCuti::with('subCutis')->findOrFail($id);

        return response()->json($jenis->subCutis);
    }
}
