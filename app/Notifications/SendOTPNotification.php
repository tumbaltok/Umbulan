<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOTPNotification extends Notification
{
    use Queueable;

    protected int $otp;

    public function __construct(int $otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Kode OTP Anda')
            ->greeting('Halo!')
            ->line('Berikut adalah kode OTP untuk verifikasi akun Anda:')
            ->line('**' . $this->otp . '**')
            ->line('Kode ini hanya berlaku selama 5 menit.')
            ->line('Jangan bagikan kode ini kepada siapa pun.');
    }
}
