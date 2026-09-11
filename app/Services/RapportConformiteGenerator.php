<?php

namespace App\Services;

use App\Models\AccordRapportConformite;
use App\Services\Concerns\AjoutePagination;
use App\Services\Concerns\ConstruitDocumentDcus;
use App\Services\Concerns\RendDesParagraphesAvecPuces;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Génère le rapport de conformité au format .docx (rôle 2 de l'agent IA — vérification que
 * l'accord signé prend en compte les observations de la fiche d'appréciation).
 *
 * Reproduit exactement le même gabarit que FicheAppreciationGenerator — même en-tête
 * ministériel, même police (Trebuchet MS, 12pt/14pt pour le titre), même tableau bordé à une
 * ligne par champ — via le trait partagé App\Services\Concerns\ConstruitDocumentDcus, demande
 * explicite de la DCUS ("le rapport final doit garder le même template que la fiche
 * d'appréciation"). NOTE : en attendant les exemples de rapport que l'utilisateur doit
 * fournir, les champs du tableau (Accord/Référence/Résumé/Observations) restent une
 * approximation raisonnable du contenu attendu.
 */
class RapportConformiteGenerator
{
    use AjoutePagination, ConstruitDocumentDcus, RendDesParagraphesAvecPuces;

    public function generer(AccordRapportConformite $rapport): string
    {
        $accord = $rapport->accord;

        $phpWord = $this->nouveauDocumentOfficiel();
        $section = $this->nouvelleSectionOfficielle($phpWord);

        $this->ajouterPagination($section);
        $this->ajouterEnTete($section);

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
            strtoupper("RAPPORT DE CONFORMITÉ DU PROJET D'".$accord->titre),
            ['bold' => true, 'size' => 14],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(1);

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 100]);
        $this->ajouterLigneChamp($table, 'Accord', $accord->titre);
        $this->ajouterLigneChamp($table, 'Référence', $accord->reference);
        $this->ajouterLigneChamp($table, 'Date de signature', $accord->date_signature?->format('d/m/Y'));
        $this->ajouterLigneObservations($table, 'Résumé', $rapport->resume);
        $this->ajouterLigneObservations($table, 'Observations prises en compte', $rapport->points_conformes);
        $this->ajouterLigneObservations($table, 'Observations non prises en compte', $rapport->points_non_conformes);

        $section->addTextBreak(2);
        $section->addText('Rédigé par : '.$rapport->redacteur->nom_complet, ['size' => 9, 'italic' => true]);
        $section->addText('Le : '.$rapport->genere_le->locale('fr')->translatedFormat('d M Y à H:i'), ['size' => 9, 'italic' => true]);

        Storage::disk('public')->makeDirectory('accords/rapports-conformite');
        $chemin = 'accords/rapports-conformite/'.Str::random(40).'.docx';

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save(Storage::disk('public')->path($chemin));

        return $chemin;
    }
}
