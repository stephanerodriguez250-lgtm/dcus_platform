<?php

namespace Tests\Unit\Notifications;

use App\Models\ArchiveDossierPartage;
use App\Models\ArchiveFolder;
use App\Models\User;
use App\Notifications\DossierPartageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DossierPartageNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_sent_by_mail_and_database(): void
    {
        $expediteur = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['nom' => 'Conventions 2026']);
        $destinataire = User::factory()->create(['prenom' => 'Awa']);
        $partage = ArchiveDossierPartage::factory()->create();

        $notification = new DossierPartageNotification($dossier, $expediteur, $partage);

        $this->assertSame(['mail', 'database'], $notification->via($destinataire));
    }

    public function test_to_array_contains_type_message_note_and_link(): void
    {
        $expediteur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $dossier = ArchiveFolder::factory()->create(['nom' => 'Conventions 2026']);
        $destinataire = User::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create();

        $notification = new DossierPartageNotification($dossier, $expediteur, $partage, 'Voir la clause 4.');
        $data = $notification->toArray($destinataire);

        $this->assertSame('partage_dossier', $data['type']);
        $this->assertStringContainsString('Marc Dossou', $data['message']);
        $this->assertStringContainsString('Conventions 2026', $data['message']);
        $this->assertSame('Voir la clause 4.', $data['note']);
        $this->assertSame(route('archives.dossier-partages.telecharger', $partage), $data['url']);
    }

    public function test_mail_message_contains_sender_name_folder_name_and_greeting(): void
    {
        $expediteur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $dossier = ArchiveFolder::factory()->create(['nom' => 'Conventions 2026']);
        $destinataire = User::factory()->create(['prenom' => 'Awa']);
        $partage = ArchiveDossierPartage::factory()->create();

        $notification = new DossierPartageNotification($dossier, $expediteur, $partage);
        $mail = $notification->toMail($destinataire);

        $this->assertSame('Bonjour Awa,', $mail->greeting);
        $rendered = collect($mail->introLines)->implode(' ');
        $this->assertStringContainsString('Marc Dossou', $rendered);
        $this->assertStringContainsString('Conventions 2026', $rendered);
    }

    public function test_mail_message_action_link_downloads_the_zip_archive(): void
    {
        $expediteur = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create();
        $destinataire = User::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create();

        $notification = new DossierPartageNotification($dossier, $expediteur, $partage);
        $mail = $notification->toMail($destinataire);

        $this->assertSame(route('archives.dossier-partages.telecharger', $partage), $mail->actionUrl);
    }

    public function test_mail_message_includes_the_note_when_provided(): void
    {
        $expediteur = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create();
        $destinataire = User::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create();

        $notification = new DossierPartageNotification($dossier, $expediteur, $partage, 'Voir la clause 4.');
        $mail = $notification->toMail($destinataire);

        $rendered = collect($mail->introLines)->implode(' ');
        $this->assertStringContainsString('Voir la clause 4.', $rendered);
    }

    public function test_mail_message_has_no_note_line_when_note_is_null(): void
    {
        $expediteur = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create();
        $destinataire = User::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create();

        $notification = new DossierPartageNotification($dossier, $expediteur, $partage);
        $mail = $notification->toMail($destinataire);

        $rendered = collect($mail->introLines)->implode(' ');
        $this->assertStringNotContainsString('Note', $rendered);
    }
}
