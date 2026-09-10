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

    private function piedDePage(string $chemin): string
    {
        $zip = new ZipArchive;
        $zip->open(Storage::disk('public')->path($chemin));
        $xml = $zip->getFromName('word/footer1.xml');
        $zip->close();

        return (string) $xml;
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
}
