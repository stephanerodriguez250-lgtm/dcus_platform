<?php

namespace Tests\Unit\Notifications;

use App\Models\Decision;
use App\Models\User;
use App\Notifications\DecisionCreeeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecisionCreeeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_database_only(): void
    {
        $createur = User::factory()->create();
        $decision = new Decision(['intitule' => 'Renouveler la convention']);
        $decision->id = 7;
        $destinataire = User::factory()->create();

        $notification = new DecisionCreeeNotification($decision, $createur);

        $this->assertSame(['database'], $notification->via($destinataire));
    }

    public function test_to_array_contains_type_message_and_link(): void
    {
        $createur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $decision = new Decision(['intitule' => 'Renouveler la convention']);
        $decision->id = 7;
        $destinataire = User::factory()->create();

        $notification = new DecisionCreeeNotification($decision, $createur);
        $data = $notification->toArray($destinataire);

        $this->assertSame('decision', $data['type']);
        $this->assertStringContainsString('Marc Dossou', $data['message']);
        $this->assertStringContainsString('Renouveler la convention', $data['message']);
        $this->assertSame(route('decisions.show', $decision), $data['url']);
    }
}
