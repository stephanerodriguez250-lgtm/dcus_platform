<?php

namespace Tests\Feature;

use App\Models\Reunion;
use App\Models\User;
use App\Notifications\ReunionCreeeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_a_notification_as_read_redirects_to_its_url(): void
    {
        $createur = User::factory()->create();
        $destinataire = User::factory()->create();
        $reunion = Reunion::factory()->create();
        $destinataire->notify(new ReunionCreeeNotification($reunion, $createur));
        $notification = $destinataire->notifications()->first();

        $response = $this->actingAs($destinataire)->post("/notifications/{$notification->id}/lire");

        $response->assertRedirect(route('reunions.show', $reunion));
        $this->assertNotNull($destinataire->notifications()->first()->read_at);
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $createur = User::factory()->create();
        $destinataire = User::factory()->create();
        $autre = User::factory()->create();
        $reunion = Reunion::factory()->create();
        $destinataire->notify(new ReunionCreeeNotification($reunion, $createur));
        $notification = $destinataire->notifications()->first();

        $response = $this->actingAs($autre)->post("/notifications/{$notification->id}/lire");

        $response->assertNotFound();
        $this->assertNull($destinataire->notifications()->first()->read_at);
    }

    public function test_marking_all_notifications_as_read(): void
    {
        $createur = User::factory()->create();
        $destinataire = User::factory()->create();
        $destinataire->notify(new ReunionCreeeNotification(Reunion::factory()->create(), $createur));
        $destinataire->notify(new ReunionCreeeNotification(Reunion::factory()->create(), $createur));

        $response = $this->actingAs($destinataire)->post('/notifications/tout-lire');

        $response->assertRedirect();
        $this->assertSame(0, $destinataire->unreadNotifications()->count());
    }
}
