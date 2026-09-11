<?php

namespace App\Services\Concerns;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Mise en forme commune aux documents .docx officiels DCUS (fiche d'appréciation et rapport
 * de conformité) — même en-tête ministériel, même police (Trebuchet MS, taille 12 pour le
 * texte courant, 14 réservé aux titres de document par convention de l'appelant) et même
 * convention de tableau bordé à une colonne, une ligne par champ. Extrait de
 * FicheAppreciationGenerator pour que RapportConformiteGenerator reproduise exactement le
 * même gabarit visuel plutôt que d'avoir sa propre mise en page bricolée — demande explicite
 * de la DCUS ("le rapport final doit garder le même template que la fiche d'appréciation").
 * Suppose que la classe utilisatrice utilise aussi RendDesParagraphesAvecPuces (pour
 * ajouterLigneObservations) — déjà le cas des deux générateurs.
 */
trait ConstruitDocumentDcus
{
    private const LARGEUR_CONTENU = 10000;

    private function nouveauDocumentOfficiel(): PhpWord
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Trebuchet MS');
        $phpWord->setDefaultFontSize(12);

        return $phpWord;
    }

    /**
     * Marges réduites : l'en-tête doit démarrer près du haut de la page, et la largeur du
     * tableau (LARGEUR_CONTENU) doit rester à l'intérieur de la largeur utile (page ~11905
     * twips - marges) pour ne pas déborder/tronquer le texte.
     */
    private function nouvelleSectionOfficielle(PhpWord $phpWord): Section
    {
        return $phpWord->addSection(['marginTop' => 600, 'marginLeft' => 900, 'marginRight' => 900, 'marginBottom' => 900]);
    }

    /**
     * En-tête ministériel sans tableau ni tabulation : le logo flotte, ancré en haut à
     * gauche de la marge (positionnement relatif, pas une tabulation absolue), pendant que
     * les coordonnées restent de simples paragraphes alignés à droite (Jc::END) — leur
     * position s'adapte donc toujours à la largeur utile réelle et ne peut pas déborder,
     * contrairement à l'ancienne technique par tabulation qui plaçait le texte à une
     * position fixe en twips (source du débordement observé précédemment). Le logo flottant
     * sort du flux normal du texte : les deux blocs démarrent ainsi à la même hauteur, sur
     * la même "ligne" d'en-tête, sans être composés dans une seule et même ligne de texte.
     */
    private function ajouterEnTete(Section $section): void
    {
        $cheminLogo = resource_path('images/logo-mesrs.png');
        if (is_file($cheminLogo)) {
            $section->addImage($cheminLogo, [
                'width' => 262,
                'height' => 60,
                'wrappingStyle' => 'square',
                'positioning' => 'relative',
                'posHorizontalRel' => 'margin',
                'posHorizontal' => 'left',
                'posVerticalRel' => 'margin',
                'posVertical' => 'top',
            ]);
        }

        $section->addText('Cité Ministérielle-Bâtiment F', ['size' => 8], ['alignment' => Jc::END]);
        $section->addText('Qtier Ahouanlêko, 12ème Arrondissement', ['size' => 8], ['alignment' => Jc::END]);
        $section->addText('Adresse postale : 01 BP 348 Cotonou', ['size' => 8], ['alignment' => Jc::END]);
        $section->addText('Téléphone : +229 01 21 32 88 63', ['size' => 8], ['alignment' => Jc::END]);
        $section->addText('contact.mesrs@gouv.bj', ['size' => 8], ['alignment' => Jc::END]);
        $section->addText('www.enseignementsuperieur.gouv.bj', ['size' => 8], ['alignment' => Jc::END]);
        $section->addTextBreak(1);
        $section->addText(
            'Abomey-Calavi, le '.now()->locale('fr')->translatedFormat('d F Y'),
            [],
            ['alignment' => Jc::END]
        );
        $section->addTextBreak(2);
    }

    private function ajouterLigneChamp(Table $table, string $label, ?string $valeur): void
    {
        $table->addRow();
        $texteRun = $table->addCell(self::LARGEUR_CONTENU)->addTextRun();
        $texteRun->addText($label.' : ', ['bold' => true]);
        $texteRun->addText($valeur ?: '—');
    }

    private function ajouterLigneObservations(Table $table, string $titre, ?string $texte, ?string $introduction = null): void
    {
        $table->addRow();
        $cellule = $table->addCell(self::LARGEUR_CONTENU);
        if ($introduction) {
            $cellule->addText($introduction);
            $cellule->addTextBreak(1);
        }
        $cellule->addText($titre, ['bold' => true]);
        $this->ajouterParagraphes($cellule, $texte);
    }
}
