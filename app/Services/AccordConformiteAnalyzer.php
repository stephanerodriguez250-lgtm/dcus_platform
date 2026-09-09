<?php

namespace App\Services;

use App\Models\Accord;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use RuntimeException;
use Throwable;

/**
 * Rôle 2 de l'agent IA : compare le document de l'accord SIGNÉ aux observations de la
 * fiche d'appréciation (forme/fond) pour déterminer quelles observations ont été prises
 * en compte, et produit un résumé exploité ensuite par RapportConformiteGenerator.
 */
class AccordConformiteAnalyzer
{
    public function __construct(private readonly GeminiClient $gemini) {}

    public function analyser(Accord $accord): array
    {
        $appreciation = $accord->appreciation;

        if (! $appreciation) {
            throw new RuntimeException("Cet accord n'a pas encore de fiche d'appréciation.");
        }

        if (! $accord->chemin_fichier_signe || ! Storage::disk('public')->exists($accord->chemin_fichier_signe)) {
            throw new RuntimeException("Aucun document signé n'a été chargé pour cet accord.");
        }

        $prompt = $this->construirePrompt($accord);

        return $this->gemini->genererJson($prompt, $this->fichierJoint($accord));
    }

    private function construirePrompt(Accord $accord): string
    {
        $appreciation = $accord->appreciation;
        $texteDocument = $this->extraireTexteSiDocx($accord);

        return <<<PROMPT
            Tu es un juriste de la Direction de la Coopération Universitaire et Scientifique (DCUS)
            du Bénin. La DCUS avait émis les observations suivantes sur le projet d'accord
            « {$accord->titre} » avant sa signature :

            Avis global émis : {$appreciation->avis}

            Observations sur la forme :
            {$appreciation->observations_forme}

            Observations sur le fond :
            {$appreciation->observations_fond}

            Voici maintenant le document SIGNÉ par les parties (joint en pièce jointe, ou extrait
            ci-dessous). Compare son contenu à chacune des observations ci-dessus.
            {$texteDocument}

            Réponds strictement en JSON avec exactement ces clés : "resume" (2 à 4 phrases résumant
            le constat global), "points_conformes" (les observations qui ont été prises en compte
            dans le document signé, une ligne par point commençant par "- "), "points_non_conformes"
            (les observations qui n'ont PAS été prises en compte, même format ; si tout est conforme,
            renvoie une chaîne vide). N'invente aucun article de loi béninois précis si tu n'en es
            pas certain — reste général dans ce cas.
            PROMPT;
    }

    private function extraireTexteSiDocx(Accord $accord): string
    {
        if (! str_ends_with(strtolower($accord->chemin_fichier_signe), '.docx')) {
            return '';
        }

        try {
            $phpWord = IOFactory::load(Storage::disk('public')->path($accord->chemin_fichier_signe));
            $texte = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $texte .= $element->getText()."\n";
                    }
                }
            }

            return "\nContenu du document signé (extrait) :\n".mb_substr(trim($texte), 0, 8000);
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * @return array{chemin: string, mimeType: string}|null
     */
    private function fichierJoint(Accord $accord): ?array
    {
        $mime = match (strtolower(pathinfo($accord->chemin_fichier_signe, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => null,
        };

        if ($mime === null) {
            return null;
        }

        return ['chemin' => Storage::disk('public')->path($accord->chemin_fichier_signe), 'mimeType' => $mime];
    }
}
