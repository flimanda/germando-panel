<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MailTested extends Notification
{
    public function __construct(private User $user) {}

    /**
     * @return string[]
     */
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject('Panel Test Nachricht')
            ->greeting('Hallo ' . $this->user->name . '!')
            ->line('Dies ist eine Testnachricht des Panel-Mail-Systems. Alles ist in Ordnung!');
    }
}
