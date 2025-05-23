<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Webmozart\Assert\Assert;
use Illuminate\Console\Command;

class DeleteUserCommand extends Command
{
    protected $description = 'Löscht einen Benutzer aus dem Panel, wenn keine Server an ihre Konten angehängt sind.';

    protected $signature = 'p:user:delete {--user=}';

    public function handle(): int
    {
        $search = $this->option('user') ?? $this->ask(trans('command/messages.user.search_users'));
        Assert::notEmpty($search, 'Suchbegriff sollte nicht leer sein.');

        $results = User::query()
            ->where('id', 'LIKE', "$search%")
            ->orWhere('username', 'LIKE', "$search%")
            ->orWhere('email', 'LIKE', "$search%")
            ->get();

        if (count($results) < 1) {
            $this->error(trans('command/messages.user.no_users_found'));
            if ($this->input->isInteractive()) {
                return $this->handle();
            }

            return 1;
        }

        if ($this->input->isInteractive()) {
            $tableValues = [];
            foreach ($results as $user) {
                $tableValues[] = [$user->id, $user->email, $user->name];
            }

            $this->table(['User ID', 'Email', 'Name'], $tableValues);
            if (!$deleteUser = $this->ask(trans('command/messages.user.select_search_user'))) {
                return $this->handle();
            }

            $deleteUser = User::query()->findOrFail($deleteUser);
        } else {
            if (count($results) > 1) {
                $this->error(trans('command/messages.user.multiple_found'));

                return 1;
            }

            $deleteUser = $results->first();
        }

        if ($this->confirm(trans('command/messages.user.confirm_delete')) || !$this->input->isInteractive()) {
            $deleteUser->delete();

            $this->info(trans('command/messages.user.deleted'));
        }

        return 0;
    }
}
