<?php

namespace App\Extensions\OAuth\Providers;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use SocialiteProviders\Discord\Provider;
use Webbingbrasil\FilamentCopyActions\Forms\Actions\CopyAction;

final class DiscordProvider extends OAuthProvider
{
    public function __construct(protected Application $app)
    {
        parent::__construct($app);
    }

    public function getId(): string
    {
        return 'discord';
    }

    public function getProviderClass(): string
    {
        return Provider::class;
    }

    public function getSetupSteps(): array
    {
        return array_merge([
            Step::make('Neue Discord OAuth-App registrieren')
                ->schema([
                    Placeholder::make('')
                        ->content(new HtmlString(Blade::render('<p>Besuchen Sie den <x-filament::link href="https://discord.com/developers/applications" target="_blank">Discord Developer Portal</x-filament::link> und klicken Sie auf <b>New Application</b>. Geben Sie einen <b>Namen</b> (z.B. Ihr Panel-Name) ein und klicken Sie auf <b>Create</b>.</p><p>Kopieren Sie die <b>Client ID</b> und die <b>Client Secret</b> aus dem OAuth2-Tab, Sie werden sie im letzten Schritt benötigen.</p>'))),
                    Placeholder::make('')
                        ->content(new HtmlString('<p>Unter <b>Redirects</b> fügen Sie die folgende URL hinzu.</p>')),
                    TextInput::make('_noenv_callback')
                        ->label('Redirect URL')
                        ->dehydrated()
                        ->disabled()
                        ->hintAction(fn (string $state) => request()->isSecure() ? CopyAction::make()->copyable($state) : null)
                        ->formatStateUsing(fn () => url('/auth/oauth/callback/discord')),
                ]),
        ], parent::getSetupSteps());
    }

    public function getIcon(): string
    {
        return 'tabler-brand-discord-f';
    }

    public function getHexColor(): string
    {
        return '#5865F2';
    }

    public static function register(Application $app): self
    {
        return new self($app);
    }
}
