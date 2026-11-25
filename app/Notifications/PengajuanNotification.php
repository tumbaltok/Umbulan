<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PengajuanNotification extends Notification
{
    use Queueable;

    public string $title;
    public string $body;
    public array $meta;

    public function __construct(string $title, string $body, array $meta = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->meta = $meta;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'meta' => $this->meta,
        ];
    }
}
