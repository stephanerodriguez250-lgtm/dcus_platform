<?php

namespace Tests\Feature;

use App\Models\Accord;
use App\Models\AccordAppreciateur;
use App\Models\AccordAppreciation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccordAppreciationTest extends TestCase
{
    use RefreshDatabase;

    public function test_secretaire_can_submit_an_appreciation_and_a_docx_is_generated(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();

        $response = $this->actingAs($secretaire)->post("/accords/{$accord->id}/apprecier", [
            'origine' => 'Ministère de tutelle',
            'objet' => 'Coopération scientifique',
            'avis' => 'Favorable sous réserve de corrections mineures.',
            'observations_forme' => 'RAS',
            'observations_fond' => 'Clauses financières à préciser',
        ]);

        $response->assertRedirect(route('accords.show', $accord));
        $appreciation = $accord->fresh()->appreciation;
        $this->assertNotNull($appreciation);
        $this->assertSame('Ministère de tutelle', $appreciation->origine);
        $this->assertSame($secretaire->id, $appreciation->redige_par);
        $this->assertNotNull($appreciation->chemin_fiche_word);
        Storage::disk('public')->assertExists($appreciation->chemin_fiche_word);
        $this->assertDatabaseHas('accord_historiques', ['accord_id' => $accord->id]);
    }

    public function test_agent_without_authorization_cannot_apprecier(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $accord = Accord::factory()->create();

        $response = $this->actingAs($agent)->get("/accords/{$accord->id}/apprecier");

        $response->assertForbidden();
    }

    public function test_agent_granted_authorization_can_apprecier(): void
    {
        Storage::fake('public');
        $agent = User::factory()->create(['role' => 'agent']);
        AccordAppreciateur::factory()->create(['user_id' => $agent->id]);
        $accord = Accord::factory()->create();

        $response = $this->actingAs($agent)->post("/accords/{$accord->id}/apprecier", [
            'origine' => 'X', 'objet' => 'Y', 'avis' => 'Z',
        ]);

        $response->assertRedirect();
        $this->assertNotNull($accord->fresh()->appreciation);
    }

    public function test_resubmitting_an_appreciation_updates_it_instead_of_duplicating(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();

        $this->actingAs($secretaire)->post("/accords/{$accord->id}/apprecier", [
            'origine' => 'A', 'objet' => 'B', 'avis' => 'C',
        ]);
        $ancienChemin = $accord->fresh()->appreciation->chemin_fiche_word;

        $this->actingAs($secretaire)->post("/accords/{$accord->id}/apprecier", [
            'origine' => 'A2', 'objet' => 'B2', 'avis' => 'C2',
        ]);

        $this->assertSame(1, AccordAppreciation::where('accord_id', $accord->id)->count());
        $appreciation = $accord->fresh()->appreciation;
        $this->assertSame('A2', $appreciation->origine);
        Storage::disk('public')->assertMissing($ancienChemin);
    }

    public function test_can_download_the_generated_fiche(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();

        $this->actingAs($secretaire)->post("/accords/{$accord->id}/apprecier", [
            'origine' => 'A', 'objet' => 'B', 'avis' => 'C',
        ]);
        $appreciation = $accord->fresh()->appreciation;

        $response = $this->actingAs($secretaire)->get(route('accords.appreciations.telecharger', $appreciation));

        $response->assertOk();
    }
}
