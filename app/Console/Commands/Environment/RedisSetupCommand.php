<?php

namespace App\Console\Commands\Environment;

use App\Traits\Commands\RequestRedisSettingsTrait;
use App\Traits\EnvironmentWriterTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;

class RedisSetupCommand extends Command
{
    use EnvironmentWriterTrait;
    use RequestRedisSettingsTrait;

    protected $description = 'Konfiguriere das Panel, um Redis als Cache, Warteschlange und Sitzungsdriver zu verwenden.';

    protected $signature = 'p:redis:setup
                            {--redis-host= : Redis-Host, den Sie verwenden möchten.}
                            {--redis-user= : Benutzer, der zum Verbinden mit Redis verwendet wird.}
                            {--redis-pass= : Passwort, das zum Verbinden mit Redis verwendet wird.}
                            {--redis-port= : Port, über den Sie mit Redis verbinden möchten.}';

    /**
     * RedisSetupCommand constructor.
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
        $this->variables['CACHE_STORE'] = 'redis';
        $this->variables['QUEUE_CONNECTION'] = 'redis';
        $this->variables['SESSION_DRIVER'] = 'redis';

        $this->requestRedisSettings();

        $this->call('p:environment:queue-service', [
            '--overwrite' => true,
        ]);

        $this->writeToEnvironment($this->variables);

        $this->info($this->console->output());

        return 0;
    }
}
