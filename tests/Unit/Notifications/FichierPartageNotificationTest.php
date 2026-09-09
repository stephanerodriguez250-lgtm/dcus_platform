<?php

namespace Tests\Unit\Notifications;

use App\Models\ArchiveFichier;
use App\Models\User;
use App\Notifications\FichierPartageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FichierPartageNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_sent_by_mail_only(): void
    {
        $expediteur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $fichier = ArchiveFichier::factory()->create(['intitule' => 'Convention UAC']);
        $destinataire = User::factory()->create(['prenom' => 'Awa']);
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);

        $notification = new FichierPartageNotification($fichier, $expediteur, $copie);

        $this->assertSame(['mail'], $notification->via($destinataire));
    }

    public function test_mail_message_contains_sender_name_file_title_and_greeting(): void
    {
        $expediteur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $fichier = ArchiveFichier::factory()->create(['intitule' => 'Convention UAC']);
        $destinataire = User::factory()->create(['prenom' => 'Awa']);
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);

        $notification = new FichierPartageNotification($fichier, $expediteur, $copie);
        $mail = $notification->toMail($destinataire);

        $this->assertSame('Bonjour Awa,', $mail->greeting);
        $rendered = collect($mail->introLines)->implode(' ');
        $this->assertStringContainsString('Marc Dossou', $rendered);
        $this->assertStringContainsString('Convention UAC', $rendered);
    }

    public function test_mail_message_action_link_downloads_the_recipients_copy_directly(): void
    {
        $expediteur = User::factory()->create();
        $fichier = ArchiveFichier::factory()->create();
        $destinataire = User::factory()->create();
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);

        $notification = new FichierPartageNotification($fichier, $expediteur, $copie);
        $mail = $notification->toMail($destinataire);

        $this->assertSame(route('archives.fichiers.download', $copie), $mail->actionUrl);
    }
}
