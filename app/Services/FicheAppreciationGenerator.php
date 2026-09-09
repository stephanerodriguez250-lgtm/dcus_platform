<?php

namespace App\Services;

use App\Models\AccordAppreciation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Génère la fiche d'appréciation au format .docx, reproduisant la mise en forme
 * officielle des fiches DCUS (en-tête ministériel, blocs Origine/Objet/Référence/
 * Destinataire, sections "Observations sur la forme"/"sur le fond", conclusion encadrée).
 *
 * Le numéro d'avis ("Avis N°.../MESRS/DCUS/{année}") est laissé vide sur le document
 * généré : comme sur les fiches papier, il est attribué et complété à la main par la
 * DCUS. Les armoiries du Bénin (resources/images/armoiries-benin.png) sont intégrées
 * dans l'en-tête ; seul le blason est utilisé (fourni par l'utilisateur), le texte du
 * ministère reste codé en dur car le fichier reçu portait le nom d'un autre ministère.
 */
class FicheAppreciationGenerator
{
    public function generer(AccordAppreciation $appreciation): string
    {
        $accord = $appreciation->accord;

        $phpWord = new PhpWord;
        $section = $phpWord->addSection();

        $this->ajouterEnTete($section);

        $section->addText(
            'Avis N° ______________ /MESRS/DCUS/'.now()->format('Y'),
            ['bold' => true],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'DIRECTION DE LA COOPÉRATION UNIVERSITAIRE ET SCIENTIFIQUE',
            ['bold' => true, 'italic' => true, 'size' => 12],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'Arrêté 2022 N° 0145/MESRS/DC/SGM/DCUS/CJ/SA/028SGG22',
            ['italic' => true, 'size' => 10],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(1);
        $section->addText(
            strtoupper("FICHE D'APPRÉCIATION DU PROJET D'".$accord->titre),
            ['bold' => true, 'size' => 12],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(1);

        $reference = $accord->reference.' du '.$accord->date_arrivee->locale('fr')->translatedFormat('d F Y');

        $this->ajouterChampEncadre($section, 'Origine', $appreciation->origine);
        $this->ajouterChampEncadre($section, 'Objet', $appreciation->objet);
        $this->ajouterChampEncadre($section, 'Référence', $reference);
        $this->ajouterChampEncadre($section, 'Destinataire', $appreciation->origine);

        $section->addTextBreak(1);
        $section->addText(
            "Dans le cadre de l'objet suscité, la DCUS a procédé à une analyse minutieuse du projet et y a relevé les éléments d'appréciation ci-après :"
        );
        $section->addTextBreak(1);

        $section->addText('1°) Observations sur la forme', ['bold' => true]);
        $this->ajouterParagraphes($section, $appreciation->observations_forme);
        $section->addTextBreak(1);

        $section->addText('2°) Observations sur le fond', ['bold' => true]);
        $this->ajouterParagraphes($section, $appreciation->observations_fond);
        $section->addTextBreak(1);

        $conclusion = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 100]);
        $conclusion->addRow();
        $texteRun = $conclusion->addCell(9350)->addTextRun();
        $texteRun->addText('Conclusion : ', ['bold' => true]);
        $texteRun->addText($appreciation->avis ?: '—');

        $section->addTextBreak(2);
        $section->addText('Rédigé par : '.$appreciation->redacteur->nom_complet, ['size' => 9, 'italic' => true]);
        $section->addText('Le : '.$appreciation->created_at->locale('fr')->translatedFormat('d M Y à H:i'), ['size' => 9, 'italic' => true]);

        Storage::disk('public')->makeDirectory('accords/appreciations');
        $chemin = 'accords/appreciations/'.Str::random(40).'.docx';

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save(Storage::disk('public')->path($chemin));

        return $chemin;
    }

    private function ajouterEnTete(Section $section): void
    {
        $enTete = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $enTete->addRow();

        $celluleLogo = $enTete->addCell(1100);
        $cheminLogo = resource_path('images/armoiries-benin.png');
        if (is_file($cheminLogo)) {
            $celluleLogo->addImage($cheminLogo, ['width' => 55, 'height' => 55]);
        }

        $gauche = $enTete->addCell(4400);
        $gauche->addText('MINISTÈRE', ['bold' => true, 'size' => 9]);
        $gauche->addText("DE L'ENSEIGNEMENT SUPÉRIEUR", ['bold' => true, 'size' => 9]);
        $gauche->addText('ET DE LA RECHERCHE SCIENTIFIQUE', ['bold' => true, 'size' => 9]);
        $gauche->addText('RÉPUBLIQUE DU BÉNIN', ['bold' => true, 'size' => 9]);

        $droite = $enTete->addCell(3850);
        $droite->addText('01 BP 348 Cotonou', ['size' => 8], ['alignment' => Jc::END]);
        $droite->addText('Tél. +229 21 30 53 93', ['size' => 8], ['alignment' => Jc::END]);
        $droite->addText('www.enseignementsuperieur.gouv.bj', ['size' => 8], ['alignment' => Jc::END]);

        $section->addTextBreak(1);
        $section->addText(
            'Abomey-Calavi, le '.now()->locale('fr')->translatedFormat('d F Y'),
            [],
            ['alignment' => Jc::END]
        );
        $section->addTextBreak(1);
    }

    private function ajouterChampEncadre(Section $section, string $label, ?string $valeur): void
    {
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 100]);
        $table->addRow();
        $texteRun = $table->addCell(9350)->addTextRun();
        $texteRun->addText($label.' : ', ['bold' => true]);
        $texteRun->addText($valeur ?: '—');
    }

    private function ajouterParagraphes(Section $section, ?string $texte): void
    {
        $lignes = array_filter(array_map('trim', explode("\n", (string) $texte)));

        if ($lignes === []) {
            $section->addText('—');

            return;
        }

        foreach ($lignes as $ligne) {
            $section->addText($ligne);
        }
    }
}
