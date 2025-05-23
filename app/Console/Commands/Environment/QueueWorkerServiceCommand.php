<?php

namespace App\Console\Commands\Environment;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class QueueWorkerServiceCommand extends Command
{
    protected $description = 'Erstelle den Service für den Warteschlangenworker.';

    protected $signature = 'p:environment:queue-service
        {--service-name= : Name des Warteschlangenworker-Services.}
        {--user= : Der Benutzer, unter dem PHP ausgeführt wird.}
        {--group= : Die Gruppe, unter der PHP ausgeführt wird.}
        {--overwrite : Erzwinge die Überschreitung, wenn die Service-Datei bereits existiert.}';

    public function handle(): void
    {
        $serviceName = $this->option('service-name') ?? $this->ask('Warteschlangenworker-Service-Name', 'pelican-queue');
        $path = '/etc/systemd/system/' . $serviceName  . '.service';

        $fileExists = @file_exists($path);
        if ($fileExists && !$this->option('overwrite') && !$this->confirm('Die Service-Datei existiert bereits. Möchten Sie sie überschreiben?')) {
            $this->line('Erstellung der Warteschlangenworker-Service-Datei abgebrochen, weil die Service-Datei bereits existiert.');

            return;
        }

        $user = $this->option('user') ?? $this->ask('Webserver-Benutzer', 'www-data');
        $group = $this->option('group') ?? $this->ask('Webserver-Gruppe', 'www-data');

        $redisUsed = config('queue.default') === 'redis' || config('session.driver') === 'redis' || config('cache.default') === 'redis';
        $afterRedis = $redisUsed ? '
After=redis-server.service' : '';

        $basePath = base_path();

        $success = File::put($path, "# Pelican Queue File
# ----------------------------------

[Unit]
Description=Pelican Queue Service$afterRedis

[Service]
User=$user
Group=$group
Restart=always
ExecStart=/usr/bin/php $basePath/artisan queue:work --tries=3
StartLimitInterval=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
        ");

        if (!$success) {
            $this->error('Error creating service file');

            return;
        }

        if ($fileExists) {
            $result = Process::run("systemctl restart $serviceName.service");
            if ($result->failed()) {
                $this->error('Error restarting service: ' . $result->errorOutput());

                return;
            }

            $this->line('Queue worker service file updated successfully.');
        } else {
            $result = Process::run("systemctl enable --now $serviceName.service");
            if ($result->failed()) {
                $this->error('Error enabling service: ' . $result->errorOutput());

                return;
            }

            $this->line('Queue worker service file created successfully.');
        }
    }
}
