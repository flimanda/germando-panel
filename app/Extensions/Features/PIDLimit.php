<?php

namespace App\Extensions\Features;

use Filament\Actions\Action;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class PIDLimit extends FeatureProvider
{
    public function __construct(protected Application $app)
    {
        parent::__construct($app);
    }

    /** @return array<string> */
    public function getListeners(): array
    {
        return [
            'pthread_create fehlgeschlagen',
            'Threaderstellung fehlgeschlagen',
            'Thread konnte nicht erstellt werden',
            'Native Thread konnte nicht erstellt werden',
            'Neuer Native Thread konnte nicht erstellt werden',
            'Exception im Thread "craft async scheduler management thread"',
        ];
    }

    public function getId(): string
    {
        return 'pid_limit';
    }

    public function getAction(): Action
    {
        return Action::make($this->getId())
            ->requiresConfirmation()
            ->icon('tabler-alert-triangle')
            ->modalHeading(fn () => auth()->user()->isAdmin() ? 'Speicher oder Prozesslimit erreicht...' : 'Möglicherweise ist das Ressourcenlimit erreicht...')
            ->modalDescription(new HtmlString(Blade::render(
                auth()->user()->isAdmin() ? <<<'HTML'
                    <p>
                        Dieser Server hat das maximale Prozess- oder Speicherlimit erreicht.
                    </p>
                    <p class="mt-4">
                        Erhöhen von <code>container_pid_limit</code> in der
                        Konfiguration, <code>config.yml</code>, könnte dieses Problem lösen.
                    </p>
                    <p class="mt-4">
                        <b>Hinweis: Wings muss neugestartet werden, damit die Änderungen in der Konfigurationsdatei wirksam werden</b>
                    </p>
                HTML
                :
                <<<'HTML'
                    <p>
                        Dieser Server versucht, mehr Ressourcen zu verwenden als zugewiesen. Bitte kontaktieren Sie den Administrator
                        und geben Sie ihm den folgenden Fehler an.
                    </p>
                    <p class="mt-4">
                        <code>
                            pthread_create fehlgeschlagen, Möglicherweise ausgelastet oder Prozess/Ressourcenlimit erreicht
                        </code>
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
