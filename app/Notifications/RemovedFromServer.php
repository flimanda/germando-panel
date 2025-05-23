<?php

namespace App\Notifications;

use App\Models\Server;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RemovedFromServer extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Server $server) {}

    /** @return string[] */
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage())
            ->error()
            ->greeting('Hallo ' . $notifiable->username . '.')
            ->line('Du wurdest als Subuser für den folgenden Server entfernt.')
            ->line('Server Name: ' . $this->server->name)
            ->action('Panel besuchen', url(''));
    }
}
