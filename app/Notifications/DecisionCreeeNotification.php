<?php

namespace App\Notifications;

use App\Models\Decision;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DecisionCreeeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Decision $decision,
        public User $createur,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'decision',
            'icone' => 'bi-check2-square',
            'message' => $this->createur->nom_complet.' a créé la décision « '.$this->decision->intitule.' ».',
            'url' => route('decisions.show', $this->decision),
        ];
    }
}
