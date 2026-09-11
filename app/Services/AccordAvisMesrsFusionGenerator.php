<?php

namespace App\Services;

use App\Models\Accord;
use Illuminate\Support\Facades\Storage;

/**
 * Rôle 3 de l'agent IA : lit la fiche d'appréciation SCANNÉE du ministère (MESRS) et la
 * fusionne avec les observations déjà rédigées par la DCUS (Accord::appreciation). Le
 * résultat remplace en place observations_forme/observations_fond de la fiche DCUS existante
 * (pas de fiche séparée) — décision explicite de la DCUS. Le prompt exige explicitement de ne
 * perdre aucun point distinct des deux sources tout en évitant les doublons entre elles, car
 * cette fusion doit être fiable sans relecture exhaustive ligne à ligne par l'agent (qui ne
 * fait que réviser/corriger le résultat avant de valider, voir AccordAvisMesrsController).
 */
class AccordAvisMesrsFusionGenerator
{
    public function __construct(private readonly GeminiClient $gemini) {}

    public function fusionner(Accord $accord, string $cheminFichierMinistere): array
    {
        $prompt = $this->construirePrompt($accord, $cheminFichierMinistere);

        return $this->gemini->genererJson($prompt, $this->fichierJoint($cheminFichierMinistere));
    }

    private function construirePrompt(Accord $accord, string $cheminFichierMinistere): string
    {
        $appreciation = $accord->appreciation;

        return <<<PROMPT
            Tu es un juriste de la Direction de la Coopération Universitaire et Scientifique (DCUS)
            du Bénin. La DCUS a déjà rédigé les observations suivantes sur le projet d'accord
            « {$accord->titre} » :

            Observations sur la forme (DCUS) :
            {$appreciation->observations_forme}

            Observations sur le fond (DCUS) :
            {$appreciation->observations_fond}

            Le ministère de tutelle (MESRS) a produit sa propre fiche d'appréciation sur ce même
            projet d'accord, jointe ci-dessous en pièce scannée. Lis attentivement ses observations
            sur la forme et sur le fond.

            Produis une version FUSIONNÉE des observations, destinée à remplacer celles de la DCUS
            ci-dessus dans la fiche existante :
            - Conserve INTÉGRALEMENT et sans aucune modification chaque point déjà rédigé par la
              DCUS ci-dessus.
            - Ajoute ensuite chaque point soulevé par le ministère qui n'apparaît pas déjà parmi les
              observations de la DCUS.
            - N'ajoute JAMAIS un point du ministère qui répète (même reformulé) un point déjà
              présent côté DCUS — dans ce cas, ne garde que la version DCUS, une seule fois.
            - N'omets AUCUN point distinct provenant de l'une ou l'autre des deux fiches : chaque
              observation réellement différente doit apparaître dans le résultat.

            Réponds strictement en JSON avec exactement ces clés : "observations_forme" et
            "observations_fond", chacune au format à puces (une ligne par point commençant par
            "- ", et "  - " pour un sous-point).
            PROMPT;
    }

    /**
     * @return array{chemin: string, mimeType: string}
     */
    private function fichierJoint(string $cheminFichierMinistere): array
    {
        $mime = match (strtolower(pathinfo($cheminFichierMinistere, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/pdf',
        };

        return ['chemin' => Storage::disk('public')->path($cheminFichierMinistere), 'mimeType' => $mime];
    }
}
