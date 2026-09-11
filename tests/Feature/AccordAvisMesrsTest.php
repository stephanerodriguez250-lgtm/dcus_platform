<?php

namespace Tests\Feature;

use App\Models\Accord;
use App\Models\AccordAppreciateur;
use App\Models\AccordAppreciation;
use App\Models\User;
use App\Services\GeminiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AccordAvisMesrsTest extends TestCase
{
    use RefreshDatabase;

    public function test_secretaire_can_view_the_form_once_an_appreciation_exists(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $response = $this->actingAs($secretaire)->get(route('accords.avis-mesrs.create', $accord));

        $response->assertOk();
    }

    public function test_cannot_view_the_form_before_an_appreciation_exists(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();

        $response = $this->actingAs($secretaire)->get(route('accords.avis-mesrs.create', $accord));

        $response->assertRedirect(route('accords.show', $accord));
        $response->assertSessionHas('error');
    }

    public function test_agent_without_authorization_cannot_view_the_form(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $response = $this->actingAs($agent)->get(route('accords.avis-mesrs.create', $accord));

        $response->assertForbidden();
    }

    public function test_secretaire_can_upload_the_scanned_ministry_fiche_and_get_a_merged_suggestion(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create([
            'accord_id' => $accord->id,
            'observations_forme' => '- Paginer.',
            'observations_fond' => '- Comité de suivi à préciser.',
        ]);

        $this->mock(GeminiClient::class, function ($mock) {
            $mock->shouldReceive('genererJson')->once()->andReturn([
                'observations_forme' => "- Paginer.\n- Ajouter la date.",
                'observations_fond' => "- Comité de suivi à préciser.\n- Clarifier la résiliation.",
            ]);
        });

        $fichier = UploadedFile::fake()->create('avis-ministere.pdf', 10, 'application/pdf');
        $response = $this->actingAs($secretaire)->postJson(
            route('accords.avis-mesrs.suggestion', $accord),
            ['fichier_ministere' => $fichier]
        );

        $response->assertOk();
        $response->assertJson([
            'observations_forme' => "- Paginer.\n- Ajouter la date.",
            'observations_fond' => "- Comité de suivi à préciser.\n- Clarifier la résiliation.",
            'nom_fiche_ministere' => 'avis-ministere.pdf',
        ]);
        Storage::disk('public')->assertExists($response->json('chemin_fiche_ministere'));
    }

    public function test_suggestion_requires_an_existing_appreciation(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();
        $fichier = UploadedFile::fake()->create('avis-ministere.pdf', 10, 'application/pdf');

        $response = $this->actingAs($secretaire)->postJson(
            route('accords.avis-mesrs.suggestion', $accord),
            ['fichier_ministere' => $fichier]
        );

        $response->assertStatus(422);
    }

    public function test_a_gemini_failure_during_suggestion_returns_a_readable_error_and_removes_the_upload(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $this->mock(GeminiClient::class, function ($mock) {
            $mock->shouldReceive('genererJson')->once()->andThrow(new RuntimeException('Quota dépassé'));
        });

        $fichier = UploadedFile::fake()->create('avis-ministere.pdf', 10, 'application/pdf');
        $response = $this->actingAs($secretaire)->postJson(
            route('accords.avis-mesrs.suggestion', $accord),
            ['fichier_ministere' => $fichier]
        );

        $response->assertStatus(422);
        $response->assertJson(['error' => 'La fusion IA a échoué : Quota dépassé']);
        Storage::disk('public')->assertDirectoryEmpty('accords/avis-mesrs');
    }

    public function test_agent_without_authorization_cannot_request_a_suggestion(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);
        $fichier = UploadedFile::fake()->create('avis-ministere.pdf', 10, 'application/pdf');

        $response = $this->actingAs($agent)->postJson(
            route('accords.avis-mesrs.suggestion', $accord),
            ['fichier_ministere' => $fichier]
        );

        $response->assertForbidden();
    }

    public function test_secretaire_can_validate_the_merged_avis_and_the_fiche_is_regenerated(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);
        $chemin = UploadedFile::fake()->create('avis-ministere.pdf', 10, 'application/pdf')
            ->store('accords/avis-mesrs', 'public');

        $response = $this->actingAs($secretaire)->post(route('accords.avis-mesrs.store', $accord), [
            'chemin_fiche_ministere' => $chemin,
            'nom_fiche_ministere' => 'avis-ministere.pdf',
            'observations_forme' => "- Paginer.\n- Ajouter la date.",
            'observations_fond' => "- Comité de suivi à préciser.\n- Clarifier la résiliation.",
        ]);

        $response->assertRedirect(route('accords.show', $accord));
        $appreciation = $accord->fresh()->appreciation;
        $this->assertNotNull($appreciation->avis_mesrs_valide_le);
        $this->assertSame($secretaire->id, $appreciation->avis_mesrs_valide_par);
        $this->assertSame($chemin, $appreciation->chemin_fiche_ministere);
        $this->assertSame("- Paginer.\n- Ajouter la date.", $appreciation->observations_forme);
        $this->assertNotNull($appreciation->chemin_fiche_word);
        Storage::disk('public')->assertExists($appreciation->chemin_fiche_word);
        $this->assertSame('avis_mesrs', $accord->fresh()->etape);
        $this->assertDatabaseHas('accord_historiques', ['accord_id' => $accord->id]);
    }

    public function test_store_requires_an_existing_appreciation(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();

        $response = $this->actingAs($secretaire)->post(route('accords.avis-mesrs.store', $accord), [
            'chemin_fiche_ministere' => 'accords/avis-mesrs/inexistant.pdf',
            'nom_fiche_ministere' => 'x.pdf',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_store_rejects_a_chemin_fiche_ministere_that_does_not_exist_on_disk(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $response = $this->actingAs($secretaire)->post(route('accords.avis-mesrs.store', $accord), [
            'chemin_fiche_ministere' => 'accords/avis-mesrs/inexistant.pdf',
            'nom_fiche_ministere' => 'x.pdf',
        ]);

        $response->assertSessionHasErrors('chemin_fiche_ministere');
        $this->assertNull($accord->fresh()->appreciation->avis_mesrs_valide_le);
    }

    public function test_resubmitting_updates_instead_of_duplicating_and_deletes_previous_files(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $premierChemin = UploadedFile::fake()->create('avis-1.pdf', 10, 'application/pdf')
            ->store('accords/avis-mesrs', 'public');
        $this->actingAs($secretaire)->post(route('accords.avis-mesrs.store', $accord), [
            'chemin_fiche_ministere' => $premierChemin,
            'nom_fiche_ministere' => 'avis-1.pdf',
            'observations_forme' => '- V1',
            'observations_fond' => '- V1',
        ]);
        $ancienneFicheWord = $accord->fresh()->appreciation->chemin_fiche_word;

        $secondChemin = UploadedFile::fake()->create('avis-2.pdf', 10, 'application/pdf')
            ->store('accords/avis-mesrs', 'public');
        $this->actingAs($secretaire)->post(route('accords.avis-mesrs.store', $accord), [
            'chemin_fiche_ministere' => $secondChemin,
            'nom_fiche_ministere' => 'avis-2.pdf',
            'observations_forme' => '- V2',
            'observations_fond' => '- V2',
        ]);

        $this->assertSame(1, AccordAppreciation::where('accord_id', $accord->id)->count());
        $appreciation = $accord->fresh()->appreciation;
        $this->assertSame($secondChemin, $appreciation->chemin_fiche_ministere);
        $this->assertSame('- V2', $appreciation->observations_forme);
        Storage::disk('public')->assertMissing($premierChemin);
        Storage::disk('public')->assertMissing($ancienneFicheWord);
    }

    public function test_agent_without_authorization_cannot_validate_the_avis(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $response = $this->actingAs($agent)->post(route('accords.avis-mesrs.store', $accord), [
            'chemin_fiche_ministere' => 'x.pdf',
            'nom_fiche_ministere' => 'x.pdf',
        ]);

        $response->assertForbidden();
    }

    public function test_agent_granted_authorization_can_validate_the_avis(): void
    {
        Storage::fake('public');
        $agent = User::factory()->create(['role' => 'agent']);
        AccordAppreciateur::factory()->create(['user_id' => $agent->id]);
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);
        $chemin = UploadedFile::fake()->create('avis.pdf', 10, 'application/pdf')->store('accords/avis-mesrs', 'public');

        $response = $this->actingAs($agent)->post(route('accords.avis-mesrs.store', $accord), [
            'chemin_fiche_ministere' => $chemin,
            'nom_fiche_ministere' => 'avis.pdf',
        ]);

        $response->assertRedirect();
        $this->assertNotNull($accord->fresh()->appreciation->avis_mesrs_valide_le);
    }
}
