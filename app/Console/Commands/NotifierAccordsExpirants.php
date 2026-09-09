<?php

namespace App\Console\Commands;

use App\Models\Accord;
use App\Models\User;
use App\Notifications\AccordExpirationProcheNotification;
use Illuminate\Console\Command;

class NotifierAccordsExpirants extends Command
{
    protected $signature = 'accords:notifier-expiration';

    protected $description = "Notifie tous les utilisateurs actifs des accords dont l'expiration approche (60 jours)";

    public function handle(): void
    {
        $accords = Accord::whereNotNull('date_expiration')
            ->whereNull('alerte_expiration_envoyee_le')
            ->whereBetween('date_expiration', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->get();

        $utilisateurs = User::where('actif', true)->get();

        foreach ($accords as $accord) {
            foreach ($utilisateurs as $utilisateur) {
                $utilisateur->notify(new AccordExpirationProcheNotification($accord));
            }
            $accord->update(['alerte_expiration_envoyee_le' => now()]);
        }

        $this->info($accords->count().' accord(s) notifié(s).');
    }
}
