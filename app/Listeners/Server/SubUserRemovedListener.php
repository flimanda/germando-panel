<?php

namespace App\Listeners\Server;

use App\Events\Server\SubUserRemoved;
use App\Notifications\RemovedFromServer;
use Filament\Notifications\Notification;

class SubUserRemovedListener
{
    public function handle(SubUserRemoved $event): void
    {
        Notification::make()
            ->title('Von Server entfernt')
            ->body('Sie wurden als Subbenutzer von ' . $event->server->name . ' entfernt.')
            ->sendToDatabase($event->user);

        $event->user->notify(new RemovedFromServer($event->server));
    }
}
