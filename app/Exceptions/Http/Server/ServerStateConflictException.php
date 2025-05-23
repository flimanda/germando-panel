<?php

namespace App\Exceptions\Http\Server;

use App\Enums\ServerState;
use App\Models\Server;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ServerStateConflictException extends ConflictHttpException
{
    /**
     * Exception thrown when the server is in an unsupported state for API access or
     * certain operations within the codebase.
     */
    public function __construct(Server $server, ?\Throwable $previous = null)
    {
        $message = 'Dieser Server ist derzeit in einem nicht unterstützten Zustand, bitte versuchen Sie es später erneut.';
        if ($server->isSuspended()) {
            $message = 'Dieser Server ist derzeit gesperrt und die angefragte Funktionalität ist nicht verfügbar.';
        } elseif ($server->node->isUnderMaintenance()) {
            $message = 'Der Knoten dieses Servers ist derzeit in Wartung und die angefragte Funktionalität ist nicht verfügbar.';
        } elseif (!$server->isInstalled()) {
            $message = 'Dieser Server hat noch nicht seinen Installationsprozess abgeschlossen, bitte versuchen Sie es später erneut.';
        } elseif ($server->status === ServerState::RestoringBackup) {
            $message = 'Dieser Server wird derzeit von einem Backup wiederhergestellt, bitte versuchen Sie es später erneut.';
        } elseif (!is_null($server->transfer)) {
            $message = 'Dieser Server wird derzeit auf ein neues Gerät übertragen, bitte versuchen Sie es später erneut.';
        }

        parent::__construct($message, $previous);
    }
}
