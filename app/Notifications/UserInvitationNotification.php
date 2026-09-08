<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url('/inscription/'.$this->token);

        return (new MailMessage)
            ->subject('Invitation à rejoindre la Plateforme DCUS')
            ->greeting('Bonjour,')
            ->line('Vous avez été invité(e) à créer un compte sur la Plateforme DCUS.')
            ->action('Créer mon compte', $url)
            ->line('Ce lien d\'invitation expirera dans 7 jours.')
            ->line('Si vous ne vous attendiez pas à cette invitation, vous pouvez ignorer cet email.')
            ->salutation('Plateforme DCUS');
    }
}
