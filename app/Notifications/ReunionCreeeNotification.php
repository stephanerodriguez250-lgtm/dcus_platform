<?php

namespace App\Notifications;

use App\Models\Reunion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReunionCreeeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Reunion $reunion,
        public User $createur,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'reunion',
            'icone' => 'bi-calendar-event',
            'message' => $this->createur->nom_complet.' a créé la réunion « '.$this->reunion->titre.' ».',
            'url' => route('reunions.show', $this->reunion),
        ];
    }
}
