<?php

namespace App\Console\Commands;

use App\Models\Car\PengajuanCar;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Services\ApprovalRoutingService;
use App\Services\PushNotificationService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FollowUpPengajuanNotification extends Command
{
    protected $signature = 'pengajuan:followup-notif';

    protected $description = 'Kirim pengingat Push Notification & in-app berkala untuk pengajuan pending dengan proteksi jam kerja resmi';

    protected ScheduleService $scheduleService;
    protected PushNotificationService $pushService;
    protected ApprovalRoutingService $routingService;

    public function __construct(ScheduleService $scheduleService, PushNotificationService $pushService, ApprovalRoutingService $routingService)
    {
        parent::__construct();
        $this->scheduleService = $scheduleService;
        $this->pushService = $pushService;
        $this->routingService = $routingService;
    }

    public function handle(): int
    {
        $this->info('Memulai pemindaian pengajuan pending untuk follow-up Push Notification...');

        $cooldownThreshold = Carbon::now()->subHours(2);
        $minimumAge = Carbon::now()->subMinutes(30);

        $totalTerkirim = 0;

        foreach (['cuti' => PengajuanCuti::with(['user.roles', 'jenisCuti', 'subCuti']),
                  'car' => PengajuanCar::with(['user.roles', 'details']),
                  'mpr' => PengajuanMpr::with(['user.roles', 'items'])] as $type => $query) {
            $pendingItems = $query
                ->where('status_akhir', 'pending')
                ->where('created_at', '<=', $minimumAge)
                ->where(function ($q) use ($cooldownThreshold) {
                    $q->whereNull('last_notified_at')
                      ->orWhere('last_notified_at', '<=', $cooldownThreshold);
                })
                ->get();

            foreach ($pendingItems as $item) {
                $totalTerkirim += $this->prosesPengajuan($type, $item);
            }
        }

        $this->info("Selesai. Notifikasi Terkirim: {$totalTerkirim}");
        return Command::SUCCESS;
    }

    private function prosesPengajuan(string $type, $pengajuan): int
    {
        $pemohon = $pengajuan->user;
        if (!$pemohon) {
            return 0;
        }

        $tahapAktif = ($pengajuan->status_tahap_1 === 'pending') ? 1 : 2;
        if ($tahapAktif === 2 && $pengajuan->status_tahap_2 !== 'pending') {
            return 0;
        }

        $approvers = $this->routingService->getApproversForStage($pemohon, $type, $tahapAktif);
        if ($approvers->isEmpty()) {
            return 0;
        }

        $now = Carbon::now('Asia/Jakarta');
        $sent = 0;

        foreach ($approvers as $approver) {
            $schedule = $this->scheduleService->getTodaySchedule($approver, $now);
            $isWorkingNow = false;
            if (!($schedule['is_day_off'] ?? false) && !empty($schedule['scheduled_in']) && !empty($schedule['scheduled_out'])) {
                $inTime = Carbon::parse($now->format('Y-m-d') . ' ' . $schedule['scheduled_in'], 'Asia/Jakarta');
                $outTime = Carbon::parse($now->format('Y-m-d') . ' ' . $schedule['scheduled_out'], 'Asia/Jakarta');
                if ($outTime->lt($inTime)) {
                    $outTime->addDay();
                }
                $isWorkingNow = $now->between($inTime, $outTime);
            }

            if (!$isWorkingNow) {
                continue;
            }

            try {
                $res = $this->pushService->sendFollowUpNotification($type, $pengajuan, $approver, $tahapAktif);
                if ($res['success'] ?? false) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                Log::error("[FollowUpNotif] Gagal mengirim pengingat {$type} ID {$pengajuan->id}: " . $e->getMessage());
            }
        }

        if ($sent > 0) {
            $pengajuan->update(['last_notified_at' => $now]);
        }

        return $sent;
    }
}
