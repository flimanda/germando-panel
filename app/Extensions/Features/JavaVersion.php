<?php

namespace App\Extensions\Features;

use App\Facades\Activity;
use App\Models\Permission;
use App\Models\Server;
use App\Repositories\Daemon\DaemonPowerRepository;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Application;

class JavaVersion extends FeatureProvider
{
    public function __construct(protected Application $app)
    {
        parent::__construct($app);
    }

    /** @return array<string> */
    public function getListeners(): array
    {
        return [
            'java.lang.UnsupportedClassVersionError',
            'nicht unterstützte major.minor Version',
            'wurde mit einer neueren Version des Java-Runtimes erstellt',
            'minecraft 1.17 erfordert, dass der Server mit java 16 oder höher gestartet wird',
            'minecraft 1.18 erfordert, dass der Server mit java 17 oder höher gestartet wird',
            'minecraft 1.19 erfordert, dass der Server mit java 17 oder höher gestartet wird',
        ];
    }

    public function getId(): string
    {
        return 'java_version';
    }

    public function getAction(): Action
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        return Action::make($this->getId())
            ->requiresConfirmation()
            ->modalHeading('Nicht unterstützte Java-Version')
            ->modalDescription('Dieser Server ist derzeit mit einer nicht unterstützten Version von Java gestartet und kann nicht gestartet werden.')
            ->modalSubmitActionLabel('Docker-Image aktualisieren')
            ->disabledForm(fn () => !auth()->user()->can(Permission::ACTION_STARTUP_DOCKER_IMAGE, $server))
            ->form([
                Placeholder::make('java')
                    ->label('Bitte wählen Sie eine unterstützte Version aus der Liste unten, um den Server zu starten.'),
                Select::make('image')
                    ->label('Docker-Image')
                    ->disabled(fn () => !in_array($server->image, $server->egg->docker_images))
                    ->options(fn () => collect($server->egg->docker_images)->mapWithKeys(fn ($key, $value) => [$key => $value]))
                    ->selectablePlaceholder(false)
                    ->default(fn () => $server->image)
                    ->notIn(fn () => $server->image)
                    ->required()
                    ->preload()
                    ->native(false),
            ])
            ->action(function (array $data, DaemonPowerRepository $powerRepository) use ($server) {
                try {
                    $new = $data['image'];
                    $original = $server->image;
                    $server->forceFill(['image' => $new])->saveOrFail();

                    if ($original !== $server->image) {
                        Activity::event('server:startup.image')
                            ->property(['old' => $original, 'new' => $new])
                            ->log();
                    }

                    $powerRepository->setServer($server)->send('restart');

                    Notification::make()
                        ->title('Docker-Image aktualisiert')
                        ->body('Server wird jetzt neustarten.')
                        ->success()
                        ->send();
                } catch (Exception $exception) {
                    Notification::make()
                        ->title('Konnte Docker-Image nicht aktualisieren')
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
