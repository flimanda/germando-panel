<?php

namespace App\Extensions\Features;

use App\Models\Server;
use App\Repositories\Daemon\DaemonFileRepository;
use App\Repositories\Daemon\DaemonPowerRepository;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class MinecraftEula extends FeatureProvider
{
    public function __construct(protected Application $app)
    {
        parent::__construct($app);
    }

    /** @return array<string> */
    public function getListeners(): array
    {
        return [
            'you need to agree to the eula in order to run the server',
        ];
    }

    public function getId(): string
    {
        return 'eula';
    }

    public function getAction(): Action
    {
        return Action::make($this->getId())
            ->requiresConfirmation()
            ->modalHeading('Minecraft EULA')
            ->modalDescription(new HtmlString(Blade::render('Durch das Drücken von "I Accept" unten bestätigen Sie Ihre Zustimmung zu den <x-filament::link href="https://minecraft.net/eula" target="_blank">Minecraft EULA </x-filament::link>.')))
            ->modalSubmitActionLabel('I Accept')
            ->action(function (DaemonFileRepository $fileRepository, DaemonPowerRepository $powerRepository) {
                try {
                    /** @var Server $server */
                    $server = Filament::getTenant();

                    $fileRepository->setServer($server)->putContent('eula.txt', 'eula=true');

                    $powerRepository->setServer($server)->send('restart');

                    Notification::make()
                        ->title('Minecraft EULA akzeptiert')
                        ->body('Server wird jetzt neustarten.')
                        ->success()
                        ->send();
                } catch (Exception $exception) {
                    Notification::make()
                        ->title('Konnte Minecraft EULA nicht akzeptieren')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function register(Application $app): self
    {
        return new self($app);
    }
}
