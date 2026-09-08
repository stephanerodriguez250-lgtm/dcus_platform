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

        $notification = new FichierPartageNotification($fichier, $expediteur);

        $this->assertSame(['mail'], $notification->via($destinataire));
    }

    public function test_mail_message_contains_sender_name_file_title_and_greeting(): void
    {
        $expediteur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $fichier = ArchiveFichier::factory()->create(['intitule' => 'Convention UAC']);
        $destinataire = User::factory()->create(['prenom' => 'Awa']);

        $notification = new FichierPartageNotification($fichier, $expediteur);
        $mail = $notification->toMail($destinataire);

        $this->assertSame('Bonjour Awa,', $mail->greeting);
        $rendered = collect($mail->introLines)->implode(' ');
        $this->assertStringContainsString('Marc Dossou', $rendered);
        $this->assertStringContainsString('Convention UAC', $rendered);
    }
}
