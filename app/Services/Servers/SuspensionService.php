<?php

namespace App\Services\Servers;

use App\Enums\ServerState;
use App\Enums\SuspendAction;
use Filament\Notifications\Notification;
use App\Models\Server;
use App\Repositories\Daemon\DaemonServerRepository;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SuspensionService
{
    /**
     * SuspensionService constructor.
     */
    public function __construct(
        private DaemonServerRepository $daemonServerRepository
    ) {}

    /**
     * Suspends a server on the system.
     *
     * @throws \Throwable
     */
    public function handle(Server $server, SuspendAction $action): void
    {
        $isSuspending = $action === SuspendAction::Suspend;
        // Nothing needs to happen if we're suspending the server, and it is already
        // suspended in the database. Additionally, nothing needs to happen if the server
        // is not suspended, and we try to un-suspend the instance.
        if ($isSuspending === $server->isSuspended()) {
            Notification::make()->danger()->title('Failed!')->body('Server is already suspended!')->send();

            return;
        }

        // Check if the server is currently being transferred.
        if (!is_null($server->transfer)) {
            Notification::make()->danger()->title('Fehlgeschlagen!')->body('Server wird derzeit übertragen.')->send();
            throw new ConflictHttpException('Konnte die Suspendierungsstatusänderung für einen Server, der derzeit übertragen wird, nicht durchführen.');
        }

        // Update the server's suspension status.
        $server->update([
            'status' => $isSuspending ? ServerState::Suspended : null,
        ]);

        // Tell daemon to re-sync the server state.
        $this->daemonServerRepository->setServer($server)->sync();
    }
}
