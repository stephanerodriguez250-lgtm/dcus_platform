<?php

namespace Tests\Unit\Services;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Services\AccordAvisMesrsFusionGenerator;
use App\Services\GeminiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class AccordAvisMesrsFusionGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_fusionner_transmet_les_observations_dcus_existantes_et_retourne_la_fusion(): void
    {
        Storage::fake('public');
        $accord = Accord::factory()->create(['titre' => "Accord-cadre entre l'UAC et le PAC"]);
        AccordAppreciation::factory()->create([
            'accord_id' => $accord->id,
            'observations_forme' => '- Paginer le document.',
            'observations_fond' => '- Préciser le comité de suivi.',
        ]);
        $accord->load('appreciation');

        $fichier = UploadedFile::fake()->create('avis-ministere.pdf', 10, 'application/pdf');
        $chemin = $fichier->store('accords/avis-mesrs', 'public');

        $resultatAttendu = [
            'observations_forme' => "- Paginer le document.\n- Ajouter la date de signature.",
            'observations_fond' => "- Préciser le comité de suivi.\n- Clarifier la clause de résiliation.",
        ];

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('genererJson')
            ->once()
            ->withArgs(fn (string $prompt) => str_contains($prompt, $accord->titre)
                && str_contains($prompt, 'Paginer le document.')
                && str_contains($prompt, 'Préciser le comité de suivi.')
            )
            ->andReturn($resultatAttendu);

        $resultat = (new AccordAvisMesrsFusionGenerator($gemini))->fusionner($accord, $chemin);

        $this->assertSame($resultatAttendu, $resultat);
    }

    public function test_fusionner_joint_le_fichier_scanne_du_ministere(): void
    {
        Storage::fake('public');
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);
        $accord->load('appreciation');

        $fichier = UploadedFile::fake()->create('avis-ministere.png', 10, 'image/png');
        $chemin = $fichier->store('accords/avis-mesrs', 'public');

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('genererJson')
            ->once()
            ->withArgs(fn (string $prompt, ?array $piece) => $piece !== null
                && $piece['mimeType'] === 'image/png'
                && $piece['chemin'] === Storage::disk('public')->path($chemin)
            )
            ->andReturn(['observations_forme' => '', 'observations_fond' => '']);

        (new AccordAvisMesrsFusionGenerator($gemini))->fusionner($accord, $chemin);
    }

    public function test_le_prompt_demande_explicitement_de_ne_rien_omettre_et_deviter_les_doublons(): void
    {
        Storage::fake('public');
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);
        $accord->load('appreciation');

        $fichier = UploadedFile::fake()->create('avis-ministere.pdf', 10, 'application/pdf');
        $chemin = $fichier->store('accords/avis-mesrs', 'public');

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('genererJson')
            ->once()
            ->withArgs(fn (string $prompt) => str_contains($prompt, 'Conserve INTÉGRALEMENT')
                && str_contains($prompt, 'répète')
                && str_contains($prompt, "N'omets AUCUN point")
            )
            ->andReturn(['observations_forme' => '', 'observations_fond' => '']);

        (new AccordAvisMesrsFusionGenerator($gemini))->fusionner($accord, $chemin);
    }
}
