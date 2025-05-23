<?php

namespace App\Extensions\Features;

use Filament\Actions\Action;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class SteamDiskSpace extends FeatureProvider
{
    public function __construct(protected Application $app)
    {
        parent::__construct($app);
    }

    /** @return array<string> */
    public function getListeners(): array
    {
        return [
            'steamcmd needs 250mb of free disk space to update',
            '0x202 after update job',
        ];
    }

    public function getId(): string
    {
        return 'steam_disk_space';
    }

    public function getAction(): Action
    {
        return Action::make($this->getId())
            ->requiresConfirmation()
            ->modalHeading('Kein Speicherplatz mehr verfügbar...')
            ->modalDescription(new HtmlString(Blade::render(
                auth()->user()->isAdmin() ? <<<'HTML'
                    <p>
                        Dieser Server hat keinen verfügbaren Speicherplatz mehr und kann die Installation oder das Update nicht abschließen.
                    </p>
                    <p class="mt-4">
                        Stellen Sie sicher, dass der Host genug Speicherplatz hat, indem Sie{' '}
                        <code class="rounded py-1 px-2">df -h</code> auf dem Host eingeben, auf dem dieser Server gehostet wird. Löschen Sie Dateien oder erhöhen Sie den verfügbaren Speicherplatz, um das Problem zu lösen.
                    </p>
                HTML
                :
                <<<'HTML'
                    <p>
                        Dieser Server hat keinen verfügbaren Speicherplatz mehr und kann die Installation oder das Update nicht abschließen. Bitte kontaktieren Sie den Administrator(en) und informieren Sie sie über das Speicherplatzproblem.
                    </p>
                HTML
            )))
            ->modalCancelActionLabel('Schließen')
            ->action(fn () => null);
    }

    public static function register(Application $app): self
    {
        return new self($app);
    }
}
