<?php

namespace App\Jobs;

use App\Models\User\User;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    protected string $type;
    protected array $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(string $type, array $payload)
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    /**
     * Helper untuk antrean pengiriman pesan raw teks
     */
    public static function sendRaw(string $number, string $message): void
    {
        self::dispatch('raw', [
            'number'  => $number,
            'message' => $message,
        ]);
    }

    /**
     * Helper untuk antrean notifikasi pengajuan baru
     */
    public static function sendNewSubmission(string $module, $pengajuan, User $approver, int $tahap = 1): void
    {
        self::dispatch('new_submission', [
            'module'    => $module,
            'pengajuan' => $pengajuan,
            'approver'  => $approver,
            'tahap'     => $tahap,
        ]);
    }

    /**
     * Helper untuk antrean notifikasi follow-up / reminder
     */
    public static function sendFollowUp(string $module, $pengajuan, User $approver, int $tahap = 1): void
    {
        self::dispatch('follow_up', [
            'module'    => $module,
            'pengajuan' => $pengajuan,
            'approver'  => $approver,
            'tahap'     => $tahap,
        ]);
    }

    /**
     * Eksekusi job asynchronous pengiriman WhatsApp
     */
    public function handle(WhatsAppService $waService): void
    {
        try {
            if ($this->type === 'raw') {
                $waService->sendMessage($this->payload['number'], $this->payload['message']);
            } elseif ($this->type === 'new_submission') {
                $waService->sendNewSubmissionNotification(
                    $this->payload['module'],
                    $this->payload['pengajuan'],
                    $this->payload['approver'],
                    $this->payload['tahap'] ?? 1
                );
            } elseif ($this->type === 'follow_up') {
                $waService->sendFollowUpNotification(
                    $this->payload['module'],
                    $this->payload['pengajuan'],
                    $this->payload['approver'],
                    $this->payload['tahap'] ?? 1
                );
            }
        } catch (\Throwable $e) {
            Log::error("[SendWhatsAppNotification Job] Gagal mengirim pesan WA: " . $e->getMessage(), [
                'type' => $this->type,
            ]);
            throw $e;
        }
    }
}
