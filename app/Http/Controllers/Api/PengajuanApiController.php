<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendPushNotification;
use App\Models\Car\PengajuanCar;
use App\Models\Car\PengajuanCarDetail;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\Cuti\SubCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Models\Mpr\PengajuanMprDetail;
use App\Services\ApprovalRoutingService;
use App\Services\CalendarScheduleService;
use App\Traits\CutiHelperTrait;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengajuanApiController extends Controller
{
    use CutiHelperTrait;

    public function __construct(
        protected ApprovalRoutingService $approvalRouting,
        protected CalendarScheduleService $calendarService
    ) {}

    /**
     * Mengubah jadwal kerja awal / rutin user dari APK Native.
     */
    public function updateSchedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_type' => 'required|string|in:normal,roster',
            'current_shift_choice' => 'nullable|string|in:pagi,malam,libur',
            'normal_work_days' => 'nullable|array',
            'normal_work_days.*' => 'string|max:10',
            'normal_check_in' => 'nullable|string|max:10',
            'normal_check_out' => 'nullable|string|max:10',
            'roster_start_date' => 'nullable|date',
        ]);

        $user = $request->user();
        $isNormal = $validated['schedule_type'] === 'normal';

        $rosterStartDate = $validated['roster_start_date'] ?? $user->roster_start_date;
        if (!$isNormal && empty($validated['roster_start_date'])) {
            $now = Carbon::now('Asia/Jakarta');
            $tuesday = $now->copy()->startOfWeek()->addDay();
            if ($now->lt($tuesday->copy()->setTime(7, 0))) {
                $tuesday->subWeek();
            }
            $shiftOffset = match ($validated['current_shift_choice'] ?? 'pagi') {
                'malam' => 1,
                'libur' => 2,
                default => 0,
            };
            $rosterStartDate = $tuesday->subWeeks($shiftOffset)->toDateString();
        }

        $user->update([
            'schedule_type' => $validated['schedule_type'],
            'normal_work_days' => $isNormal ? ($validated['normal_work_days'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']) : null,
            'normal_check_in' => $isNormal ? ($validated['normal_check_in'] ?? $user->normal_check_in ?? '07:00:00') : null,
            'normal_check_out' => $isNormal ? ($validated['normal_check_out'] ?? $user->normal_check_out ?? '16:00:00') : null,
            'roster_start_date' => $isNormal ? null : $rosterStartDate,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal kerja berhasil disimpan.',
            'data' => [
                'schedule_type' => $user->schedule_type,
                'is_account_complete' => $user->isAccountComplete(),
                'completion_status' => $user->getAccountCompletionStatus(),
            ],
        ]);
    }

    /**
     * Menyimpan tanda tangan digital (base64 data URI atau file upload) dari APK Native.
     */
    public function updateSignature(Request $request): JsonResponse
    {
        $request->validate([
            'signature_file' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'signature_base64' => 'nullable|string',
        ]);

        $user = $request->user();
        $path = null;

        if ($request->hasFile('signature_file')) {
            $path = $request->file('signature_file')->store('signatures', 'public');
        } elseif ($request->filled('signature_base64')) {
            $raw = $request->signature_base64;
            if (preg_match('/^data:image\/(png|jpe?g);base64,/', $raw, $matches)) {
                $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                $binary = base64_decode(substr($raw, strpos($raw, ',') + 1), true);
                if ($binary === false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Format base64 tanda tangan tidak valid.',
                    ], 422);
                }
                $filename = 'signatures/ttd_' . $user->id . '_' . time() . '.' . $ext;
                Storage::disk('public')->put($filename, $binary);
                $path = $filename;
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format tanda tangan harus data URI PNG/JPG.',
                ], 422);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan unggah file atau gambar tanda tangan.',
            ], 422);
        }

        if ($user->signature && Storage::disk('public')->exists($user->signature)) {
            Storage::disk('public')->delete($user->signature);
        }

        $user->forceFill(['signature' => $path])->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Tanda tangan digital berhasil disimpan.',
            'data' => [
                'signature_path' => $path,
                'is_account_complete' => $user->isAccountComplete(),
                'completion_status' => $user->getAccountCompletionStatus(),
            ],
        ]);
    }

    /**
     * Daftar master jenis cuti beserta sub-cuti.
     */
    public function jenisCuti(): JsonResponse
    {
        $data = JenisCuti::with('subCutis')
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Daftar pengajuan cuti milik user yang sedang login.
     */
    public function cutiIndex(Request $request): JsonResponse
    {
        $items = PengajuanCuti::with(['jenisCuti:id,name_cuti', 'subCuti:id,name_cuti', 'approverTahap1:id,name', 'approverTahap2:id,name'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate((int) ($request->integer('per_page') ?: 15));

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    /**
     * Membuat pengajuan cuti baru dari APK Native.
     */
    public function cutiStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jenis_cuti_id' => 'required|exists:jenis_cutis,id',
            'sub_cuti_id' => 'nullable|exists:jenis_cutis,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan_cuti' => 'required|string|max:1000',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $user = $request->user();
        $jenisCutiId = (int) $validated['jenis_cuti_id'];
        $subCutiId = isset($validated['sub_cuti_id']) ? (int) $validated['sub_cuti_id'] : null;

        $jenisDb = JenisCuti::find($jenisCutiId);
        $subDb = $subCutiId ? SubCuti::find($subCutiId) : null;

        $mulai = Carbon::parse($validated['tanggal_mulai'], 'Asia/Jakarta')->startOfDay();
        $selesai = Carbon::parse($validated['tanggal_selesai'], 'Asia/Jakarta')->startOfDay();

        $totalHari = $this->hitungTotalHariCuti($user, $mulai, $selesai, $jenisDb, $subDb);
        if ($totalHari <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rentang tanggal yang dipilih tidak memiliki hari kerja efektif.',
            ], 422);
        }

        if ($subDb && str_contains(strtolower($subDb->nama_sub_cuti ?? ''), 'haid')) {
            if (!$user->isPerempuan()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cuti Haid hanya diperuntukkan bagi karyawan perempuan.',
                ], 422);
            }
            if ($mulai->format('Y-m') !== $selesai->format('Y-m')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengajuan Cuti Haid tidak boleh melintasi pergantian bulan.',
                ], 422);
            }
        }

        $route = $this->approvalRouting->resolveApprovalRoute($user, 'cuti');
        if (!$route['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $route['error'] ?? 'Alur persetujuan cuti belum dikonfigurasi.',
            ], 422);
        }

        $dokumenPath = $request->hasFile('dokumen_pendukung')
            ? $request->file('dokumen_pendukung')->store('dokumen_cuti', 'public')
            : null;

        $tahunSekarang = (int) $mulai->year;
        $bulanSekarang = (int) $mulai->month;

        DB::beginTransaction();
        try {
            if ($subCutiId && $subDb && str_contains(strtolower($subDb->nama_sub_cuti ?? ''), 'haid')) {
                $totalHaidBulanIni = DB::table('pengajuan_cutis')
                    ->where('user_id', $user->id)
                    ->where('sub_cuti_id', $subCutiId)
                    ->whereIn(DB::raw('LOWER(status_akhir)'), ['pending', 'approved'])
                    ->whereMonth('tanggal_mulai', $bulanSekarang)
                    ->whereYear('tanggal_mulai', $tahunSekarang)
                    ->lockForUpdate()
                    ->sum('total_hari');

                if (((int) $totalHaidBulanIni + $totalHari) > 2) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Batas jatah kuota Cuti Haid maksimal 2 hari per bulan.',
                    ], 422);
                }
            }

            if ($this->alurPotongSaldo($jenisCutiId, $subCutiId)) {
                $cutiTahunanId = $this->getCutiTahunanId();
                $saldo = SaldoCuti::where('user_id', $user->id)
                    ->where('jenis_cuti_id', $cutiTahunanId)
                    ->where('tahun', $tahunSekarang)
                    ->lockForUpdate()
                    ->first();

                if (!$saldo) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Sisa kuota cuti tahunan Anda belum diatur oleh admin.',
                    ], 422);
                }

                $this->validasiDanCekSaldo($user->id, $jenisCutiId, $subCutiId, $tahunSekarang, $totalHari);
            }

            $bentrok = DB::table('pengajuan_cutis')
                ->where('user_id', $user->id)
                ->whereIn(DB::raw('LOWER(status_akhir)'), ['pending', 'approved'])
                ->where(function ($q) use ($mulai, $selesai) {
                    $q->whereDate('tanggal_mulai', '<=', $selesai->toDateString())
                      ->whereDate('tanggal_selesai', '>=', $mulai->toDateString());
                })
                ->lockForUpdate()
                ->exists();

            if ($bentrok) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tanggal cuti bertabrakan dengan pengajuan cuti lain yang masih pending atau sudah disetujui.',
                ], 422);
            }

            $pengajuan = PengajuanCuti::create([
                'user_id' => $user->id,
                'jenis_cuti_id' => $jenisCutiId,
                'sub_cuti_id' => $subCutiId,
                'tanggal_mulai' => $mulai->toDateString(),
                'tanggal_selesai' => $selesai->toDateString(),
                'total_hari' => $totalHari,
                'alasan_cuti' => $validated['alasan_cuti'],
                'dokumen_pendukung' => $dokumenPath,
                'status_tahap_1' => $route['status_tahap_1'],
                'approver_tahap_1_id' => $route['status_tahap_1'] === 'approved' ? $user->id : null,
                'status_tahap_2' => $route['status_tahap_2'],
                'approver_tahap_2_id' => $route['status_tahap_2'] === 'approved' ? $user->id : null,
                'status_akhir' => $route['status_akhir'],
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        $this->notifyApproversStage1('cuti', $pengajuan, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan cuti berhasil dikirim.',
            'data' => $pengajuan->load(['jenisCuti:id,name_cuti', 'subCuti:id,name_cuti']),
        ], 201);
    }

    /**
     * Daftar pengajuan CAR milik user.
     */
    public function carIndex(Request $request): JsonResponse
    {
        $items = PengajuanCar::with('details')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate((int) ($request->integer('per_page') ?: 15));

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    /**
     * Membuat pengajuan CAR baru dari APK Native.
     */
    public function carStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'alasan_pembelian' => 'required|string|max:1000',
            'note_explanation' => 'nullable|string|max:1000',
            'receiving_account' => 'nullable|string|max:255',
            'tanggal_pengajuan' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string|max:255',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.satuan' => 'nullable|string|max:50',
            'items.*.estimasi_harga' => 'required|numeric|min:0',
            'items.*.ongkir' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();
        $route = $this->approvalRouting->resolveApprovalRoute($user, 'car');
        if (!$route['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $route['error'] ?? 'Alur persetujuan CAR belum dikonfigurasi.',
            ], 422);
        }

        $pengajuan = DB::transaction(function () use ($validated, $user, $route) {
            $car = PengajuanCar::create([
                'user_id' => $user->id,
                'nomor_car' => 'CAR/' . date('Ym') . '/' . strtoupper(substr(uniqid(), -5)),
                'tanggal_pengajuan' => $validated['tanggal_pengajuan'] ?? now('Asia/Jakarta')->toDateString(),
                'alasan_pembelian' => $validated['alasan_pembelian'],
                'note_explanation' => $validated['note_explanation'] ?? null,
                'receiving_account' => $validated['receiving_account'] ?? null,
                'total_approval_levels' => $route['levels'],
                'status_tahap_1' => $route['status_tahap_1'],
                'approver_tahap_1_id' => $route['status_tahap_1'] === 'approved' ? $user->id : null,
                'status_tahap_2' => $route['status_tahap_2'],
                'approver_tahap_2_id' => $route['status_tahap_2'] === 'approved' ? $user->id : null,
                'status_akhir' => $route['status_akhir'],
            ]);

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['jumlah'];
                $harga = (float) $item['estimasi_harga'];
                $ongkir = (float) ($item['ongkir'] ?? 0);

                PengajuanCarDetail::create([
                    'procurement_id' => $car->id,
                    'nama_barang' => $item['nama_barang'],
                    'jumlah' => $qty,
                    'satuan' => $item['satuan'] ?? 'PCS',
                    'estimasi_harga' => $harga,
                    'ongkir' => $ongkir,
                    'total_harga' => ($qty * $harga) + $ongkir,
                ]);
            }

            return $car;
        });

        $this->notifyApproversStage1('car', $pengajuan, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan CAR berhasil dikirim.',
            'data' => $pengajuan->load('details'),
        ], 201);
    }

    /**
     * Daftar pengajuan MPR milik user.
     */
    public function mprIndex(Request $request): JsonResponse
    {
        $items = PengajuanMpr::with('items')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate((int) ($request->integer('per_page') ?: 15));

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    /**
     * Membuat pengajuan MPR baru dari APK Native.
     */
    public function mprStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'priority' => 'nullable|in:Normal,Urgent,Emergency',
            'department' => 'nullable|string|max:100',
            'delivery_point' => 'nullable|string|max:255',
            'latest_mpr_date' => 'nullable|date',
            'tanggal_pengajuan' => 'nullable|date',
            'keperluan_urgensi' => 'required|string|max:1000',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string|max:255',
            'items.*.keterangan_item' => 'nullable|string|max:500',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.satuan' => 'nullable|string|max:50',
            'items.*.estimasi_harga' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();
        $route = $this->approvalRouting->resolveApprovalRoute($user, 'mpr');
        if (!$route['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $route['error'] ?? 'Alur persetujuan MPR belum dikonfigurasi.',
            ], 422);
        }

        $dokumenPath = $request->hasFile('dokumen_pendukung')
            ? $request->file('dokumen_pendukung')->store('dokumen_mpr', 'public')
            : null;

        $pengajuan = DB::transaction(function () use ($validated, $user, $route, $dokumenPath) {
            $mpr = PengajuanMpr::create([
                'user_id' => $user->id,
                'nomor_mpr' => 'MPR/' . date('Ym') . '/' . strtoupper(substr(uniqid(), -6)),
                'priority' => $validated['priority'] ?? 'Normal',
                'department' => $validated['department'] ?? 'Operation',
                'delivery_point' => $validated['delivery_point'] ?? 'Site Umbulan',
                'latest_mpr_date' => $validated['latest_mpr_date'] ?? null,
                'tanggal_pengajuan' => $validated['tanggal_pengajuan'] ?? now('Asia/Jakarta')->toDateString(),
                'keperluan_urgensi' => $validated['keperluan_urgensi'],
                'dokumen_pendukung' => $dokumenPath,
                'total_approval_levels' => $route['levels'],
                'status_tahap_1' => $route['status_tahap_1'],
                'approver_tahap_1_id' => $route['status_tahap_1'] === 'approved' ? $user->id : null,
                'status_tahap_2' => $route['status_tahap_2'],
                'approver_tahap_2_id' => $route['status_tahap_2'] === 'approved' ? $user->id : null,
                'status_akhir' => $route['status_akhir'],
            ]);

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['jumlah'];
                $harga = (float) ($item['estimasi_harga'] ?? 0);

                PengajuanMprDetail::create([
                    'procurement_id' => $mpr->id,
                    'nama_barang' => $item['nama_barang'],
                    'keterangan_item' => $item['keterangan_item'] ?? null,
                    'jumlah' => $qty,
                    'satuan' => $item['satuan'] ?? 'PCS',
                    'estimasi_harga' => $harga,
                    'total_harga' => $qty * $harga,
                ]);
            }

            return $mpr;
        });

        $this->notifyApproversStage1('mpr', $pengajuan, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan MPR berhasil dikirim.',
            'data' => $pengajuan->load('items'),
        ], 201);
    }

    private function notifyApproversStage1(string $module, $pengajuan, $pemohon): void
    {
        $approvers = $this->approvalRouting->getApproversForStage($pemohon, $module, 1);
        foreach ($approvers as $approver) {
            SendPushNotification::sendNewSubmission($module, $pengajuan, $approver, 1);
        }
    }

    private function hitungTotalHariCuti($user, Carbon $mulai, Carbon $selesai, ?JenisCuti $jenisDb, ?SubCuti $subDb): int
    {
        $namaJenis = strtolower($jenisDb->name_cuti ?? '');
        $namaSub = strtolower($subDb->nama_sub_cuti ?? '');

        // Cuti melahirkan atau roster dihitung kalender penuh sesuai durasi tanggal
        if (str_contains($namaSub, 'melahirkan') || str_contains($namaJenis, 'melahirkan') || $user->schedule_type === 'roster') {
            return (int) $mulai->diffInDays($selesai) + 1;
        }

        $holidays = array_keys($this->calendarService->getNationalHolidays($mulai->year));

        $count = 0;
        foreach (CarbonPeriod::create($mulai, $selesai) as $day) {
            if ($day->dayOfWeekIso >= 1 && $day->dayOfWeekIso <= 5 && !in_array($day->toDateString(), $holidays, true)) {
                $count++;
            }
        }

        return $count;
    }
}
