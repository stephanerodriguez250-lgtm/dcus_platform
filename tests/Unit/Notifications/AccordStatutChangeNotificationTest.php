<?php

namespace Tests\Unit\Notifications;

use App\Models\Accord;
use App\Models\User;
use App\Notifications\AccordStatutChangeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccordStatutChangeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_database_only(): void
    {
        $modificateur = User::factory()->create();
        $accord = Accord::factory()->create(['titre' => 'Convention UAC', 'statut' => 'signe']);
        $destinataire = User::factory()->create();

        $notification = new AccordStatutChangeNotification($accord, $modificateur, 'en_negotiation', 'signe');

        $this->assertSame(['database'], $notification->via($destinataire));
    }

    public function test_to_array_contains_old_and_new_statut_labels(): void
    {
        $modificateur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $accord = Accord::factory()->create(['titre' => 'Convention UAC', 'statut' => 'signe']);
        $destinataire = User::factory()->create();

        $notification = new AccordStatutChangeNotification($accord, $modificateur, 'en_negotiation', 'signe');
        $data = $notification->toArray($destinataire);

        $this->assertSame('accord_statut', $data['type']);
        $this->assertStringContainsString('Marc Dossou', $data['message']);
        $this->assertStringContainsString('Convention UAC', $data['message']);
        $this->assertStringContainsString('En négociation', $data['message']);
        $this->assertStringContainsString('Signé', $data['message']);
        $this->assertSame(route('accords.show', $accord), $data['url']);
    }
}
