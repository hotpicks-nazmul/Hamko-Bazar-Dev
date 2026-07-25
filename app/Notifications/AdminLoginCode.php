<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdminLoginCode extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $code
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔐 Admin Login Verification Code')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You are receiving this email because someone is trying to log into your admin account.')
            ->line('Your verification code is:')
            ->line("**{$this->code}**")
            ->line('This code expires in 10 minutes.')
            ->line('If you did not attempt to log in, please change your password immediately.')
            ->salutation('— ' . config('app.name') . ' Security Team');
    }
}
