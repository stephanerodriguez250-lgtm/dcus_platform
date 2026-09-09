<?php

namespace Tests\Feature;

use App\Models\Accord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccordEnvoiSignatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_secretaire_can_mark_an_accord_as_sent(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();

        $response = $this->actingAs($secretaire)->post("/accords/{$accord->id}/envoyer");

        $response->assertRedirect();
        $this->assertNotNull($accord->fresh()->envoye_le);
        $this->assertDatabaseHas('accord_historiques', ['accord_id' => $accord->id]);
    }

    public function test_cannot_mark_an_already_sent_accord_as_sent_again(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create(['envoye_le' => now()->subDay()]);

        $response = $this->actingAs($secretaire)->post("/accords/{$accord->id}/envoyer");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_secretaire_can_record_the_signature_and_expiration_is_calculated(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create(['envoye_le' => now()]);

        $response = $this->actingAs($secretaire)->post("/accords/{$accord->id}/signer", [
            'date_signature' => '2026-01-15',
            'duree_valeur' => 3,
            'duree_unite' => 'ans',
        ]);

        $response->assertRedirect(route('accords.show', $accord));
        $accord->refresh();
        $this->assertSame('2026-01-15', $accord->date_signature->format('Y-m-d'));
        $this->assertSame('2029-01-15', $accord->date_expiration->format('Y-m-d'));
        $this->assertDatabaseHas('accord_historiques', ['accord_id' => $accord->id, 'evenement' => 'Signature enregistrée']);
    }

    public function test_date_signature_defaults_to_today_when_left_empty(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create(['envoye_le' => now()]);

        $this->actingAs($secretaire)->post("/accords/{$accord->id}/signer", [
            'duree_valeur' => 6,
            'duree_unite' => 'mois',
        ]);

        $this->assertSame(now()->format('Y-m-d'), $accord->fresh()->date_signature->format('Y-m-d'));
    }

    public function test_agent_cannot_mark_as_sent_or_signed(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $accord = Accord::factory()->create();

        $this->actingAs($agent)->post("/accords/{$accord->id}/envoyer")->assertForbidden();
        $this->actingAs($agent)->post("/accords/{$accord->id}/signer", ['duree_valeur' => 1, 'duree_unite' => 'ans'])->assertForbidden();
    }
}
