<?php

namespace Tests\Unit\Notifications;

use App\Models\Reunion;
use App\Models\User;
use App\Notifications\ReunionCreeeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReunionCreeeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_database_only(): void
    {
        $createur = User::factory()->create();
        $reunion = Reunion::factory()->create(['titre' => 'CODIR mensuel']);
        $destinataire = User::factory()->create();

        $notification = new ReunionCreeeNotification($reunion, $createur);

        $this->assertSame(['database'], $notification->via($destinataire));
    }

    public function test_to_array_contains_type_message_and_link(): void
    {
        $createur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $reunion = Reunion::factory()->create(['titre' => 'CODIR mensuel']);
        $destinataire = User::factory()->create();

        $notification = new ReunionCreeeNotification($reunion, $createur);
        $data = $notification->toArray($destinataire);

        $this->assertSame('reunion', $data['type']);
        $this->assertStringContainsString('Marc Dossou', $data['message']);
        $this->assertStringContainsString('CODIR mensuel', $data['message']);
        $this->assertSame(route('reunions.show', $reunion), $data['url']);
    }
}
