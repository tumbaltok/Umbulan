<?php

namespace App\Console\Commands;

use App\Models\Car\PengajuanCar;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Models\User\User;
use App\Services\ScheduleService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FollowUpPengajuanWhatsApp extends Command
{
    // Nama signature command artisan
    protected $signature = 'pengajuan:followup-wa';

    // Deskripsi singkat console command
    protected $description = 'Kirim pengingat WhatsApp berkala untuk pengajuan pending dengan proteksi jam kerja resmi (Work Hours Guard)';

    protected ScheduleService $scheduleService;
    protected WhatsAppService $whatsAppService;

    public function __construct(ScheduleService $scheduleService, WhatsAppService $whatsAppService)
    {
        parent::__construct();
        $this->scheduleService = $scheduleService;
        $this->whatsAppService = $whatsAppService;
    }

    // Eksekusi pemindaian antrean dan pengiriman pengingat WhatsApp
    public function handle(): int
    {
        $this->info('Memulai pemindaian pengajuan pending untuk follow-up WhatsApp...');

        // Cek status gateway terlebih dahulu
        $gatewayStatus = $this->whatsAppService->getStatus();
        if (($gatewayStatus['status'] ?? '') !== 'connected') {
            $this->warn('WhatsApp Gateway belum terhubung (Status: ' . ($gatewayStatus['status'] ?? 'offline') . '). Proses follow-up dibatalkan.');
            return Command::SUCCESS;
        }

        $cooldownThreshold = Carbon::now()->subHours(2);
        $minimumAge = Carbon::now()->subMinutes(30);

        $totalTerkirim = 0;
        $totalDitunda  = 0;

        // 1. Proses pengajuan cuti berstatus pending
        $cutiPending = PengajuanCuti::with(['user.roles', 'jenisCuti', 'subCuti'])
            ->where('status_akhir', 'pending')
            ->where('created_at', '<=', $minimumAge)
            ->where(function ($q) use ($cooldownThreshold) {
                $q->whereNull('last_notified_at')
                  ->orWhere('last_notified_at', '<=', $cooldownThreshold);
            })
            ->get();

        foreach ($cutiPending as $cuti) {
            $res = $this->prosesPengajuan('cuti', $cuti);
            $totalTerkirim += $res['sent'];
            $totalDitunda  += $res['postponed'];
        }

        // 2. Proses pengajuan CAR berstatus pending
        $carPending = PengajuanCar::with(['user.roles', 'details'])
            ->where('status_akhir', 'pending')
            ->where('created_at', '<=', $minimumAge)
            ->where(function ($q) use ($cooldownThreshold) {
                $q->whereNull('last_notified_at')
                  ->orWhere('last_notified_at', '<=', $cooldownThreshold);
            })
            ->get();

        foreach ($carPending as $car) {
            $res = $this->prosesPengajuan('car', $car);
            $totalTerkirim += $res['sent'];
            $totalDitunda  += $res['postponed'];
        }

        // 3. Proses pengajuan MPR berstatus pending
        $mprPending = PengajuanMpr::with(['user.roles', 'items'])
            ->where('status_akhir', 'pending')
            ->where('created_at', '<=', $minimumAge)
            ->where(function ($q) use ($cooldownThreshold) {
                $q->whereNull('last_notified_at')
                  ->orWhere('last_notified_at', '<=', $cooldownThreshold);
            })
            ->get();

        foreach ($mprPending as $mpr) {
            $res = $this->prosesPengajuan('mpr', $mpr);
            $totalTerkirim += $res['sent'];
            $totalDitunda  += $res['postponed'];
        }

        $this->info("Follow-up selesai: {$totalTerkirim} pesan terkirim, {$totalDitunda} pesan ditunda (di luar jam kerja).");

        return Command::SUCCESS;
    }

    // Memproses dokumen pengajuan, menentukan role approver, dan menerapkan proteksi jam kerja
    protected function prosesPengajuan(string $type, $pengajuan): array
    {
        $sentCount = 0;
        $postponedCount = 0;

        $submitter = $pengajuan->user;
        if (!$submitter) {
            return ['sent' => 0, 'postponed' => 0];
        }

        // Tentukan tahap persetujuan yang sedang pending
        $targetStage = null;
        if ($pengajuan->status_tahap_1 === 'pending') {
            $targetStage = 1;
        } elseif ($pengajuan->status_tahap_1 === 'approved' && $pengajuan->status_tahap_2 === 'pending') {
            $targetStage = 2;
        }

        if (!$targetStage) {
            return ['sent' => 0, 'postponed' => 0];
        }

        // Ambil aturan persetujuan dari role pemohon
        $typeRules = [];
        $generalRules = [];
        foreach ($submitter->roles as $r) {
            if (!empty($r->approval_rules[$type])) {
                $typeRules = $r->approval_rules[$type];
                $generalRules = $r->approval_rules;
                break;
            }
        }
        if (empty($typeRules) && $submitter->role) {
            $generalRules = $submitter->role->approval_rules ?? [];
            $typeRules = $generalRules[$type] ?? [];
        }

        $targetRoleId = null;
        if ($targetStage === 1) {
            $targetRoleId = $typeRules['approver_1_role_id'] ?? ($generalRules['approver_level_1_role_id'] ?? null);
        } elseif ($targetStage === 2) {
            $targetRoleId = $typeRules['approver_2_role_id'] ?? ($generalRules['approver_level_2_role_id'] ?? null);
        }

        if (!$targetRoleId) {
            return ['sent' => 0, 'postponed' => 0];
        }

        // Cari atasan yang memegang role tersebut dan memiliki nomor telepon terverifikasi
        $approvers = User::whereHas('roles', fn($q) => $q->where('roles.id', $targetRoleId))
            ->where('id', '!=', $submitter->id)
            ->whereNotNull('phone_verified_at')
            ->get();

        if ($approvers->isEmpty()) {
            return ['sent' => 0, 'postponed' => 0];
        }

        $notifiedAtLeastOne = false;

        foreach ($approvers as $approver) {
            // Validasi proteksi jam kerja approver (Work Hours Guard)
            $isWorking = $this->scheduleService->isUserWorkingNow($approver);

            if (!$isWorking) {
                $workingStatus = $this->scheduleService->getWorkingStatusText($approver);
                $this->line(" [TUNDA] Approver {$approver->name} sedang di luar jam kerja ({$workingStatus['label']}). Pesan ditunda.");
                $postponedCount++;
                continue;
            }

            // Atasan sedang dalam jam kerja aktif -> Kirim pengingat WhatsApp
            $this->line(" [KIRIM] Mengirim pengingat {$type} tahap {$targetStage} ke {$approver->name} ({$approver->phone_number})...");

            $sendResult = $this->whatsAppService->sendFollowUpNotification($type, $pengajuan, $approver, $targetStage);

            if ($sendResult['success'] ?? false) {
                $sentCount++;
                $notifiedAtLeastOne = true;
            }
        }

        // Catat timestamp pengingat untuk mencegah spam ganda
        if ($notifiedAtLeastOne) {
            $pengajuan->update(['last_notified_at' => Carbon::now()]);
        }

        return [
            'sent'      => $sentCount,
            'postponed' => $postponedCount,
        ];
    }
}
