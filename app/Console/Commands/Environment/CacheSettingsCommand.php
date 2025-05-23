<?php

namespace App\Console\Commands\Environment;

use App\Traits\Commands\RequestRedisSettingsTrait;
use App\Traits\EnvironmentWriterTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;

class CacheSettingsCommand extends Command
{
    use EnvironmentWriterTrait;
    use RequestRedisSettingsTrait;

    public const CACHE_DRIVERS = [
        'file' => 'Filesystem (default)',
        'database' => 'Database',
        'redis' => 'Redis',
    ];

    protected $description = 'Konfiguriere Cache-Einstellungen für das Panel.';

    protected $signature = 'p:environment:cache
                            {--driver= : Der Cache-Treiber, den Sie verwenden möchten.}
                            {--redis-host= : Redis-Host, den Sie verwenden möchten.}
                            {--redis-user= : Benutzer, der zum Verbinden mit Redis verwendet wird.}
                            {--redis-pass= : Passwort, das zum Verbinden mit Redis verwendet wird.}
                            {--redis-port= : Port, über den Sie mit Redis verbinden möchten.}';

    /**
     * CacheSettingsCommand constructor.
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
        $selected = config('cache.default', 'file');
        $this->variables['CACHE_STORE'] = $this->option('driver') ?? $this->choice(
            'Cache-Treiber',
            self::CACHE_DRIVERS,
            array_key_exists($selected, self::CACHE_DRIVERS) ? $selected : null
        );

        if ($this->variables['CACHE_STORE'] === 'redis') {
            $this->requestRedisSettings();

            if (config('queue.default') !== 'sync') {
                $this->call('p:environment:queue-service', [
                    '--overwrite' => true,
                ]);
            }
        }

        $this->writeToEnvironment($this->variables);

        $this->info($this->console->output());

        return 0;
    }
}
