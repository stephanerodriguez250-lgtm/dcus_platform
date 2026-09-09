<?php

namespace Tests\Unit\Services;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Services\AccordConformiteAnalyzer;
use App\Services\GeminiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class AccordConformiteAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    public function test_analyser_transmet_les_observations_de_la_fiche_et_retourne_le_resultat(): void
    {
        Storage::fake('public');
        $fichier = UploadedFile::fake()->create('signe.pdf', 10, 'application/pdf');
        $accord = Accord::factory()->create([
            'titre' => 'Accord X',
            'chemin_fichier_signe' => $fichier->store('accords/signes', 'public'),
        ]);
        AccordAppreciation::factory()->create([
            'accord_id' => $accord->id,
            'observations_forme' => '- Paginer le document.',
            'observations_fond' => '- Préciser le comité de suivi.',
            'avis' => 'Favorable sous réserve.',
        ]);
        $accord->load('appreciation');

        $resultatAttendu = [
            'resume' => 'Résumé...',
            'points_conformes' => '- Pagination faite',
            'points_non_conformes' => '- Comité de suivi absent',
        ];

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('genererJson')
            ->once()
            ->withArgs(fn (string $prompt) => str_contains($prompt, $accord->titre)
                && str_contains($prompt, 'Paginer le document.')
                && str_contains($prompt, 'Préciser le comité de suivi.')
            )
            ->andReturn($resultatAttendu);

        $resultat = (new AccordConformiteAnalyzer($gemini))->analyser($accord);

        $this->assertSame($resultatAttendu, $resultat);
    }

    public function test_analyser_joint_le_fichier_signe_pdf(): void
    {
        Storage::fake('public');
        $fichier = UploadedFile::fake()->create('signe.pdf', 10, 'application/pdf');
        $chemin = $fichier->store('accords/signes', 'public');

        $accord = Accord::factory()->create(['chemin_fichier_signe' => $chemin]);
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);
        $accord->load('appreciation');

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('genererJson')
            ->once()
            ->withArgs(fn (string $prompt, ?array $piece) => $piece !== null
                && $piece['mimeType'] === 'application/pdf'
                && $piece['chemin'] === Storage::disk('public')->path($chemin)
            )
            ->andReturn(['resume' => 'x']);

        (new AccordConformiteAnalyzer($gemini))->analyser($accord);
    }

    public function test_analyser_leve_une_exception_si_laccord_na_pas_dappreciation(): void
    {
        $accord = Accord::factory()->create(['chemin_fichier_signe' => 'accords/signes/x.pdf']);
        $gemini = Mockery::mock(GeminiClient::class);

        $this->expectException(RuntimeException::class);

        (new AccordConformiteAnalyzer($gemini))->analyser($accord);
    }

    public function test_analyser_leve_une_exception_si_aucun_fichier_signe(): void
    {
        $accord = Accord::factory()->create(['chemin_fichier_signe' => null]);
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);
        $accord->load('appreciation');

        $gemini = Mockery::mock(GeminiClient::class);

        $this->expectException(RuntimeException::class);

        (new AccordConformiteAnalyzer($gemini))->analyser($accord);
    }
}
