<?php

namespace App\Console\Commands\Node;

use App\Models\Node;
use Illuminate\Console\Command;

class MakeNodeCommand extends Command
{
    protected $signature = 'p:node:make
                            {--name= : Ein Name, um den Netzknoten zu identifizieren.}
                            {--description= : Eine Beschreibung, um den Netzknoten zu identifizieren.}
                            {--locationId= : Eine gültige locationId.}
                            {--fqdn= : Der Domainname (z.B. node.example.com) zum Verbinden mit dem Daemon. Eine IP-Adresse kann nur verwendet werden, wenn Sie SSL für diesen Netzknoten nicht verwenden.}
                            {--public= : Soll der Netzknoten öffentlich oder privat sein? (öffentlich=1 / privat=0).}
                            {--scheme= : Welche Verschlüsselung soll verwendet werden? (SSL aktivieren=https / SSL deaktivieren=http).}
                            {--proxy= : Steht der Daemon hinter einem Proxy? (Ja=1 / Nein=0).}
                            {--maintenance= : Soll der Wartungsmodus aktiviert sein? (Wartungsmodus aktivieren=1 / Wartungsmodus deaktivieren=0).}
                            {--maxMemory= : Setzen Sie die maximale Speichermenge.}
                            {--overallocateMemory= : Setzen Sie die Speichermenge, die Sie über die maximale Speichermenge hinaus zuweisen möchten (% oder -1, um die maximale Speichermenge zu überzuzuweisen).}
                            {--maxDisk= : Setzen Sie die maximale Festplattenmenge.}
                            {--overallocateDisk= : Setzen Sie die Festplattenmenge, die Sie über die maximale Festplattenmenge hinaus zuweisen möchten (% oder -1, um die maximale Festplattenmenge zu überzuzuweisen).}
                            {--maxCpu= : Setzen Sie die maximale CPU-Menge.}
                            {--overallocateCpu= : Setzen Sie die CPU-Menge, die Sie über die maximale CPU-Menge hinaus zuweisen möchten (% oder -1, um die maximale CPU-Menge zu überzuzuweisen).}
                            {--uploadSize= : Setzen Sie die maximale Upload-Dateigröße.}
                            {--daemonListeningPort= : Setzen Sie den Daemon-Listening-Port.}
                            {--daemonSFTPPort= : Setzen Sie den Daemon-SFTP-Listening-Port.}
                            {--daemonSFTPAlias= : Setzen Sie den Daemon-SFTP-Alias.}
                            {--daemonBase= : Setzen Sie den Basisordner.}';

    protected $description = 'Erstellt einen neuen Netzknoten auf dem System über die CLI.';

    /**
     * Handle the command execution process.
     *
     * @throws \App\Exceptions\Model\DataValidationException
     */
    public function handle(): void
    {
        $data['name'] = $this->option('name') ?? $this->ask(trans('commands.make_node.name'));
        $data['description'] = $this->option('description') ?? $this->ask(trans('commands.make_node.description'));
        $data['scheme'] = $this->option('scheme') ?? $this->anticipate(
            trans('commands.make_node.scheme'),
            ['https', 'http'],
            'https'
        );

        $data['fqdn'] = $this->option('fqdn') ?? $this->ask(trans('commands.make_node.fqdn'));
        $data['public'] = $this->option('public') ?? $this->confirm(trans('commands.make_node.public'), true);
        $data['behind_proxy'] = $this->option('proxy') ?? $this->confirm(trans('commands.make_node.behind_proxy'));
        $data['maintenance_mode'] = $this->option('maintenance') ?? $this->confirm(trans('commands.make_node.maintenance_mode'));
        $data['memory'] = $this->option('maxMemory') ?? $this->ask(trans('commands.make_node.memory'), '0');
        $data['memory_overallocate'] = $this->option('overallocateMemory') ?? $this->ask(trans('commands.make_node.memory_overallocate'), '-1');
        $data['disk'] = $this->option('maxDisk') ?? $this->ask(trans('commands.make_node.disk'), '0');
        $data['disk_overallocate'] = $this->option('overallocateDisk') ?? $this->ask(trans('commands.make_node.disk_overallocate'), '-1');
        $data['cpu'] = $this->option('maxCpu') ?? $this->ask(trans('commands.make_node.cpu'), '0');
        $data['cpu_overallocate'] = $this->option('overallocateCpu') ?? $this->ask(trans('commands.make_node.cpu_overallocate'), '-1');
        $data['upload_size'] = $this->option('uploadSize') ?? $this->ask(trans('commands.make_node.upload_size'), '256');
        $data['daemon_listen'] = $this->option('daemonListeningPort') ?? $this->ask(trans('commands.make_node.daemonListen'), '8080');
        $data['daemon_sftp'] = $this->option('daemonSFTPPort') ?? $this->ask(trans('commands.make_node.daemonSFTP'), '2022');
        $data['daemon_sftp_alias'] = $this->option('daemonSFTPAlias') ?? $this->ask(trans('commands.make_node.daemonSFTPAlias'), '');
        $data['daemon_base'] = $this->option('daemonBase') ?? $this->ask(trans('commands.make_node.daemonBase'), '/var/lib/pelican/volumes');

        $node = Node::create($data);
        $this->line(trans('commands.make_node.success', ['name' => $data['name'], 'id' => $node->id]));
    }
}
