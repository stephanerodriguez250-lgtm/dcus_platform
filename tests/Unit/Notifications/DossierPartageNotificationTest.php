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

    public function test_notification_is_sent_by_mail_only(): void
    {
        $expediteur = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['nom' => 'Conventions 2026']);
        $destinataire = User::factory()->create(['prenom' => 'Awa']);
        $partage = ArchiveDossierPartage::factory()->create();

        $notification = new DossierPartageNotification($dossier, $expediteur, $partage);

        $this->assertSame(['mail'], $notification->via($destinataire));
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
}
