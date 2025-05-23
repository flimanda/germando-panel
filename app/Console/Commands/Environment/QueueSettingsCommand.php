<?php

namespace App\Console\Commands\Environment;

use App\Traits\Commands\RequestRedisSettingsTrait;
use App\Traits\EnvironmentWriterTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;

class QueueSettingsCommand extends Command
{
    use EnvironmentWriterTrait;
    use RequestRedisSettingsTrait;

    public const QUEUE_DRIVERS = [
        'database' => 'Database (default)',
        'redis' => 'Redis',
        'sync' => 'Synchronous',
    ];

    protected $description = 'Konfiguriere Warteschlangeneinstellungen für das Panel.';

    protected $signature = 'p:environment:queue
                            {--driver= : Der Warteschlangentreiber, den Sie verwenden möchten.}
                            {--redis-host= : Redis-Host, den Sie verwenden möchten.}
                            {--redis-user= : Benutzer, der zum Verbinden mit Redis verwendet wird.}
                            {--redis-pass= : Passwort, das zum Verbinden mit Redis verwendet wird.}
                            {--redis-port= : Port, über den Sie mit Redis verbinden möchten.}';

    /**
     * QueueSettingsCommand constructor.
     */
    public function __construct(private Kernel $console)
    {
        parent::__construct();
    }

    /**
     * Handle command execution.
     */
    public function handle(): int
    {
        $selected = config('queue.default', 'database');
        $this->variables['QUEUE_CONNECTION'] = $this->option('driver') ?? $this->choice(
            'Warteschlangentreiber',
            self::QUEUE_DRIVERS,
            array_key_exists($selected, self::QUEUE_DRIVERS) ? $selected : null
        );

        if ($this->variables['QUEUE_CONNECTION'] === 'redis') {
            $this->requestRedisSettings();

            $this->call('p:environment:queue-service', [
                '--overwrite' => true,
            ]);
        }

        $this->writeToEnvironment($this->variables);

        $this->info($this->console->output());

        return 0;
    }
}
