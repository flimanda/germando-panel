<?php

namespace App\Notifications;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?string $token = null) {}

    /** @return string[] */
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->greeting('Hallo ' . $notifiable->name . '!')
            ->line('Du erhältst diese E-Mail, weil ein Konto für dich auf ' . config('app.name') . ' erstellt wurde.')
            ->line('Benutzername: ' . $notifiable->username)
            ->line('E-Mail: ' . $notifiable->email);

        if (!is_null($this->token)) {
            return $message->action('Konto einrichten', Filament::getPanel('app')->getResetPasswordUrl($this->token, $notifiable));
        }

        return $message;
    }
}
