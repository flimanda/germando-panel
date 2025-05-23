<?php

namespace App\Filament\Server\Resources\FileResource\Pages;

use AbdelhamidErrahmouni\FilamentMonacoEditor\MonacoEditor;
use App\Enums\EditorLanguages;
use App\Exceptions\Http\Server\FileSizeTooLargeException;
use App\Exceptions\Repository\FileNotEditableException;
use App\Facades\Activity;
use App\Filament\Server\Resources\FileResource;
use App\Livewire\AlertBanner;
use App\Models\Permission;
use App\Models\Server;
use App\Repositories\Daemon\DaemonFileRepository;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Panel;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Livewire\Attributes\Locked;

/**
 * @property Form $form
 */
class EditFiles extends Page
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    protected static string $resource = FileResource::class;

    protected static string $view = 'filament.server.pages.edit-file';

    protected static ?string $title = '';

    #[Locked]
    public string $path;

    private DaemonFileRepository $fileRepository;

    /** @var array<mixed> */
    public ?array $data = [];

    public function form(Form $form): Form
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        Activity::event('server:file.read')
            ->property('file', $this->path)
            ->log();

        return $form
            ->schema([
                Section::make('Editing: ' . $this->path)
                    ->footerActions([
                        Action::make('save_and_close')
                            ->label('Save & Close')
                            ->authorize(fn () => auth()->user()->can(Permission::ACTION_FILE_UPDATE, $server))
                            ->icon('tabler-device-floppy')
                            ->keyBindings('mod+shift+s')
                            ->action(function () {
                                $this->getDaemonFileRepository()->putContent($this->path, $this->data['editor'] ?? '');

                                Activity::event('server:file.write')
                                    ->property('file', $this->path)
                                    ->log();

                                Notification::make()
                                    ->success()
                                    ->title('File saved')
                                    ->body(fn () => $this->path)
                                    ->send();

                                $this->redirect(ListFiles::getUrl(['path' => dirname($this->path)]));
                            }),
                        Action::make('save')
                            ->label('Speichern')
                            ->authorize(fn () => auth()->user()->can(Permission::ACTION_FILE_UPDATE, $server))
                            ->icon('tabler-device-floppy')
                            ->keyBindings('mod+s')
                            ->action(function () {
                                $this->getDaemonFileRepository()->putContent($this->path, $this->data['editor'] ?? '');

                                Activity::event('server:file.write')
                                    ->property('file', $this->path)
                                    ->log();

                                Notification::make()
                                    ->success()
                                    ->title('Datei gespeichert')
                                    ->body(fn () => $this->path)
                                    ->send();
                            }),
                        Action::make('cancel')
                            ->label('Abbrechen')
                            ->color('danger')
                            ->icon('tabler-x')
                            ->url(fn () => ListFiles::getUrl(['path' => dirname($this->path)])),
                    ])
                    ->footerActionsAlignment(Alignment::End)
                    ->schema([
                        Select::make('lang')
                            ->label('Syntax-Highlighting')
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->options(EditorLanguages::class)
                            ->selectablePlaceholder(false)
                            ->afterStateUpdated(fn ($state) => $this->dispatch('setLanguage', lang: $state))
                            ->default(fn () => EditorLanguages::fromWithAlias(pathinfo($this->path, PATHINFO_EXTENSION))),
                        MonacoEditor::make('editor')
                            ->hiddenLabel()
                            ->showPlaceholder(false)
                            ->default(function () {
                                try {
                                    return $this->getDaemonFileRepository()->getContent($this->path, config('panel.files.max_edit_size'));
                                } catch (FileSizeTooLargeException) {
                                    AlertBanner::make()
                                        ->title('<code>' . basename($this->path) . '</code> ist zu groß!')
                                        ->body('Max ist ' . convert_bytes_to_readable(config('panel.files.max_edit_size')))
                                        ->danger()
                                        ->closable()
                                        ->send();

                                    $this->redirect(ListFiles::getUrl(['path' => dirname($this->path)]));
                                } catch (FileNotFoundException) {
                                    AlertBanner::make()
                                        ->title('<code>' . basename($this->path) . '</code> nicht gefunden!')
                                        ->danger()
                                        ->closable()
                                        ->send();

                                    $this->redirect(ListFiles::getUrl(['path' => dirname($this->path)]));
                                } catch (FileNotEditableException) {
                                    AlertBanner::make()
                                        ->title('<code>' . basename($this->path) . '</code> ist ein Verzeichnis')
                                        ->danger()
                                        ->closable()
                                        ->send();

                                    $this->redirect(ListFiles::getUrl(['path' => dirname($this->path)]));
                                } catch (ConnectionException) {
                                    // Alert banner for this one will be handled by ListFiles

                                    $this->redirect(ListFiles::getUrl(['path' => dirname($this->path)]));
                                }
                            })
                            ->language(fn (Get $get) => $get('lang'))
                            ->view('filament.plugins.monaco-editor'),
                    ]),
            ]);
    }

    public function mount(string $path): void
    {
        $this->authorizeAccess();

        $this->path = $path;

        $this->form->fill();

        if (str($path)->endsWith('.pelicanignore')) {
            AlertBanner::make('.pelicanignore_info')
                ->title('Sie bearbeiten eine <code>.pelicanignore</code> Datei!')
                ->body('Alle Dateien und Verzeichnisse, die in dieser Datei aufgelistet sind, werden von Backups ausgeschlossen. Wildcards werden unterstützt, indem Sie einen Stern (<code>*</code>) verwenden. Um eine vorherige Regel zu negieren, fügen Sie ein Ausrufezeichen (<code>!</code>) vor der Regel hinzu.')
                ->info()
                ->closable()
                ->send();

            try {
                $this->getDaemonFileRepository()->getDirectory('/');
            } catch (ConnectionException) {
                AlertBanner::make('node_connection_error')
                    ->title('Konnte keine Verbindung zum Knoten herstellen!')
                    ->danger()
                    ->send();
            }
        }
    }

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()->can(Permission::ACTION_FILE_READ_CONTENT, Filament::getTenant()), 403);
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(static::getResource()::form(
                $this->makeForm()
                    ->statePath($this->getFormStatePath())
                    ->columns($this->hasInlineLabels() ? 1 : 2)
                    ->inlineLabel($this->hasInlineLabels()),
            )),
        ];
    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
        ];

        $previousParts = '';
        foreach (explode('/', $this->path) as $part) {
            $previousParts = $previousParts . '/' . $part;
            $breadcrumbs[self::getUrl(['path' => ltrim($previousParts, '/')])] = $part;
        }

        return $breadcrumbs;
    }

    private function getDaemonFileRepository(): DaemonFileRepository
    {
        /** @var Server $server */
        $server = Filament::getTenant();
        $this->fileRepository ??= (new DaemonFileRepository())->setServer($server);

        return $this->fileRepository;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null): string
    {
        return parent::getUrl($parameters, $isAbsolute, $panel, $tenant) . '/';
    }

    public static function route(string $path): PageRegistration
    {
        return new PageRegistration(
            page: static::class,
            route: fn (Panel $panel): Route => RouteFacade::get($path, static::class)
                ->middleware(static::getRouteMiddleware($panel))
                ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
                ->where('path', '.*'),
        );
    }
}
