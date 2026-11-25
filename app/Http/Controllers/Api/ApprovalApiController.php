<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendPushNotification;
use App\Models\Car\PengajuanCar;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Services\ApprovalRoutingService;
use App\Traits\CutiHelperTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalApiController extends Controller
{
    use CutiHelperTrait;

    public function __construct(protected ApprovalRoutingService $approvalRouting) {}

    /**
     * Mengambil daftar pengajuan (Cuti, CAR, MPR) yang menunggu persetujuan atasan saat ini.
     */
    public function pending(Request $request): JsonResponse
    {
        $request->validate([
            'module' => 'nullable|in:cuti,car,mpr',
        ]);

        $user = $request->user();
        $moduleFilter = $request->query('module');

        $data = [];

        if (!$moduleFilter || $moduleFilter === 'cuti') {
            $data['cuti'] = $this->approvalRouting
                ->applyPendingApprovalScope(
                    PengajuanCuti::with(['user:id,name,nip,station_id', 'jenisCuti:id,name_cuti', 'subCuti:id,name_cuti']),
                    $user,
                    'cuti'
                )
                ->latest()
                ->get();
        }

        if (!$moduleFilter || $moduleFilter === 'car') {
            $data['car'] = $this->approvalRouting
                ->applyPendingApprovalScope(
                    PengajuanCar::with(['user:id,name,nip,station_id', 'details']),
                    $user,
                    'car'
                )
                ->latest()
                ->get();
        }

        if (!$moduleFilter || $moduleFilter === 'mpr') {
            $data['mpr'] = $this->approvalRouting
                ->applyPendingApprovalScope(
                    PengajuanMpr::with(['user:id,name,nip,station_id', 'items']),
                    $user,
                    'mpr'
                )
                ->latest()
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Memproses persetujuan atau penolakan pengajuan (cuti / car / mpr) secara atomik.
     */
    public function process(Request $request, string $module, int $id): JsonResponse
    {
        if (!in_array($module, ['cuti', 'car', 'mpr'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Modul pengajuan tidak valid.',
            ], 404);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'catatan' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        $user = $request->user();
        $action = $validated['action'];
        $catatan = $validated['catatan'] ?? null;

        DB::beginTransaction();
        try {
            $modelClass = match ($module) {
                'cuti' => PengajuanCuti::class,
                'car' => PengajuanCar::class,
                'mpr' => PengajuanMpr::class,
            };

            $submission = $modelClass::with('user')->where('id', $id)->lockForUpdate()->first();
            if (!$submission) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pengajuan tidak ditemukan.',
                ], 404);
            }

            if (strtolower((string) $submission->status_akhir) !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengajuan ini sudah selesai diproses sebelumnya.',
                ], 422);
            }

            $authCheck = $this->approvalRouting->isUserAuthorizedToApprove($user, $submission, $module);
            if (!$authCheck['authorized']) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $authCheck['reason'] ?? 'Anda tidak berwenang memproses pengajuan ini.',
                ], 403);
            }

            $isStage1Pending = strtolower((string) $submission->status_tahap_1) === 'pending';
            $nextStageApproverNotify = false;

            if ($action === 'reject') {
                if ($isStage1Pending) {
                    $submission->status_tahap_1 = 'rejected';
                    $submission->approver_tahap_1_id = $user->id;
                } else {
                    $submission->status_tahap_2 = 'rejected';
                    $submission->approver_tahap_2_id = $user->id;
                }
                $submission->status_akhir = 'rejected';
                $submission->catatan_penolakan = $catatan;
            } else {
                if ($isStage1Pending) {
                    $submission->status_tahap_1 = 'approved';
                    $submission->approver_tahap_1_id = $user->id;

                    if (strtolower((string) $submission->status_tahap_2) === 'not_required') {
                        $submission->status_akhir = 'approved';
                    } else {
                        $nextStageApproverNotify = true;
                    }
                } else {
                    $submission->status_tahap_2 = 'approved';
                    $submission->approver_tahap_2_id = $user->id;
                    $submission->status_akhir = 'approved';
                }
            }

            // Sinkronisasi saldo & jadwal absen bila pengajuan cuti mencapai persetujuan final
            if ($module === 'cuti' && $submission->status_akhir === 'approved') {
                if ($this->alurPotongSaldo((int) $submission->jenis_cuti_id, $submission->sub_cuti_id ? (int) $submission->sub_cuti_id : null)) {
                    $cutiTahunanId = $this->getCutiTahunanId();
                    $tahun = Carbon::parse($submission->tanggal_mulai)->year;
                    $saldo = SaldoCuti::where('user_id', $submission->user_id)
                        ->where('jenis_cuti_id', $cutiTahunanId)
                        ->where('tahun', $tahun)
                        ->lockForUpdate()
                        ->first();

                    if (!$saldo || $saldo->sisa_saldo < $submission->total_hari) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Saldo cuti tahunan pemohon tidak mencukupi untuk disetujui.',
                        ], 422);
                    }

                    $saldo->decrement('sisa_saldo', (int) $submission->total_hari);
                }

                $this->sinkronisasiCutiDanAbsen($submission);
            }

            $submission->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses persetujuan: ' . $e->getMessage(),
            ], 500);
        }

        // Dispatch Push Notification ke Approver Tahap 2 atau ke Pemohon
        if ($nextStageApproverNotify && $submission->user) {
            $approversTahap2 = $this->approvalRouting->getApproversForStage($submission->user, $module, 2);
            foreach ($approversTahap2 as $approver2) {
                SendPushNotification::sendNewSubmission($module, $submission, $approver2, 2);
            }
        }

        if (in_array($submission->status_akhir, ['approved', 'rejected'], true)) {
            SendPushNotification::sendStatusUpdate($module, $submission, $submission->status_akhir, $catatan);
        }

        return response()->json([
            'status' => 'success',
            'message' => $action === 'approve' ? 'Pengajuan berhasil disetujui.' : 'Pengajuan berhasil ditolak.',
            'data' => $submission->fresh(),
        ]);
    }
}
