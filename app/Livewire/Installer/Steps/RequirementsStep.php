<?php

namespace App\Livewire\Installer\Steps;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class RequirementsStep
{
    public const MIN_PHP_VERSION = '8.2';

    public static function make(): Step
    {
        $compare = version_compare(phpversion(), self::MIN_PHP_VERSION);
        $correctPhpVersion = $compare >= 0;

        $fields = [
            Section::make('PHP Version')
                ->description(self::MIN_PHP_VERSION . ' oder neuer')
                ->icon($correctPhpVersion ? 'tabler-check' : 'tabler-x')
                ->iconColor($correctPhpVersion ? 'success' : 'danger')
                ->schema([
                    Placeholder::make('')
                        ->content('Ihre PHP-Version ist ' . PHP_VERSION . '.'),
                ]),
        ];

        $phpExtensions = [
            'BCMath' => extension_loaded('bcmath'),
            'cURL' => extension_loaded('curl'),
            'GD' => extension_loaded('gd'),
            'intl' => extension_loaded('intl'),
            'mbstring' => extension_loaded('mbstring'),
            'MySQL' => extension_loaded('pdo_mysql'),
            'SQLite3' => extension_loaded('pdo_sqlite'),
            'XML' => extension_loaded('xml'),
            'Zip' => extension_loaded('zip'),
        ];
        $allExtensionsInstalled = !in_array(false, $phpExtensions);

        $fields[] = Section::make('PHP Extensions')
            ->description(implode(', ', array_keys($phpExtensions)))
            ->icon($allExtensionsInstalled ? 'tabler-check' : 'tabler-x')
            ->iconColor($allExtensionsInstalled ? 'success' : 'danger')
            ->schema([
                Placeholder::make('')
                    ->content('Alle benötigten PHP-Erweiterungen sind installiert.')
                    ->visible($allExtensionsInstalled),
                Placeholder::make('')
                    ->content('Die folgenden PHP-Erweiterungen sind fehlend: ' . implode(', ', array_keys($phpExtensions, false)))
                    ->visible(!$allExtensionsInstalled),
            ]);

        $folderPermissions = [
            'Storage' => substr(sprintf('%o', fileperms(base_path('storage/'))), -4) >= 755,
            'Cache' => substr(sprintf('%o', fileperms(base_path('bootstrap/cache/'))), -4) >= 755,
        ];
        $correctFolderPermissions = !in_array(false, $folderPermissions);

        $fields[] = Section::make('Ordnerberechtigungen')
            ->description(implode(', ', array_keys($folderPermissions)))
            ->icon($correctFolderPermissions ? 'tabler-check' : 'tabler-x')
            ->iconColor($correctFolderPermissions ? 'success' : 'danger')
            ->schema([
                Placeholder::make('')
                    ->content('Alle Ordner haben die korrekten Berechtigungen.')
                    ->visible($correctFolderPermissions),
                Placeholder::make('')
                    ->content('Die folgenden Ordner haben falsche Berechtigungen: ' . implode(', ', array_keys($folderPermissions, false)))
                    ->visible(!$correctFolderPermissions),
            ]);

        return Step::make('requirements')
            ->label('Serveranforderungen')
            ->schema($fields)
            ->afterValidation(function () use ($correctPhpVersion, $allExtensionsInstalled, $correctFolderPermissions) {
                if (!$correctPhpVersion || !$allExtensionsInstalled || !$correctFolderPermissions) {
                    Notification::make()
                        ->title('Es fehlen einige Anforderungen!')
                        ->danger()
                        ->send();

                    throw new Halt('Es fehlen einige Anforderungen!');
                }
            });
    }
}
