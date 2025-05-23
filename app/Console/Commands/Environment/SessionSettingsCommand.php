<?php

namespace App\Console\Commands\Environment;

use App\Traits\Commands\RequestRedisSettingsTrait;
use App\Traits\EnvironmentWriterTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;

class SessionSettingsCommand extends Command
{
    use EnvironmentWriterTrait;
    use RequestRedisSettingsTrait;

    public const SESSION_DRIVERS = [
        'file' => 'Filesystem (default)',
        'redis' => 'Redis',
        'database' => 'Database',
        'cookie' => 'Cookie',
    ];

    protected $description = 'Konfiguriere Sitzungseinstellungen für das Panel.';

    protected $signature = 'p:environment:session
                            {--driver= : Der Sitzungsdriver, den Sie verwenden möchten.}
                            {--redis-host= : Redis-Host, den Sie verwenden möchten.}
                            {--redis-user= : Benutzer, der zum Verbinden mit Redis verwendet wird.}
                            {--redis-pass= : Passwort, das zum Verbinden mit Redis verwendet wird.}
                            {--redis-port= : Port, über den Sie mit Redis verbinden möchten.}';

    /**
     * SessionSettingsCommand constructor.
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
        $selected = config('session.driver', 'file');
        $this->variables['SESSION_DRIVER'] = $this->option('driver') ?? $this->choice(
            'Sitzungsdriver',
            self::SESSION_DRIVERS,
            array_key_exists($selected, self::SESSION_DRIVERS) ? $selected : null
        );

        if ($this->variables['SESSION_DRIVER'] === 'redis') {
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
