<?php

namespace Tests\Unit\Services;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Models\AccordAppreciationExemple;
use App\Services\AccordAppreciationSuggestionGenerator;
use App\Services\GeminiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class AccordAppreciationSuggestionGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generer_transmet_le_contexte_de_laccord_et_les_exemples_fixes(): void
    {
        AccordAppreciationExemple::create([
            'ordre' => 1,
            'origine' => 'Université de Parakou',
            'objet' => 'Etude et avis sur un projet passé',
            'observations_forme' => '- RAS',
            'observations_fond' => '- RAS',
        ]);
        $accord = Accord::factory()->create([
            'titre' => "Accord-cadre de partenariat entre l'UAC et le PAC",
            'reference' => 'MESRS-2026/0142',
        ]);

        $resultatAttendu = [
            'origine' => 'UAC', 'objet' => 'Objet généré',
            'observations_forme' => '- RAS', 'observations_fond' => '- RAS',
        ];

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('genererJson')
            ->once()
            ->withArgs(function (string $prompt, ?array $fichier) use ($accord) {
                return str_contains($prompt, $accord->titre)
                    && str_contains($prompt, $accord->reference)
                    && str_contains($prompt, 'Université de Parakou')
                    && $fichier === null;
            })
            ->andReturn($resultatAttendu);

        $resultat = (new AccordAppreciationSuggestionGenerator($gemini))->generer($accord);

        $this->assertSame($resultatAttendu, $resultat);
    }

    public function test_generer_utilise_les_exemples_fixes_et_non_les_appreciations_reelles(): void
    {
        AccordAppreciationExemple::create([
            'ordre' => 1,
            'origine' => 'Université de Parakou',
            'objet' => 'Exemple fixe choisi par la DCUS',
            'observations_forme' => '- RAS',
            'observations_fond' => '- RAS',
        ]);
        AccordAppreciation::factory()->create([
            'origine' => 'Université de Natitingou',
            'objet' => "Une vraie appréciation saisie par un agent, qui ne doit plus servir d'exemple",
        ]);
        $accord = Accord::factory()->create();

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('genererJson')
            ->once()
            ->withArgs(fn (string $prompt) => str_contains($prompt, 'Université de Parakou')
                && ! str_contains($prompt, 'Université de Natitingou')
            )
            ->andReturn(['origine' => 'X']);

        (new AccordAppreciationSuggestionGenerator($gemini))->generer($accord);
    }

    public function test_generer_joint_le_fichier_pdf_de_laccord_a_lappel_gemini(): void
    {
        Storage::fake('public');
        $fichier = UploadedFile::fake()->create('accord.pdf', 10, 'application/pdf');
        $chemin = $fichier->store('accords', 'public');

        $accord = Accord::factory()->create(['chemin_fichier' => $chemin]);

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('genererJson')
            ->once()
            ->withArgs(fn (string $prompt, ?array $piece) => $piece !== null
                && $piece['mimeType'] === 'application/pdf'
                && $piece['chemin'] === Storage::disk('public')->path($chemin)
            )
            ->andReturn(['origine' => 'X']);

        (new AccordAppreciationSuggestionGenerator($gemini))->generer($accord);
    }
}
