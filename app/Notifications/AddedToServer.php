<?php

namespace App\Notifications;

use App\Filament\Server\Pages\Console;
use App\Models\Server;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AddedToServer extends Notification implements ShouldQueue
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
            ->greeting('Hallo ' . $notifiable->username . '!')
            ->line('Du hast den Status "Subuser" für den folgenden Server erhalten, was dir bestimmte Kontrolle über den Server ermöglicht.')
            ->line('Server Name: ' . $this->server->name)
            ->action('Server besuchen', Console::getUrl(panel: 'server', tenant: $this->server));
    }
}
