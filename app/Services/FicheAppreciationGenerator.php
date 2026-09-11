<?php

namespace App\Services;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Services\Concerns\AjoutePagination;
use App\Services\Concerns\ConstruitDocumentDcus;
use App\Services\Concerns\RendDesParagraphesAvecPuces;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Génère la fiche d'appréciation au format .docx, reproduisant la mise en forme
 * officielle des fiches DCUS : en-tête ministériel (logo flottant + coordonnées alignées
 * à droite sur la même ligne, sans tableau ni tabulation — voir
 * App\Services\Concerns\ConstruitDocumentDcus, partagé avec RapportConformiteGenerator pour
 * que les deux documents suivent exactement le même gabarit), puis un unique tableau bordé
 * regroupant Origine/Objet/Référence/Destinataire, Observations sur la forme/le fond et
 * Conclusion — chaque champ sur sa propre ligne. Police Trebuchet MS dans tout le document
 * (12pt pour le texte courant, 14pt pour le titre "FICHE D'APPRÉCIATION...", seul élément à
 * cette taille), demande explicite de la DCUS.
 *
 * Le numéro d'avis ("Avis N°.../MESRS/DCUS/{année}") vient de AccordAppreciation::$avis,
 * saisi par l'agent (l'identifiant unique de la fiche, pas la conclusion — voir plus bas) ;
 * la Conclusion, elle, n'est plus saisie du tout : elle est entièrement générée à partir de
 * Accord::$conclusion_appreciation (formule fixe imposée par la DCUS, seul l'intitulé de
 * l'accord variant d'une fiche à l'autre).
 */
class FicheAppreciationGenerator
{
    use AjoutePagination, ConstruitDocumentDcus, RendDesParagraphesAvecPuces;

    public function generer(AccordAppreciation $appreciation): string
    {
        $accord = $appreciation->accord;

        $phpWord = $this->nouveauDocumentOfficiel();
        $section = $this->nouvelleSectionOfficielle($phpWord);

        $this->ajouterPagination($section);
        $this->ajouterEnTete($section);

        $section->addText(
            'Avis N° '.$appreciation->avis.' /MESRS/DCUS/'.now()->format('Y'),
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
            ['bold' => true, 'size' => 14],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(1);

        $reference = $accord->reference.' du '.$accord->date_arrivee->locale('fr')->translatedFormat('d F Y');

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 100]);
        $this->ajouterLigneChamp($table, 'Origine', $appreciation->origine);
        $this->ajouterLigneChamp($table, 'Objet', $appreciation->objet);
        $this->ajouterLigneChamp($table, 'Référence', $reference);
        $this->ajouterLigneChamp($table, 'Destinataire', $appreciation->origine);
        $this->ajouterLigneObservations(
            $table,
            '1°) Observations sur la forme',
            $appreciation->observations_forme,
            "Dans le cadre de l'objet suscité, la DCUS a procédé à une analyse minutieuse du projet et y a relevé les éléments d'appréciation ci-après :"
        );
        $this->ajouterLigneObservations($table, '2°) Observations sur le fond', $appreciation->observations_fond);
        $this->ajouterLigneConclusion($table, $accord);

        $section->addTextBreak(2);
        $section->addText('Rédigé par : '.$appreciation->redacteur->nom_complet, ['size' => 9, 'italic' => true]);
        $section->addText('Le : '.$appreciation->created_at->locale('fr')->translatedFormat('d M Y à H:i'), ['size' => 9, 'italic' => true]);

        Storage::disk('public')->makeDirectory('accords/appreciations');
        $chemin = 'accords/appreciations/'.Str::random(40).'.docx';

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save(Storage::disk('public')->path($chemin));

        return $chemin;
    }

    private function ajouterLigneConclusion(Table $table, Accord $accord): void
    {
        $table->addRow();
        $texteRun = $table->addCell(self::LARGEUR_CONTENU)->addTextRun();
        $texteRun->addText('Conclusion : ', ['bold' => true]);
        $texteRun->addText($accord->conclusion_appreciation);
    }
}
