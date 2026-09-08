<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe — DCUS')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line('Vous recevez cet email car une demande de réinitialisation de mot de passe a été effectuée pour votre compte DCUS.')
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien de réinitialisation expirera dans 60 minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, aucune action n'est requise.")
            ->salutation('Plateforme DCUS');
    }
}
