<?php

namespace App\Listeners\Server;

use App\Events\Server\SubUserAdded;
use App\Filament\Server\Pages\Console;
use App\Notifications\AddedToServer;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class SubUserAddedListener
{
    public function handle(SubUserAdded $event): void
    {
        $event->subuser->loadMissing('server');
        $event->subuser->loadMissing('user');

        Notification::make()
            ->title('Zum Server hinzugefügt')
            ->body('Sie wurden als Subbenutzer zu ' . $event->subuser->server->name . ' hinzugefügt.')
            ->actions([
                Action::make('view')
                    ->button()
                    ->label('Server öffnen')
                    ->markAsRead()
                    ->url(fn () => Console::getUrl(panel: 'server', tenant: $event->subuser->server)),
            ])
            ->sendToDatabase($event->subuser->user);

        $event->subuser->user->notify(new AddedToServer($event->subuser->server));
    }
}
