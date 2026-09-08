<?php

namespace App\Notifications;

use App\Models\ArchiveFichier;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FichierPartageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ArchiveFichier $fichier,
        public User $expediteur,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->expediteur->nom_complet.' vous a partagé un fichier — DCUS')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line($this->expediteur->nom_complet.' vous a partagé le fichier « '.$this->fichier->intitule.' » sur la Plateforme DCUS.')
            ->action('Voir mes archives', route('archives.index'))
            ->line('Vous trouverez ce fichier à la racine de votre espace Archives.')
            ->salutation('Plateforme DCUS');
    }
}
