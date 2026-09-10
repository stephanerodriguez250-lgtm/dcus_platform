<?php

namespace App\Services\Concerns;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Ajoute un numéro de page centré ("Page X sur Y") en pied de page, via les champs Word
 * natifs PAGE/NUMPAGES (calculés par Word à l'ouverture, pas des valeurs figées au moment
 * de la génération) — demande explicite de la DCUS pour que tous les documents générés
 * (fiche d'appréciation, rapport de conformité) soient paginés automatiquement.
 */
trait AjoutePagination
{
    private function ajouterPagination(Section $section): void
    {
        $piedDePage = $section->addFooter();
        $piedDePage->addPreserveText('Page {PAGE} sur {NUMPAGES}', ['size' => 8], ['alignment' => Jc::CENTER]);
    }
}
