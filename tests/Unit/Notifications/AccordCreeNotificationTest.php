<?php

namespace Tests\Unit\Notifications;

use App\Models\Accord;
use App\Models\User;
use App\Notifications\AccordCreeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccordCreeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_database_only(): void
    {
        $createur = User::factory()->create();
        $accord = Accord::factory()->create(['titre' => 'Convention UAC']);
        $destinataire = User::factory()->create();

        $notification = new AccordCreeNotification($accord, $createur);

        $this->assertSame(['database'], $notification->via($destinataire));
    }

    public function test_to_array_contains_type_message_and_link(): void
    {
        $createur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $accord = Accord::factory()->create(['titre' => 'Convention UAC']);
        $destinataire = User::factory()->create();

        $notification = new AccordCreeNotification($accord, $createur);
        $data = $notification->toArray($destinataire);

        $this->assertSame('accord', $data['type']);
        $this->assertStringContainsString('Marc Dossou', $data['message']);
        $this->assertStringContainsString('Convention UAC', $data['message']);
        $this->assertSame(route('accords.show', $accord), $data['url']);
    }
}
