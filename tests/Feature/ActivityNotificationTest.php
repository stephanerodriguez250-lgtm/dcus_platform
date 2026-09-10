<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AccordCreeNotification;
use App\Notifications\DecisionCreeeNotification;
use App\Notifications\ReunionCreeeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_reunion_notifies_other_active_users_but_not_the_creator_or_inactive_users(): void
    {
        Notification::fake();

        $createur = User::factory()->secretaire()->create();
        $autreActif = User::factory()->create(['actif' => true]);
        $inactif = User::factory()->create(['actif' => false]);

        $this->actingAs($createur)->post('/reunions', [
            'titre' => 'CODIR mensuel',
            'date' => now()->addDays(3)->format('Y-m-d'),
            'lieu' => 'Salle A',
            'ordre_du_jour' => 'Discussion des accords en cours.',
            'statut' => 'planifiee',
        ]);

        Notification::assertSentTo($autreActif, ReunionCreeeNotification::class);
        Notification::assertNotSentTo($createur, ReunionCreeeNotification::class);
        Notification::assertNotSentTo($inactif, ReunionCreeeNotification::class);
    }

    public function test_creating_a_decision_notifies_other_active_users(): void
    {
        Notification::fake();

        $createur = User::factory()->secretaire()->create();
        $autreActif = User::factory()->create(['actif' => true]);

        $this->actingAs($createur)->post('/decisions', [
            'intitule' => 'Renouveler la convention',
            'statut' => 'assignee',
            'progression' => 0,
            'source_type' => 'note_ministerielle',
        ]);

        Notification::assertSentTo($autreActif, DecisionCreeeNotification::class);
        Notification::assertNotSentTo($createur, DecisionCreeeNotification::class);
    }

    public function test_creating_an_accord_notifies_other_active_users(): void
    {
        Notification::fake();
        Storage::fake('public');

        $createur = User::factory()->secretaire()->create();
        $autreActif = User::factory()->create(['actif' => true]);

        $this->actingAs($createur)->post('/accords', [
            'titre' => 'Convention UAC',
            'institution_origine' => 'UAC',
            'institution_partenaire' => 'Université Paris-Saclay',
            'reference' => 'MESRS-2026/001',
            'fichier' => UploadedFile::fake()->create('accord.pdf', 100, 'application/pdf'),
        ]);

        Notification::assertSentTo($autreActif, AccordCreeNotification::class);
        Notification::assertNotSentTo($createur, AccordCreeNotification::class);
    }
}
