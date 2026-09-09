<?php

namespace App\Services;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Throwable;

/**
 * Rôle 1 de l'agent IA : à partir des fiches d'appréciation déjà rédigées par la DCUS
 * (utilisées comme exemples de style/critères) et du document de l'accord à apprécier,
 * suggère un contenu pour les 4 champs de contenu du formulaire d'appréciation (origine,
 * objet, observations sur la forme, observations sur le fond). L'agent DCUS reste libre
 * de tout modifier avant d'enregistrer — cette suggestion n'est jamais enregistrée telle
 * quelle. Le numéro d'avis (identifiant de la fiche) et la Conclusion ne sont volontairement
 * pas suggérés : le premier est un identifiant que seul l'agent peut attribuer, la seconde
 * est entièrement générée à partir de l'intitulé de l'accord (voir
 * Accord::$conclusion_appreciation), sans intervention de l'IA.
 */
class AccordAppreciationSuggestionGenerator
{
    private const NOMBRE_EXEMPLES = 5;

    public function __construct(private readonly GeminiClient $gemini) {}

    public function generer(Accord $accord): array
    {
        $exemples = AccordAppreciation::latest()
            ->limit(self::NOMBRE_EXEMPLES)
            ->get();

        $prompt = $this->construirePrompt($accord, $exemples);

        return $this->gemini->genererJson($prompt, $this->fichierJoint($accord));
    }

    private function construirePrompt(Accord $accord, Collection $exemples): string
    {
        $exemplesTexte = $exemples->isEmpty()
            ? '(Aucun exemple précédent disponible.)'
            : $exemples->map(fn (AccordAppreciation $e) => implode("\n", [
                "- Origine : {$e->origine}",
                "  Objet : {$e->objet}",
                "  Observations sur la forme : {$e->observations_forme}",
                "  Observations sur le fond : {$e->observations_fond}",
            ]))->implode("\n\n");

        $texteDocument = $this->extraireTexteSiDocx($accord);

        return <<<PROMPT
            Tu es un juriste de la Direction de la Coopération Universitaire et Scientifique (DCUS)
            du Bénin. Ton rôle est d'analyser un projet d'accord-cadre reçu d'une université ou d'un
            partenaire et de rédiger une fiche d'appréciation, dans le même style que les exemples
            ci-dessous déjà rédigés par la DCUS.

            Exemples de fiches d'appréciation déjà rédigées par la DCUS :
            {$exemplesTexte}

            Informations sur l'accord à apprécier :
            - Intitulé : {$accord->titre}
            - Institution partenaire : {$accord->institution_partenaire}
            - Référence : {$accord->reference}
            {$texteDocument}

            Rédige une nouvelle fiche d'appréciation pour cet accord. Réponds strictement en JSON,
            avec exactement ces clés : "origine" (l'institution béninoise à l'origine de la demande),
            "objet" (une phrase décrivant l'objet de l'étude), "observations_forme" (observations
            sur la forme du document, une ligne par point commençant par "- ", et "  - " pour un
            sous-point), "observations_fond" (même format que observations_forme mais sur le fond).
            N'invente aucun article de loi béninois précis si tu n'en es pas certain — reste général
            dans ce cas.
            PROMPT;
    }

    private function extraireTexteSiDocx(Accord $accord): string
    {
        if (! $accord->chemin_fichier || ! str_ends_with(strtolower($accord->chemin_fichier), '.docx')) {
            return '';
        }

        if (! Storage::disk('public')->exists($accord->chemin_fichier)) {
            return '';
        }

        try {
            $phpWord = IOFactory::load(Storage::disk('public')->path($accord->chemin_fichier));
            $texte = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $texte .= $element->getText()."\n";
                    }
                }
            }

            return "\nContenu du document (extrait) :\n".mb_substr(trim($texte), 0, 8000);
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * @return array{chemin: string, mimeType: string}|null
     */
    private function fichierJoint(Accord $accord): ?array
    {
        if (! $accord->chemin_fichier || ! Storage::disk('public')->exists($accord->chemin_fichier)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($accord->chemin_fichier, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => null,
        };

        if ($mime === null) {
            return null;
        }

        return ['chemin' => Storage::disk('public')->path($accord->chemin_fichier), 'mimeType' => $mime];
    }
}
