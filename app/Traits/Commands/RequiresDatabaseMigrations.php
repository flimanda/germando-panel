<?php

namespace App\Traits\Commands;

use App\Traits\CheckMigrationsTrait;
use Illuminate\Console\Command;

/**
 * @mixin Command
 */
trait RequiresDatabaseMigrations
{
    use CheckMigrationsTrait;

    /**
     * Throw a massive error into the console to hopefully catch the users attention and get
     * them to properly run the migrations rather than ignoring  other previous
     * errors...
     */
    protected function showMigrationWarning(): void
    {
        $this->getOutput()->writeln('<options=bold>
| @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ |
|                                                                              |
|          Ihre Datenbank ist nicht ordnungsgemäß migriert worden!             |
|                                                                              |
| @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ |</>

Sie müssen den folgenden Befehl ausführen, um Ihre Datenbank zu migrieren:

  <fg=green;options=bold>php artisan migrate --step --force</>

Sie können die Panel-Funktionen nicht erwartungsgemäß verwenden, es sei denn, Sie korrigieren den Fehler, indem Sie den obigen Befehl ausführen.
');

        $this->getOutput()->error('Sie müssen den obigen Fehler beheben, bevor Sie fortfahren können.');
    }
}
