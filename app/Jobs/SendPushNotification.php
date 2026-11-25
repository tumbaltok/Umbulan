<?php

namespace App\Jobs;

use App\Models\User\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public string $type;
    public array $payload;

    public function __construct(string $type, array $payload)
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    public static function sendNewSubmission(string $module, $pengajuan, User $approver, int $tahap = 1): void
    {
        self::dispatch('new_submission', [
            'module'    => $module,
            'pengajuan' => $pengajuan,
            'approver'  => $approver,
            'tahap'     => $tahap,
        ]);
    }

    public static function sendFollowUp(string $module, $pengajuan, User $approver, int $tahap = 1): void
    {
        self::dispatch('follow_up', [
            'module'    => $module,
            'pengajuan' => $pengajuan,
            'approver'  => $approver,
            'tahap'     => $tahap,
        ]);
    }

    public static function sendStatusUpdate(string $module, $pengajuan, string $status, ?string $catatan = null): void
    {
        self::dispatch('status_update', [
            'module'    => $module,
            'pengajuan' => $pengajuan,
            'status'    => $status,
            'catatan'   => $catatan,
        ]);
    }

    public function handle(PushNotificationService $pushService): void
    {
        try {
            if ($this->type === 'new_submission') {
                $pushService->sendNewSubmissionNotification(
                    $this->payload['module'],
                    $this->payload['pengajuan'],
                    $this->payload['approver'],
                    $this->payload['tahap'] ?? 1
                );
            } elseif ($this->type === 'follow_up') {
                $pushService->sendFollowUpNotification(
                    $this->payload['module'],
                    $this->payload['pengajuan'],
                    $this->payload['approver'],
                    $this->payload['tahap'] ?? 1
                );
            } elseif ($this->type === 'status_update') {
                $pushService->sendApprovalStatusNotification(
                    $this->payload['module'],
                    $this->payload['pengajuan'],
                    $this->payload['status'],
                    $this->payload['catatan'] ?? null
                );
            }
        } catch (\Throwable $e) {
            Log::error("[SendPushNotification] Gagal memproses job {$this->type}: " . $e->getMessage());
            throw $e;
        }
    }
}
