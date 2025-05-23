<?php

namespace App\Notifications;

use App\Filament\Server\Pages\Console;
use App\Models\User;
use Illuminate\Bus\Queueable;
use App\Models\Server;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ServerInstalled extends Notification implements ShouldQueue
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
            ->greeting('Hallo ' . $notifiable->username . '.')
            ->line('Dein Server wurde installiert und ist nun bereit für dich zu verwenden.')
            ->line('Server Name: ' . $this->server->name)
            ->action('Login und beginnen zu verwenden', Console::getUrl(panel: 'server', tenant: $this->server));
    }
}
