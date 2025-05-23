<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InfoCommand extends Command
{
    protected $description = 'Zeigt die Anwendungskonfiguration, die Datenbankkonfiguration, die E-Mail-Konfiguration und die Backup-Konfiguration zusammen mit der Panel-Version an.';

    protected $signature = 'p:info';

    public function handle(): void
    {
        $this->call('about');
    }
}
