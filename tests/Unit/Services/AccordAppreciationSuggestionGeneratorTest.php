<?php

namespace Tests\Unit\Services;

use App\Models\Accord;
use App\Models\AccordAppreciation;
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

    public function test_generer_transmet_le_contexte_de_laccord_et_les_exemples_passes(): void
    {
        AccordAppreciation::factory()->create([
            'origine' => 'Université de Parakou',
            'objet' => 'Etude et avis sur un projet passé',
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
