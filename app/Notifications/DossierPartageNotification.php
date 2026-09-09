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
        public ?string $note = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'partage_dossier',
            'icone' => 'bi-folder-symlink',
            'message' => $this->expediteur->nom_complet.' vous a partagé le dossier « '.$this->dossier->nom.' ».',
            'note' => $this->note,
            'url' => route('archives.dossier-partages.telecharger', $this->partage),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->expediteur->nom_complet.' vous a partagé un dossier — DCUS')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line($this->expediteur->nom_complet.' vous a partagé le dossier « '.$this->dossier->nom.' » sur la Plateforme DCUS.');

        if ($this->note) {
            $mail->line('Note de '.$this->expediteur->prenom.' : « '.$this->note.' »');
        }

        return $mail
            ->action('Télécharger le dossier (.zip)', route('archives.dossier-partages.telecharger', $this->partage))
            ->line('Vous trouverez aussi ce dossier, non compressé, dans le dossier « Partages reçus » de votre espace Archives.')
            ->salutation('Plateforme DCUS');
    }
}
