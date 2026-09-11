<?php

namespace Tests\Unit\Services;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Models\User;
use App\Services\FicheAppreciationGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class FicheAppreciationGeneratorTest extends TestCase
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

    private function stylesDocx(string $chemin): string
    {
        $zip = new ZipArchive;
        $zip->open(Storage::disk('public')->path($chemin));
        $xml = $zip->getFromName('word/styles.xml');
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

    public function test_fiche_reproduit_la_mise_en_forme_officielle_de_la_dcus(): void
    {
        Storage::fake('public');

        $redacteur = User::factory()->create();
        $accord = Accord::factory()->create([
            'titre' => "Accord-cadre de partenariat entre l'UAC et le Port Autonome de Cotonou",
            'reference' => 'N°6064-2025/UAC/SG/VR-CIPIP/SRUONB',
        ]);
        $appreciation = AccordAppreciation::factory()->create([
            'accord_id' => $accord->id,
            'origine' => "Université d'Abomey-Calavi (UAC)",
            'objet' => "Etude et avis sur le projet d'accord-cadre de partenariat entre l'UAC et le PAC",
            'avis' => '0053',
            'observations_forme' => 'Paginer le document.',
            'observations_fond' => 'Préciser la composition du comité de suivi.',
            'redige_par' => $redacteur->id,
        ]);

        $chemin = (new FicheAppreciationGenerator)->generer($appreciation);
        $texte = $this->texteDocx($chemin);

        $this->assertStringContainsString('DIRECTION DE LA COOPÉRATION UNIVERSITAIRE ET SCIENTIFIQUE', $texte);
        $this->assertStringContainsString('Arr', $texte); // "Arrêté 2022 N° 0145/..." (accents fragment sensitive)
        $this->assertStringContainsString('0145/MESRS/DC/SGM/DCUS/CJ/SA/028SGG22', $texte);
        $this->assertStringContainsString('Observations sur la forme', $texte);
        $this->assertStringContainsString('Observations sur le fond', $texte);
        $this->assertStringContainsString('Conclusion', $texte);
        $this->assertStringContainsString($appreciation->origine, $texte);
        $this->assertStringContainsString($appreciation->objet, $texte);
        $this->assertStringContainsString($accord->reference, $texte);
        $this->assertStringContainsString($appreciation->observations_forme, $texte);
        $this->assertStringContainsString($appreciation->observations_fond, $texte);
        $this->assertTrue($this->contientUneImage($chemin), "L'en-tête doit être l'image officielle du ministère.");

        // L'en-tête est désormais une seule image officielle (fournie par l'utilisateur,
        // logo + coordonnées déjà intégrés) — plus aucune coordonnée n'est composée en texte.
        $this->assertStringNotContainsString('01 BP 348 Cotonou', $texte);
        $this->assertStringNotContainsString('contact.mesrs@gouv.bj', $texte);

        // Le numéro d'avis (identifiant de la fiche, saisi par l'agent) remplit le blanc en
        // tête de document — ce n'est PAS la Conclusion.
        $this->assertStringContainsString('Avis N° 0053 /MESRS/DCUS/', $texte);

        // La Conclusion n'est plus saisie : elle est entièrement générée à partir de
        // l'intitulé de l'accord (Accord::$conclusion_appreciation), avec la formulation fixe
        // imposée par la DCUS.
        $this->assertStringContainsString($accord->conclusion_appreciation, $texte);
        $this->assertStringContainsString("le processus de signature de l'accord-cadre de partenariat entre l'UAC et le Port Autonome de Cotonou peut être enclenché, sous réserve de la prise en compte des observations faites.", $texte);

        // L'en-tête (une seule image officielle) ne doit contenir aucun tableau ni aucune
        // tabulation (source du débordement de l'ancienne technique par texte+tabulation).
        // Tout le reste (Origine/Objet/Référence/Destinataire/Observations/Conclusion) forme
        // un unique tableau, qui doit donc apparaître après l'en-tête dans le document.
        $this->assertSame(1, substr_count($texte, '<w:tbl>'), 'Un seul tableau doit exister dans le document.');
        $this->assertStringNotContainsString('<w:tabs>', $texte, "L'en-tête ne doit utiliser aucune tabulation.");
        $positionDate = strpos($texte, 'Abomey-Calavi, le');
        $positionTableau = strpos($texte, '<w:tbl>');
        $this->assertNotFalse($positionDate);
        $this->assertNotFalse($positionTableau);
        $this->assertLessThan($positionTableau, $positionDate, "L'en-tête doit précéder le tableau, pas y être imbriqué.");

        // La marge supérieure doit être réduite pour remonter l'en-tête en haut de la page.
        $this->assertStringContainsString('w:top="600"', $texte);
        $this->assertStringContainsString('w:left="900"', $texte);
        $this->assertStringContainsString('w:right="900"', $texte);

        // La phrase introductive doit se trouver DANS le tableau, dans la même cellule que
        // "1°) Observations sur la forme", juste au-dessus — plus avant le tableau.
        $positionIntro = strpos($texte, "Dans le cadre de l'objet suscité");
        $positionObservationsForme = strpos($texte, '1°) Observations sur la forme');
        $this->assertNotFalse($positionIntro);
        $this->assertGreaterThan($positionTableau, $positionIntro, 'La phrase introductive doit être dans le tableau.');
        $this->assertLessThan($positionObservationsForme, $positionIntro, 'La phrase introductive doit précéder "1°) Observations sur la forme".');
    }

    public function test_le_document_est_pagine_automatiquement(): void
    {
        Storage::fake('public');

        $appreciation = AccordAppreciation::factory()->create();

        $chemin = (new FicheAppreciationGenerator)->generer($appreciation);
        $piedDePage = $this->piedDePage($chemin);

        $this->assertStringContainsString('PAGE', $piedDePage);
        $this->assertStringContainsString('NUMPAGES', $piedDePage);
    }

    public function test_le_document_utilise_trebuchet_ms_avec_le_titre_en_plus_grand(): void
    {
        Storage::fake('public');

        $accord = Accord::factory()->create(['titre' => "Accord-cadre de partenariat entre l'UAC et le PAC"]);
        $appreciation = AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $chemin = (new FicheAppreciationGenerator)->generer($appreciation);
        $styles = $this->stylesDocx($chemin);
        $texte = $this->texteDocx($chemin);

        // Police et taille par défaut du document entier : Trebuchet MS 12 (w:sz est en
        // demi-points, donc 12pt = 24).
        $this->assertStringContainsString('w:ascii="Trebuchet MS"', $styles);
        $this->assertStringContainsString('w:sz w:val="24"', $styles);

        // Le titre du document ("FICHE D'APPRÉCIATION...") doit être en 14pt (w:sz val=28) —
        // la seule taille 14 utilisée dans tout le document.
        $this->assertStringContainsString('w:sz w:val="28"', $texte);
    }

    public function test_les_lignes_avec_puce_deviennent_des_listes_a_puces_avec_sous_niveaux(): void
    {
        Storage::fake('public');

        $accord = Accord::factory()->create();
        $appreciation = AccordAppreciation::factory()->create([
            'accord_id' => $accord->id,
            'observations_forme' => "Le projet est structuré en huit (08) articles. Cependant, il est recommandé de :\n- écrire « CHAPITRE III » au lieu de « chapitre V »\n  - à la page 5 uniquement\n- titrer chaque article conformément au contenu",
        ]);

        $chemin = (new FicheAppreciationGenerator)->generer($appreciation);
        $texte = $this->texteDocx($chemin);

        // La phrase d'introduction (sans puce) reste un paragraphe simple, sans marqueur de liste.
        $this->assertStringContainsString('Le projet est structuré', $texte);

        // Les lignes à puce utilisent la numérotation de liste PhpWord (w:numPr/w:ilvl).
        $this->assertStringContainsString('écrire « CHAPITRE III »', $texte);
        $this->assertStringContainsString('à la page 5 uniquement', $texte);
        $this->assertStringContainsString('titrer chaque article', $texte);
        $this->assertMatchesRegularExpression('/<w:ilvl w:val="0"\/>/', $texte);
        $this->assertMatchesRegularExpression('/<w:ilvl w:val="1"\/>/', $texte);
    }
}
