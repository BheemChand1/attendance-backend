<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyCompanyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public $verificationToken;
    public $companyName;

    public function __construct($verificationToken, $companyName)
    {
        $this->verificationToken = $verificationToken;
        $this->companyName = $companyName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = url('/api/verify-email?token=' . $this->verificationToken);

        return (new MailMessage)
            ->subject('Verify Your Email - ' . $this->companyName)
            ->greeting('Welcome to Attendance Management System!')
            ->line('Thank you for registering ' . $this->companyName . ' with us.')
            ->line('Please verify your email address by clicking the button below.')
            ->action('Verify Email', $verificationUrl)
            ->line('Or copy this link: ' . $verificationUrl)
            ->line('This link will expire in 24 hours.')
            ->line('If you did not create this account, please ignore this email.')
            ->salutation('Best regards, Attendance Management System');
    }
}
