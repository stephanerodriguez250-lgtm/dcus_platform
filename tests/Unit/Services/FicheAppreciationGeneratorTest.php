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
            'avis' => 'Le processus de signature peut être enclenché, sous réserve de la prise en compte des observations faites.',
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
        $this->assertStringContainsString($appreciation->avis, $texte);
        $this->assertStringContainsString($appreciation->observations_forme, $texte);
        $this->assertStringContainsString($appreciation->observations_fond, $texte);
        $this->assertTrue($this->contientUneImage($chemin), "L'en-tête doit inclure le logo du ministère.");

        // Le logo doit flotter (ancré en haut de la marge) plutôt qu'être une image en
        // ligne : c'est ce qui permet aux coordonnées de démarrer à la même hauteur que
        // lui plutôt que de s'empiler dessous (vérifié visuellement après un rendu PDF).
        $this->assertStringContainsString('position:relative', $texte, 'Le logo doit être positionné en flottant, pas en ligne.');
        $this->assertStringContainsString('mso-position-vertical:top', $texte, 'Le logo doit être ancré en haut de la marge.');

        // L'en-tête (logo flottant + coordonnées) ne doit contenir aucun tableau ni aucune
        // tabulation (source du débordement précédent) : les coordonnées restent de simples
        // paragraphes alignés à droite. Tout le reste (Origine/Objet/Référence/Destinataire/
        // Observations/Conclusion) forme un unique tableau, qui doit donc apparaître après
        // les coordonnées dans le document.
        $this->assertSame(1, substr_count($texte, '<w:tbl>'), 'Un seul tableau doit exister dans le document.');
        $this->assertStringNotContainsString('<w:tabs>', $texte, "L'en-tête ne doit plus utiliser de tabulations (source du débordement).");
        $positionCoordonnees = strpos($texte, '01 BP 348 Cotonou');
        $positionTableau = strpos($texte, '<w:tbl>');
        $this->assertNotFalse($positionCoordonnees);
        $this->assertNotFalse($positionTableau);
        $this->assertLessThan($positionTableau, $positionCoordonnees, "L'en-tête doit précéder le tableau, pas y être imbriqué.");

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
