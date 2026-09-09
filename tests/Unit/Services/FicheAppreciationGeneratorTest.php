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
        $this->assertTrue($this->contientUneImage($chemin), "L'en-tête doit inclure les armoiries du Bénin.");
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
