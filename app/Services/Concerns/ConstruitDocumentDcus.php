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
     * En-tête ministériel : une unique image officielle fournie par l'utilisateur (armoiries +
     * nom du ministère + coordonnées, déjà composés dans le fichier lui-même), insérée en
     * ligne sur toute la largeur utile de la page — remplace l'ancienne composition par logo
     * flottant + paragraphes de coordonnées alignés à droite, devenue inutile puisque plus
     * rien ne doit être aligné à côté de l'image.
     */
    private function ajouterEnTete(Section $section): void
    {
        $cheminEntete = resource_path('images/entete-mesrs.png');
        if (is_file($cheminEntete)) {
            $section->addImage($cheminEntete, [
                // Même largeur que le tableau (LARGEUR_CONTENU = 10000 twips = 500pt), pour
                // rester confortablement à l'intérieur de la largeur utile de la page et garder
                // une cohérence visuelle avec le reste du document. Hauteur dérivée du ratio
                // réel du fichier (1092x159px) pour ne pas le déformer.
                'width' => 500,
                'height' => 72.8,
                'alignment' => Jc::CENTER,
            ]);
        }

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
