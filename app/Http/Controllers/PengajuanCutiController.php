<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\SaldoCuti;
use App\Models\JenisCuti;
use App\Models\SubCuti;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Traits\CutiHelperTrait;
use App\Services\ScheduleService;
use App\Services\CalendarScheduleService;

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
                if (!$daySchedule['is_day_off'] && !$isNationalHoliday) {
                    $totalHariKerja++;
                }
            } else {
                if (!$daySchedule['is_day_off']) {
                    $totalHariKerja++;
                }
            }

            $currentDate->addDay();
        }

        return $totalHariKerja;
    }

    private function sendWhatsAppNotification(?string $targetPhone, string $message)
    {
        if (!$targetPhone) return false;

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
            Log::error("Gagal mengirim WA: " . $e->getMessage());
            return false;
        }
    }

    public function create()
    {
        $user = Auth::user();
        $jenisCuti = JenisCuti::with('subCutis')->get();

        // Cari ID Cuti Tahunan secara dinamis berdasarkan kode/nama
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

        return view('cuti.create', compact('jenisCuti', 'sisaSaldo'));
    }

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
                    return back()->withErrors(['error' => "Batas jatah kuota Cuti Haid maksimal adalah 2 hari per bulan."])->withInput();
                }
            }
        }

        // Menggunakan alurPotongSaldo dari CutiHelperTrait
        if ($this->alurPotongSaldo($jenisCutiId, $subCutiId)) {
            $saldo = SaldoCuti::where('user_id', $user->id)
                ->where('jenis_cuti_id', $jenisCutiId)
                ->where('tahun', $tahunSekarang)
                ->first();

            if (!$saldo) {
                return redirect()->back()->withErrors(['error' => 'Sisa kuota cuti Anda belum diatur oleh admin.'])->withInput();
            }

            try {
                // Menggunakan validasiDanCekSaldo dari CutiHelperTrait
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
            return back()->withErrors(['error' => "Ditolak! Terdapat pengajuan yang berstatus sama di tanggal tersebut."])->withInput();
        }

        $namaDokumen = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $namaDokumen = $request->file('dokumen_pendukung')->store('dokumen_cuti', 'public');
        }

        $roleName = strtolower($user->role->role_name ?? '');
        $statusSupervisor = 'pending';
        $statusManager    = 'pending';
        $statusAkhir      = 'pending';

        if (in_array($roleName, ['manager', 'hrd', 'full akses'])) {
            $statusSupervisor = 'approved';
            $statusManager    = 'approved';
            $statusAkhir      = 'approved';
        } elseif ($roleName === 'supervisor') {
            $statusSupervisor = 'approved';
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
                // Dipanggil dari CutiHelperTrait
                $this->sinkronisasiCutiDanAbsen($pengajuan);
            }

            DB::commit();

            return redirect()->route('cuti.riwayat')->with('success', 'Pengajuan cuti/ijin berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()])->withInput();
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

        return view('cuti.riwayat', compact('pengajuanCuti'));
    }

    public function listPengajuan()
    {
        $atasan = Auth::user();
        $roleName = $atasan->role ? strtolower($atasan->role->role_name) : '';

        $query = DB::table('pengajuan_cutis')
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('jenis_cutis', 'pengajuan_cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
            ->leftJoin('sub_cutis', 'pengajuan_cutis.sub_cuti_id', '=', 'sub_cutis.id')
            ->select(
                'pengajuan_cutis.*',
                'users.name as user_name',
                'jenis_cutis.name_cuti',
                'sub_cutis.nama_sub_cuti',
                'users.station_id'
            )
            ->orderBy('pengajuan_cutis.created_at', 'desc');

        if ($roleName === 'supervisor') {
            $query->where('pengajuan_cutis.status_supervisor', 'pending')->where('users.station_id', $atasan->station_id);
        } elseif (in_array($roleName, ['manager', 'full akses'])) {
            $query->where('pengajuan_cutis.status_manager', 'pending')->where('pengajuan_cutis.status_supervisor', 'approved');
        }

        $daftarPengajuan = $query->get();
        return view('admin.persetujuan.cuti', compact('daftarPengajuan'));
    }

    public function prosesPersetujuan(Request $request, int $id)
    {
        $request->validate([
            'tindakan' => 'required|in:approved,rejected',
            'catatan_penolakan' => 'nullable|string'
        ]);

        $atasan = Auth::user();
        $tindakan = $request->tindakan;
        $pengajuan = PengajuanCuti::findOrFail($id);
        $roleName = $atasan->role ? strtolower($atasan->role->role_name) : '';

        if ($roleName === 'supervisor') {
            $pengajuan->update([
                'status_supervisor' => $tindakan,
                'supervisor_id'     => $tindakan === 'approved' ? $atasan->id : null,
                'status_akhir'      => $tindakan === 'rejected' ? 'rejected' : 'pending',
                'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
            ]);
            return redirect()->back()->with('success', 'Status pengajuan cuti berhasil diperbarui');
        } elseif (in_array($roleName, ['manager', 'hrd', 'full akses'])) {
            DB::beginTransaction();
            try {
                $pengajuan->update([
                    'status_manager'    => $tindakan,
                    'manager_id'        => $tindakan === 'approved' ? $atasan->id : null,
                    'status_akhir'      => $tindakan,
                    'catatan_penolakan' => $tindakan === 'rejected' ? $request->catatan_penolakan : null
                ]);

                if ($tindakan === 'approved') {
                    // Dipanggil dari CutiHelperTrait
                    $this->sinkronisasiCutiDanAbsen($pengajuan);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal memproses: ' . $e->getMessage());
            }
            return redirect()->back()->with('success', 'Status pengajuan cuti berhasil diperbarui');
        }

        return redirect()->back()->with('error', 'Akses ditolak.');
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
            'status_supervisor' => $cuti->status_supervisor,
            'status_manager' => $cuti->status_manager,
            'status_akhir' => $cuti->status_akhir,
            'catatan_penolakan' => $cuti->catatan_penolakan,
            'dokumen_pendukung' => $cuti->dokumen_pendukung
        ]);
    }

    public function cetakSuratCuti(int $id)
    {
        $pengajuan = PengajuanCuti::with(['user', 'supervisor', 'manager'])->findOrFail($id);

        if ($pengajuan->status_manager !== 'approved' && $pengajuan->status_akhir !== 'approved') {
            return redirect()->back()->with('error', 'Surat cuti belum disetujui sepenuhnya.');
        }

        $data = [
            'id' => $id,
            'title' => 'Surat Cuti - ' . $pengajuan->user->name,
            'pengajuan' => $pengajuan
        ];

        $pdf = Pdf::loadView('cuti.cetak', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Surat-Cuti-' . $pengajuan->id . '.pdf');
    }

    public function handleSubCuti(int $id)
    {
        $jenis = JenisCuti::with('subCutis')->findOrFail($id);
        return response()->json($jenis->subCutis);
    }
}