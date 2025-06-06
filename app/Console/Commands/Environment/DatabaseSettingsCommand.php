<?php

namespace App\Console\Commands\Environment;

use App\Traits\EnvironmentWriterTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\DatabaseManager;

class DatabaseSettingsCommand extends Command
{
    use EnvironmentWriterTrait;

    public const DATABASE_DRIVERS = [
        'sqlite' => 'SQLite (recommended)',
        'mariadb' => 'MariaDB',
        'mysql' => 'MySQL',
    ];

    protected $description = 'Konfiguriere Datenbank-Einstellungen für das Panel.';

    protected $signature = 'p:environment:database
                            {--driver= : Der Datenbank-Treiber, den Sie verwenden möchten.}
                            {--database= : Die Datenbank, die Sie verwenden möchten.}
                            {--host= : Die Verbindungsadresse für den MySQL/MariaDB-Server.}
                            {--port= : Der Verbindungsport für den MySQL/MariaDB-Server.}
                            {--username= : Benutzername, der beim Verbinden mit dem MySQL/MariaDB-Server verwendet wird.}
                            {--password= : Passwort, das zum Verbinden mit dem MySQL/MariaDB-Datenbank verwendet wird.}';

    /** @var array<array-key, mixed> */
    protected array $variables = [];

    /**
     * DatabaseSettingsCommand constructor.
     */
    public function __construct(private DatabaseManager $database, private Kernel $console)
    {
        parent::__construct();
    }

    /**
     * Handle command execution.
     */
    public function handle(): int
    {
        $this->error('Changing the database driver will NOT move any database data!');
        $this->error('Please make sure you made a database backup first!');
        $this->error('After changing the driver you will have to manually move the old data to the new database.');
        if (!$this->confirm('Do you want to continue?')) {
            return 1;
        }

        $selected = config('database.default', 'sqlite');
        $this->variables['DB_CONNECTION'] = $this->option('driver') ?? $this->choice(
            'Datenbank-Treiber',
            self::DATABASE_DRIVERS,
            array_key_exists($selected, self::DATABASE_DRIVERS) ? $selected : null
        );

        if ($this->variables['DB_CONNECTION'] === 'mysql') {
            $this->output->note(trans('commands.database_settings.DB_HOST_note'));
            $this->variables['DB_HOST'] = $this->option('host') ?? $this->ask(
                'Datenbank-Host',
                config('database.connections.mysql.host', '127.0.0.1')
            );

            $this->variables['DB_PORT'] = $this->option('port') ?? $this->ask(
                'Datenbank-Port',
                config('database.connections.mysql.port', 3306)
            );

            $this->variables['DB_DATABASE'] = $this->option('database') ?? $this->ask(
                'Datenbank-Name',
                config('database.connections.mysql.database', 'panel')
            );

            $this->output->note(trans('commands.database_settings.DB_USERNAME_note'));
            $this->variables['DB_USERNAME'] = $this->option('username') ?? $this->ask(
                'Datenbank-Benutzername',
                config('database.connections.mysql.username', 'pelican')
            );

            $askForMySQLPassword = true;
            if (!empty(config('database.connections.mysql.password')) && $this->input->isInteractive()) {
                $this->variables['DB_PASSWORD'] = config('database.connections.mysql.password');
                $askForMySQLPassword = $this->confirm(trans('commands.database_settings.DB_PASSWORD_note'));
            }

            if ($askForMySQLPassword) {
                $this->variables['DB_PASSWORD'] = $this->option('password') ?? $this->secret('Datenbank-Passwort');
            }

            try {
                // Test connection
                config()->set('database.connections._panel_command_test', [
                    'driver' => 'mysql',
                    'host' => $this->variables['DB_HOST'],
                    'port' => $this->variables['DB_PORT'],
                    'database' => $this->variables['DB_DATABASE'],
                    'username' => $this->variables['DB_USERNAME'],
                    'password' => $this->variables['DB_PASSWORD'],
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'strict' => true,
                ]);

                $this->database->connection('_panel_command_test')->getPdo();
            } catch (\PDOException $exception) {
                $this->output->error(sprintf('Unable to connect to the MySQL server using the provided credentials. The error returned was "%s".', $exception->getMessage()));
                $this->output->error(trans('commands.database_settings.DB_error_2'));

                if ($this->confirm(trans('commands.database_settings.go_back'))) {
                    $this->database->disconnect('_panel_command_test');

                    return $this->handle();
                }

                return 1;
            }
        } elseif ($this->variables['DB_CONNECTION'] === 'mariadb') {
            $this->output->note(trans('commands.database_settings.DB_HOST_note'));
            $this->variables['DB_HOST'] = $this->option('host') ?? $this->ask(
                'Datenbank-Host',
                config('database.connections.mariadb.host', '127.0.0.1')
            );

            $this->variables['DB_PORT'] = $this->option('port') ?? $this->ask(
                'Datenbank-Port',
                config('database.connections.mariadb.port', 3306)
            );

            $this->variables['DB_DATABASE'] = $this->option('database') ?? $this->ask(
                'Datenbank-Name',
                config('database.connections.mariadb.database', 'panel')
            );

            $this->output->note(trans('commands.database_settings.DB_USERNAME_note'));
            $this->variables['DB_USERNAME'] = $this->option('username') ?? $this->ask(
                'Datenbank-Benutzername',
                config('database.connections.mariadb.username', 'pelican')
            );

            $askForMariaDBPassword = true;
            if (!empty(config('database.connections.mariadb.password')) && $this->input->isInteractive()) {
                $this->variables['DB_PASSWORD'] = config('database.connections.mariadb.password');
                $askForMariaDBPassword = $this->confirm(trans('commands.database_settings.DB_PASSWORD_note'));
            }

            if ($askForMariaDBPassword) {
                $this->variables['DB_PASSWORD'] = $this->option('password') ?? $this->secret('Datenbank-Passwort');
            }

            try {
                // Test connection
                config()->set('database.connections._panel_command_test', [
                    'driver' => 'mariadb',
                    'host' => $this->variables['DB_HOST'],
                    'port' => $this->variables['DB_PORT'],
                    'database' => $this->variables['DB_DATABASE'],
                    'username' => $this->variables['DB_USERNAME'],
                    'password' => $this->variables['DB_PASSWORD'],
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'strict' => true,
                ]);

                $this->database->connection('_panel_command_test')->getPdo();
            } catch (\PDOException $exception) {
                $this->output->error(sprintf('Unable to connect to the MariaDB server using the provided credentials. The error returned was "%s".', $exception->getMessage()));
                $this->output->error(trans('commands.database_settings.DB_error_2'));

                if ($this->confirm(trans('commands.database_settings.go_back'))) {
                    $this->database->disconnect('_panel_command_test');

                    return $this->handle();
                }

                return 1;
            }
        } elseif ($this->variables['DB_CONNECTION'] === 'sqlite') {
            $this->variables['DB_DATABASE'] = $this->option('database') ?? $this->ask(
                'Datenbank-Pfad',
                (string) env('DB_DATABASE', 'database.sqlite')
            );
        }

        $this->writeToEnvironment($this->variables);

        $this->info($this->console->output());

        return 0;
    }
}
