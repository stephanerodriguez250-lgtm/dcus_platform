<?php

namespace App\Services;

use App\Models\AccordRapportConformite;
use App\Services\Concerns\RendDesParagraphesAvecPuces;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Génère le rapport de conformité au format .docx (rôle 2 de l'agent IA — vérification que
 * l'accord signé prend en compte les observations de la fiche d'appréciation).
 *
 * NOTE : en attendant les exemples de rapport que l'utilisateur doit fournir, le document
 * est construit directement avec PhpWord (mise en page simple) — même approche provisoire
 * que FicheAppreciationGenerator avant réception du vrai template DCUS.
 */
class RapportConformiteGenerator
{
    use RendDesParagraphesAvecPuces;

    public function generer(AccordRapportConformite $rapport): string
    {
        $accord = $rapport->accord;

        $phpWord = new PhpWord;
        $section = $phpWord->addSection();

        $section->addText('RAPPORT DE CONFORMITÉ', ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER]);
        $section->addText(
            'Direction de la Coopération Universitaire et Scientifique',
            ['italic' => true, 'size' => 10],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(1);

        $section->addText('Accord : '.$accord->titre, ['bold' => true]);
        $section->addText('Référence : '.$accord->reference);
        if ($accord->date_signature) {
            $section->addText('Date de signature : '.$accord->date_signature->format('d/m/Y'));
        }
        $section->addTextBreak(1);

        $section->addText('Résumé', ['bold' => true, 'size' => 12]);
        $section->addText($rapport->resume ?: '—');
        $section->addTextBreak(1);

        $section->addText('Observations prises en compte', ['bold' => true, 'size' => 12]);
        $this->ajouterParagraphes($section, $rapport->points_conformes);
        $section->addTextBreak(1);

        $section->addText('Observations non prises en compte', ['bold' => true, 'size' => 12]);
        $this->ajouterParagraphes($section, $rapport->points_non_conformes);

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
