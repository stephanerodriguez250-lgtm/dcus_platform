<?php

namespace App\Notifications;

use App\Models\Accord;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccordExpirationProcheNotification extends Notification
{
    use Queueable;

    public function __construct(public Accord $accord) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Accord bientôt expiré — '.$this->accord->titre.' — DCUS')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line('L\'accord « '.$this->accord->titre.' » avec '.$this->accord->institution_partenaire.' expire le '.$this->accord->date_expiration->format('d/m/Y').'.')
            ->action('Voir l\'accord', route('accords.show', $this->accord))
            ->salutation('Plateforme DCUS');
    }
}
