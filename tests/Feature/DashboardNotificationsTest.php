<?php

namespace Tests\Feature;

use App\Models\Reunion;
use App\Models\User;
use App\Notifications\ReunionCreeeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_unread_notifications_with_their_message(): void
    {
        $createur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $destinataire = User::factory()->create();
        $reunion = Reunion::factory()->create(['titre' => 'CODIR mensuel']);
        $destinataire->notify(new ReunionCreeeNotification($reunion, $createur));

        $response = $this->actingAs($destinataire)->get('/');

        $response->assertOk();
        $response->assertSee('CODIR mensuel');
        $response->assertSee('Marc Dossou');
    }

    public function test_dashboard_shows_the_unread_notifications_count(): void
    {
        $createur = User::factory()->create();
        $destinataire = User::factory()->create();
        $destinataire->notify(new ReunionCreeeNotification(Reunion::factory()->create(), $createur));
        $destinataire->notify(new ReunionCreeeNotification(Reunion::factory()->create(), $createur));

        $response = $this->actingAs($destinataire)->get('/');

        $response->assertOk();
        $response->assertSee('2');
    }

    public function test_dashboard_with_no_notifications_shows_an_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Aucune notification');
    }
}
