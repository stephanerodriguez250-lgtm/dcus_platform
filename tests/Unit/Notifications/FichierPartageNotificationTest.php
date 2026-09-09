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

    public function test_notification_is_sent_by_mail_and_database(): void
    {
        $expediteur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $fichier = ArchiveFichier::factory()->create(['intitule' => 'Convention UAC']);
        $destinataire = User::factory()->create(['prenom' => 'Awa']);
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);

        $notification = new FichierPartageNotification($fichier, $expediteur, $copie);

        $this->assertSame(['mail', 'database'], $notification->via($destinataire));
    }

    public function test_to_array_contains_type_message_note_and_link(): void
    {
        $expediteur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $fichier = ArchiveFichier::factory()->create(['intitule' => 'Convention UAC']);
        $destinataire = User::factory()->create();
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);

        $notification = new FichierPartageNotification($fichier, $expediteur, $copie, 'Voir page 3.');
        $data = $notification->toArray($destinataire);

        $this->assertSame('partage_fichier', $data['type']);
        $this->assertStringContainsString('Marc Dossou', $data['message']);
        $this->assertStringContainsString('Convention UAC', $data['message']);
        $this->assertSame('Voir page 3.', $data['note']);
        $this->assertSame(route('archives.fichiers.download', $copie), $data['url']);
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

    public function test_mail_message_includes_the_note_when_provided(): void
    {
        $expediteur = User::factory()->create();
        $fichier = ArchiveFichier::factory()->create();
        $destinataire = User::factory()->create();
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);

        $notification = new FichierPartageNotification($fichier, $expediteur, $copie, 'Merci de vérifier la page 3.');
        $mail = $notification->toMail($destinataire);

        $rendered = collect($mail->introLines)->implode(' ');
        $this->assertStringContainsString('Merci de vérifier la page 3.', $rendered);
    }

    public function test_mail_message_has_no_note_line_when_note_is_null(): void
    {
        $expediteur = User::factory()->create();
        $fichier = ArchiveFichier::factory()->create();
        $destinataire = User::factory()->create();
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);

        $notification = new FichierPartageNotification($fichier, $expediteur, $copie);
        $mail = $notification->toMail($destinataire);

        $rendered = collect($mail->introLines)->implode(' ');
        $this->assertStringNotContainsString('Note', $rendered);
    }
}
