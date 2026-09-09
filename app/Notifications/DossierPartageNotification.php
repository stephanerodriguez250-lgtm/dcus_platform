<?php

namespace App\Notifications;

use App\Models\ArchiveDossierPartage;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DossierPartageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ArchiveFolder $dossier,
        public User $expediteur,
        public ArchiveDossierPartage $partage,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->expediteur->nom_complet.' vous a partagé un dossier — DCUS')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line($this->expediteur->nom_complet.' vous a partagé le dossier « '.$this->dossier->nom.' » sur la Plateforme DCUS.')
            ->action('Télécharger le dossier (.zip)', route('archives.dossier-partages.telecharger', $this->partage))
            ->line('Vous trouverez aussi ce dossier, non compressé, à la racine de votre espace Archives.')
            ->salutation('Plateforme DCUS');
    }
}
