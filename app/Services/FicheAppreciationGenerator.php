<?php

namespace App\Services;

use App\Models\AccordAppreciation;
use App\Services\Concerns\RendDesParagraphesAvecPuces;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Tab;

/**
 * Génère la fiche d'appréciation au format .docx, reproduisant la mise en forme
 * officielle des fiches DCUS : en-tête ministériel (armoiries + texte, sans tableau),
 * puis un unique tableau bordé regroupant Origine/Objet/Référence/Destinataire,
 * Observations sur la forme/le fond et Conclusion — chaque champ sur sa propre ligne.
 *
 * Le numéro d'avis ("Avis N°.../MESRS/DCUS/{année}") est laissé vide sur le document
 * généré : comme sur les fiches papier, il est attribué et complété à la main par la
 * DCUS. Les armoiries du Bénin (resources/images/armoiries-benin.png) sont intégrées
 * dans l'en-tête ; seul le blason est utilisé (fourni par l'utilisateur), le texte du
 * ministère reste codé en dur car le fichier reçu portait le nom d'un autre ministère.
 */
class FicheAppreciationGenerator
{
    use RendDesParagraphesAvecPuces;

    private const LARGEUR_CONTENU = 9350;

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

        $section->addText(
            "Dans le cadre de l'objet suscité, la DCUS a procédé à une analyse minutieuse du projet et y a relevé les éléments d'appréciation ci-après :"
        );
        $section->addTextBreak(1);

        $reference = $accord->reference.' du '.$accord->date_arrivee->locale('fr')->translatedFormat('d F Y');

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 100]);
        $this->ajouterLigneChamp($table, 'Origine', $appreciation->origine);
        $this->ajouterLigneChamp($table, 'Objet', $appreciation->objet);
        $this->ajouterLigneChamp($table, 'Référence', $reference);
        $this->ajouterLigneChamp($table, 'Destinataire', $appreciation->origine);
        $this->ajouterLigneObservations($table, '1°) Observations sur la forme', $appreciation->observations_forme);
        $this->ajouterLigneObservations($table, '2°) Observations sur le fond', $appreciation->observations_fond);
        $this->ajouterLigneConclusion($table, $appreciation->avis);

        $section->addTextBreak(2);
        $section->addText('Rédigé par : '.$appreciation->redacteur->nom_complet, ['size' => 9, 'italic' => true]);
        $section->addText('Le : '.$appreciation->created_at->locale('fr')->translatedFormat('d M Y à H:i'), ['size' => 9, 'italic' => true]);

        Storage::disk('public')->makeDirectory('accords/appreciations');
        $chemin = 'accords/appreciations/'.Str::random(40).'.docx';

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save(Storage::disk('public')->path($chemin));

        return $chemin;
    }

    /**
     * En-tête ministériel sans tableau : les armoiries flottent à gauche (habillage
     * "square"), le nom du ministère et les coordonnées à droite s'alignent sur une
     * tabulation droite positionnée au bord du contenu — le texte du ministère occupe
     * la zone laissée libre par l'image, comme dans un traitement de texte classique.
     */
    private function ajouterEnTete(Section $section): void
    {
        $cheminLogo = resource_path('images/armoiries-benin.png');
        if (is_file($cheminLogo)) {
            $section->addImage($cheminLogo, [
                'width' => 60,
                'height' => 60,
                'wrappingStyle' => 'square',
                'positioning' => 'relative',
                'posHorizontal' => 'left',
                'posHorizontalRel' => 'margin',
                'posVertical' => 'top',
                'posVerticalRel' => 'margin',
            ]);
        }

        $styleTabulation = ['tabs' => [new Tab('right', self::LARGEUR_CONTENU)]];

        $lignes = [
            ['MINISTÈRE', '01 BP 348 Cotonou'],
            ["DE L'ENSEIGNEMENT SUPÉRIEUR", 'Tél. +229 21 30 53 93'],
            ['ET DE LA RECHERCHE SCIENTIFIQUE', 'www.enseignementsuperieur.gouv.bj'],
            ['RÉPUBLIQUE DU BÉNIN', ''],
        ];

        foreach ($lignes as [$gauche, $droite]) {
            $texteRun = $section->addTextRun($styleTabulation);
            $texteRun->addText($gauche, ['bold' => true, 'size' => 9]);
            $texteRun->addText("\t".$droite, ['size' => 8]);
        }

        $section->addTextBreak(1);
        $section->addText(
            'Abomey-Calavi, le '.now()->locale('fr')->translatedFormat('d F Y'),
            [],
            ['alignment' => Jc::END]
        );
        $section->addTextBreak(1);
    }

    private function ajouterLigneChamp(Table $table, string $label, ?string $valeur): void
    {
        $table->addRow();
        $texteRun = $table->addCell(self::LARGEUR_CONTENU)->addTextRun();
        $texteRun->addText($label.' : ', ['bold' => true]);
        $texteRun->addText($valeur ?: '—');
    }

    private function ajouterLigneObservations(Table $table, string $titre, ?string $texte): void
    {
        $table->addRow();
        $cellule = $table->addCell(self::LARGEUR_CONTENU);
        $cellule->addText($titre, ['bold' => true]);
        $this->ajouterParagraphes($cellule, $texte);
    }

    private function ajouterLigneConclusion(Table $table, ?string $avis): void
    {
        $table->addRow();
        $texteRun = $table->addCell(self::LARGEUR_CONTENU)->addTextRun();
        $texteRun->addText('Conclusion : ', ['bold' => true]);
        $texteRun->addText($avis ?: '—');
    }
}
