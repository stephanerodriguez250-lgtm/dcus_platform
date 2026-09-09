<?php

namespace Tests\Feature;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Models\AccordRapportConformite;
use App\Models\User;
use App\Services\GeminiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccordConformiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_secretaire_can_run_the_compliance_analysis_and_a_report_is_generated(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $fichier = UploadedFile::fake()->create('signe.pdf', 10, 'application/pdf');
        $accord = Accord::factory()->create(['chemin_fichier_signe' => $fichier->store('accords/signes', 'public')]);
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $this->mock(GeminiClient::class, function ($mock) {
            $mock->shouldReceive('genererJson')->once()->andReturn([
                'resume' => 'Résumé', 'points_conformes' => '- OK', 'points_non_conformes' => '',
            ]);
        });

        $response = $this->actingAs($secretaire)->post(route('accords.analyser-conformite', $accord));

        $response->assertRedirect(route('accords.show', $accord));
        $rapport = $accord->fresh()->rapportConformite;
        $this->assertNotNull($rapport);
        $this->assertSame('Résumé', $rapport->resume);
        $this->assertNotNull($rapport->chemin_rapport_word);
        Storage::disk('public')->assertExists($rapport->chemin_rapport_word);
        $this->assertDatabaseHas('accord_historiques', ['accord_id' => $accord->id]);
    }

    public function test_agent_without_authorization_cannot_run_the_analysis(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $accord = Accord::factory()->create();

        $response = $this->actingAs($agent)->post(route('accords.analyser-conformite', $accord));

        $response->assertForbidden();
    }

    public function test_a_readable_error_is_shown_if_the_signed_file_is_missing(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create(['chemin_fichier_signe' => null]);
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $response = $this->actingAs($secretaire)->post(route('accords.analyser-conformite', $accord));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_resubmitting_the_analysis_updates_it_instead_of_duplicating(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $fichier = UploadedFile::fake()->create('signe.pdf', 10, 'application/pdf');
        $accord = Accord::factory()->create(['chemin_fichier_signe' => $fichier->store('accords/signes', 'public')]);
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $this->mock(GeminiClient::class, function ($mock) {
            $mock->shouldReceive('genererJson')->twice()->andReturn(['resume' => 'R1'], ['resume' => 'R2']);
        });

        $this->actingAs($secretaire)->post(route('accords.analyser-conformite', $accord));
        $ancienChemin = $accord->fresh()->rapportConformite->chemin_rapport_word;

        $this->actingAs($secretaire)->post(route('accords.analyser-conformite', $accord));

        $this->assertSame(1, AccordRapportConformite::where('accord_id', $accord->id)->count());
        $this->assertSame('R2', $accord->fresh()->rapportConformite->resume);
        Storage::disk('public')->assertMissing($ancienChemin);
    }

    public function test_can_download_the_generated_report(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $fichier = UploadedFile::fake()->create('signe.pdf', 10, 'application/pdf');
        $accord = Accord::factory()->create(['chemin_fichier_signe' => $fichier->store('accords/signes', 'public')]);
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $this->mock(GeminiClient::class, function ($mock) {
            $mock->shouldReceive('genererJson')->once()->andReturn(['resume' => 'R']);
        });

        $this->actingAs($secretaire)->post(route('accords.analyser-conformite', $accord));
        $rapport = $accord->fresh()->rapportConformite;

        $response = $this->actingAs($secretaire)->get(route('accords.rapports-conformite.telecharger', $rapport));

        $response->assertOk();
    }

    public function test_agent_without_authorization_cannot_download_the_report(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        $fichier = UploadedFile::fake()->create('signe.pdf', 10, 'application/pdf');
        $accord = Accord::factory()->create(['chemin_fichier_signe' => $fichier->store('accords/signes', 'public')]);
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $this->mock(GeminiClient::class, function ($mock) {
            $mock->shouldReceive('genererJson')->once()->andReturn(['resume' => 'R']);
        });

        $this->actingAs($secretaire)->post(route('accords.analyser-conformite', $accord));
        $rapport = $accord->fresh()->rapportConformite;

        $response = $this->actingAs($agent)->get(route('accords.rapports-conformite.telecharger', $rapport));

        $response->assertForbidden();
    }
}
