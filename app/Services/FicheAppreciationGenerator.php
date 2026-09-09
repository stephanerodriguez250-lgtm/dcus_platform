<?php

namespace App\Services;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Services\Concerns\RendDesParagraphesAvecPuces;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Génère la fiche d'appréciation au format .docx, reproduisant la mise en forme
 * officielle des fiches DCUS : en-tête ministériel (logo flottant + coordonnées alignées
 * à droite sur la même ligne, sans tableau ni tabulation), puis un unique tableau bordé
 * regroupant Origine/Objet/Référence/Destinataire, Observations sur la forme/le fond et
 * Conclusion — chaque champ sur sa propre ligne.
 *
 * Le numéro d'avis ("Avis N°.../MESRS/DCUS/{année}") vient de AccordAppreciation::$avis,
 * saisi par l'agent (l'identifiant unique de la fiche, pas la conclusion — voir plus bas) ;
 * la Conclusion, elle, n'est plus saisie du tout : elle est entièrement générée à partir de
 * Accord::$conclusion_appreciation (formule fixe imposée par la DCUS, seul l'intitulé de
 * l'accord variant d'une fiche à l'autre). L'en-tête utilise directement
 * resources/images/logo-mesrs.png (le logo officiel
 * MESRS — armoiries + "MINISTÈRE DE L'ENSEIGNEMENT SUPÉRIEUR ET DE LA RECHERCHE
 * SCIENTIFIQUE" + "RÉPUBLIQUE DU BÉNIN" déjà composés dans l'image, fourni par
 * l'utilisateur après deux tentatives précédentes avec des fichiers mal étiquetés pour
 * un autre ministère) en image flottante ancrée en haut à gauche de la marge, avec les
 * coordonnées en simples paragraphes alignés à droite (Jc::END, sans tabulation) — le
 * flottement fait sortir le logo du flux normal du texte, si bien que les coordonnées
 * démarrent à la même hauteur que le logo au lieu de s'empiler dessous. Une version
 * précédente utilisait une image en ligne (non flottante) par prudence, mais un contrôle
 * visuel direct (rendu du .docx en PDF puis en image) a montré que le logo et les
 * coordonnées ne partageaient alors pas la même ligne d'en-tête. Une version encore plus
 * ancienne faisait flotter du texte à côté d'une image via une tabulation à position fixe,
 * ce qui débordait de la page et tronquait le texte à l'impression ; l'alignement à droite
 * simple (sans tabulation) utilisé ici s'adapte toujours à la marge réelle et ne peut donc
 * pas déborder de la même façon.
 */
class FicheAppreciationGenerator
{
    use RendDesParagraphesAvecPuces;

    private const LARGEUR_CONTENU = 10000;

    public function generer(AccordAppreciation $appreciation): string
    {
        $accord = $appreciation->accord;

        $phpWord = new PhpWord;
        // Marges réduites : l'en-tête doit démarrer près du haut de la page, et la
        // tabulation droite de l'en-tête (LARGEUR_CONTENU) doit rester à l'intérieur de la
        // largeur utile (page ~11905 twips - marges) pour ne pas déborder/tronquer le texte.
        $section = $phpWord->addSection(['marginTop' => 600, 'marginLeft' => 900, 'marginRight' => 900, 'marginBottom' => 900]);

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
            ['bold' => true, 'size' => 12],
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

        $section->addText('01 BP 348 Cotonou', ['size' => 8], ['alignment' => Jc::END]);
        $section->addText('Tél. +229 21 30 53 93', ['size' => 8], ['alignment' => Jc::END]);
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

    private function ajouterLigneConclusion(Table $table, Accord $accord): void
    {
        $table->addRow();
        $texteRun = $table->addCell(self::LARGEUR_CONTENU)->addTextRun();
        $texteRun->addText('Conclusion : ', ['bold' => true]);
        $texteRun->addText($accord->conclusion_appreciation);
    }
}
