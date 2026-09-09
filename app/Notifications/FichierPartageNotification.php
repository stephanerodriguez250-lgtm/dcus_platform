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
        public ArchiveFichier $copie,
        public ?string $note = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'partage_fichier',
            'icone' => 'bi-share',
            'message' => $this->expediteur->nom_complet.' vous a partagé le fichier « '.$this->fichier->intitule.' ».',
            'note' => $this->note,
            'url' => route('archives.fichiers.download', $this->copie),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->expediteur->nom_complet.' vous a partagé un fichier — DCUS')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line($this->expediteur->nom_complet.' vous a partagé le fichier « '.$this->fichier->intitule.' » sur la Plateforme DCUS.');

        if ($this->note) {
            $mail->line('Note de '.$this->expediteur->prenom.' : « '.$this->note.' »');
        }

        return $mail
            ->action('Télécharger le fichier', route('archives.fichiers.download', $this->copie))
            ->line('Vous trouverez aussi ce fichier dans le dossier « Partages reçus » de votre espace Archives.')
            ->salutation('Plateforme DCUS');
    }
}
