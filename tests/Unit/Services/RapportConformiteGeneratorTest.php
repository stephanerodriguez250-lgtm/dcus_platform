<?php

namespace Tests\Unit\Services;

use App\Models\Accord;
use App\Models\AccordRapportConformite;
use App\Models\User;
use App\Services\RapportConformiteGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class RapportConformiteGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private function texteDocx(string $chemin): string
    {
        $zip = new ZipArchive;
        $zip->open(Storage::disk('public')->path($chemin));
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        return $xml;
    }

    private function stylesDocx(string $chemin): string
    {
        $zip = new ZipArchive;
        $zip->open(Storage::disk('public')->path($chemin));
        $xml = $zip->getFromName('word/styles.xml');
        $zip->close();

        return (string) $xml;
    }

    private function piedDePage(string $chemin): string
    {
        $zip = new ZipArchive;
        $zip->open(Storage::disk('public')->path($chemin));
        $xml = $zip->getFromName('word/footer1.xml');
        $zip->close();

        return (string) $xml;
    }

    private function contientUneImage(string $chemin): bool
    {
        $zip = new ZipArchive;
        $zip->open(Storage::disk('public')->path($chemin));

        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (str_starts_with($zip->getNameIndex($i), 'word/media/')) {
                $zip->close();

                return true;
            }
        }

        $zip->close();

        return false;
    }

    public function test_genere_un_rapport_word_avec_le_resume_et_les_points(): void
    {
        Storage::fake('public');

        $redacteur = User::factory()->create();
        $accord = Accord::factory()->create([
            'titre' => 'Accord-cadre entre UAC et PAC',
            'reference' => 'MESRS-2026/0142',
            'date_signature' => '2026-05-01',
        ]);
        $rapport = AccordRapportConformite::factory()->create([
            'accord_id' => $accord->id,
            'resume' => "Le document signé reprend l'essentiel des observations formulées.",
            'points_conformes' => "- Pagination corrigée\n- Comité de suivi précisé",
            'points_non_conformes' => '- Article 8 non renommé',
            'genere_par' => $redacteur->id,
            'genere_le' => now(),
        ]);

        $chemin = (new RapportConformiteGenerator)->generer($rapport);
        $texte = $this->texteDocx($chemin);

        $this->assertStringContainsString($accord->titre, $texte);
        $this->assertStringContainsString($accord->reference, $texte);
        $this->assertStringContainsString($rapport->resume, $texte);
        $this->assertStringContainsString('Pagination corrigée', $texte);
        $this->assertStringContainsString('Article 8 non renommé', $texte);
        $this->assertMatchesRegularExpression('/<w:ilvl w:val="0"\/>/', $texte);
    }

    public function test_le_document_est_pagine_automatiquement(): void
    {
        Storage::fake('public');

        $rapport = AccordRapportConformite::factory()->create();

        $chemin = (new RapportConformiteGenerator)->generer($rapport);
        $piedDePage = $this->piedDePage($chemin);

        $this->assertStringContainsString('PAGE', $piedDePage);
        $this->assertStringContainsString('NUMPAGES', $piedDePage);
    }

    public function test_le_rapport_reproduit_le_meme_gabarit_que_la_fiche_dappreciation(): void
    {
        Storage::fake('public');

        $accord = Accord::factory()->create([
            'titre' => "Accord-cadre de partenariat entre l'UAC et le PAC",
            'reference' => 'MESRS-2026/0142',
        ]);
        $rapport = AccordRapportConformite::factory()->create(['accord_id' => $accord->id]);

        $chemin = (new RapportConformiteGenerator)->generer($rapport);
        $texte = $this->texteDocx($chemin);
        $styles = $this->stylesDocx($chemin);

        // Même en-tête ministériel (logo flottant + coordonnées) que la fiche d'appréciation.
        $this->assertTrue($this->contientUneImage($chemin), "L'en-tête doit inclure le logo du ministère.");
        $this->assertStringContainsString('01 BP 348 Cotonou', $texte);
        $this->assertStringContainsString('Fax : +229 21 324188', $texte);
        $this->assertStringContainsString('contact.mesrs@gouv.bj', $texte);
        $this->assertStringContainsString('DIRECTION DE LA COOPÉRATION UNIVERSITAIRE ET SCIENTIFIQUE', $texte);
        $this->assertStringContainsString('0145/MESRS/DC/SGM/DCUS/CJ/SA/028SGG22', $texte);
        $this->assertStringContainsString('position:relative', $texte, 'Le logo doit être positionné en flottant, pas en ligne.');

        // Même police (Trebuchet MS 12pt par défaut, titre en 14pt).
        $this->assertStringContainsString('w:ascii="Trebuchet MS"', $styles);
        $this->assertStringContainsString('w:sz w:val="24"', $styles);
        $this->assertStringContainsString('w:sz w:val="28"', $texte);

        // Même titre construit à partir de l'intitulé de l'accord, même convention que la fiche.
        $this->assertStringContainsString(strtoupper("RAPPORT DE CONFORMITÉ DU PROJET D'".$accord->titre), $texte);

        // Même structure : un unique tableau bordé, aucune tabulation, en-tête avant le tableau.
        $this->assertSame(1, substr_count($texte, '<w:tbl>'), 'Un seul tableau doit exister dans le document.');
        $this->assertStringNotContainsString('<w:tabs>', $texte);
        $this->assertStringContainsString('w:top="600"', $texte);
        $this->assertStringContainsString('w:left="900"', $texte);
        $this->assertStringContainsString('w:right="900"', $texte);
    }
}
