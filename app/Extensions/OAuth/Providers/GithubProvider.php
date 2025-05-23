<?php

namespace App\Extensions\OAuth\Providers;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Webbingbrasil\FilamentCopyActions\Forms\Actions\CopyAction;

final class GithubProvider extends OAuthProvider
{
    public function __construct(protected Application $app)
    {
        parent::__construct($app);
    }

    public function getId(): string
    {
        return 'github';
    }

    public function getSetupSteps(): array
    {
        return array_merge([
            Step::make('Neue Github OAuth-App registrieren')
                ->schema([
                    Placeholder::make('')
                        ->content(new HtmlString(Blade::render('<p>Besuchen Sie den <x-filament::link href="https://github.com/settings/developers" target="_blank">Github Developer Dashboard</x-filament::link>, gehen Sie zu <b>OAuth Apps</b> und klicken Sie auf <b>New OAuth App</b>.</p><p>Geben Sie einen <b>Anwendungsnamen</b> (z.B. Ihr Panel-Name) ein, setzen Sie <b>Homepage URL</b> auf Ihre Panel-URL und geben Sie die folgende URL als <b>Authorization callback URL</b> ein.</p>'))),
                    TextInput::make('_noenv_callback')
                        ->label('Authorization callback URL')
                        ->dehydrated()
                        ->disabled()
                        ->hintAction(fn (string $state) => request()->isSecure() ? CopyAction::make()->copyable($state) : null)
                        ->default(fn () => url('/auth/oauth/callback/github')),
                    Placeholder::make('')
                        ->content(new HtmlString('<p>Wenn Sie alle Felder ausgefüllt haben, klicken Sie auf <b>Register application</b>.</p>')),
                ]),
            Step::make('Client Secret erstellen')
                ->schema([
                    Placeholder::make('')
                        ->content(new HtmlString('<p>Wenn Sie Ihre App registriert haben, erstellen Sie einen neuen <b>Client Secret</b>.</p><p>Sie benötigen auch die <b>Client ID</b>.</p>')),
                ]),
        ], parent::getSetupSteps());
    }

    public function getIcon(): string
    {
        return 'tabler-brand-github-f';
    }

    public function getHexColor(): string
    {
        return '#4078c0';
    }

    public static function register(Application $app): self
    {
        return new self($app);
    }
}
