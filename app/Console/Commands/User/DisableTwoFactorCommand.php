<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;

class DisableTwoFactorCommand extends Command
{
    protected $description = 'Deaktiviert die Zwei-Faktor-Authentifizierung für einen bestimmten Benutzer im Panel.';

    protected $signature = 'p:user:disable2fa {--email= : Die E-Mail-Adresse des Benutzers, dessen Zwei-Faktor-Authentifizierung deaktiviert werden soll.}';

    /**
     * Handle command execution process.
     *
     * @throws \App\Exceptions\Model\DataValidationException
     */
    public function handle(): void
    {
        if ($this->input->isInteractive()) {
            $this->output->warning(trans('command/messages.user.2fa_help_text.0') . trans('command/messages.user.2fa_help_text.1'));
        }

        $email = $this->option('email') ?? $this->ask(trans('command/messages.user.ask_email'));

        $user = User::query()->where('email', $email)->firstOrFail();
        $user->use_totp = false;
        $user->totp_secret = null;
        $user->save();

        $this->info(trans('command/messages.user.2fa_disabled', ['email' => $user->email]));
    }
}
