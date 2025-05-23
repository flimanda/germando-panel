<?php

namespace App\Console\Commands\Environment;

use Illuminate\Console\Command;

class AppSettingsCommand extends Command
{
    protected $description = 'Konfiguriere grundlegende Umgebungseinstellungen für das Panel.';

    protected $signature = 'p:environment:setup';

    public function handle(): void
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            $this->comment('Kopiere die Beispiel- .env-Datei');
            copy($path . '.example', $path);
        }

        if (!config('app.key')) {
            $this->comment('Generiere App-Schlüssel');
            $this->call('key:generate');
        }

        $this->comment('Erstelle Speicherverknüpfung');
        $this->call('storage:link');

        $this->comment('Caching Komponenten & Icons');
        $this->call('filament:optimize');
    }
}
