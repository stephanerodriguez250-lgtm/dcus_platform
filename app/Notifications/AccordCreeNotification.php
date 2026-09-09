<?php

namespace App\Notifications;

use App\Models\Accord;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccordCreeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Accord $accord,
        public User $createur,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'accord',
            'icone' => 'bi-file-earmark-text',
            'message' => $this->createur->nom_complet.' a créé l\'accord « '.$this->accord->titre.' ».',
            'url' => route('accords.show', $this->accord),
        ];
    }
}
