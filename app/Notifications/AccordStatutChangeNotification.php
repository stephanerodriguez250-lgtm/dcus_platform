<?php

namespace App\Notifications;

use App\Models\Accord;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccordStatutChangeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Accord $accord,
        public User $modificateur,
        public string $ancienStatut,
        public string $nouveauStatut,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $ancienLabel = Accord::$statuts[$this->ancienStatut] ?? $this->ancienStatut;
        $nouveauLabel = Accord::$statuts[$this->nouveauStatut] ?? $this->nouveauStatut;

        return [
            'type' => 'accord_statut',
            'icone' => 'bi-arrow-repeat',
            'message' => $this->modificateur->nom_complet.' a changé le statut de l\'accord « '.$this->accord->titre.' » : '.$ancienLabel.' → '.$nouveauLabel.'.',
            'url' => route('accords.show', $this->accord),
        ];
    }
}
