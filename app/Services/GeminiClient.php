<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Client HTTP minimal pour l'API Gemini (palier gratuit — voir GEMINI_API_KEY dans .env).
 * Force le modèle à répondre en JSON strict (response_mime_type) pour éviter un parsing
 * fragile côté appelant.
 */
class GeminiClient
{
    private const TENTATIVES_MAX = 3;

    private string $apiKey;

    private string $model;

    private int $delaiEntreTentativesMs;

    public function __construct(?string $apiKey = null, ?string $model = null, int $delaiEntreTentativesMs = 1000)
    {
        $this->apiKey = $apiKey ?? (string) config('services.gemini.key');
        $this->model = $model ?? (string) config('services.gemini.model');
        $this->delaiEntreTentativesMs = $delaiEntreTentativesMs;
    }

    /**
     * @param  array{chemin: string, mimeType: string}|null  $fichier  Pièce jointe optionnelle
     *                                                                 (PDF ou image) envoyée en base64.
     */
    public function genererJson(string $prompt, ?array $fichier = null): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Aucune clé API Gemini configurée (GEMINI_API_KEY).');
        }

        // Le délai HTTP ci-dessous (Http::timeout(60), jusqu'à self::TENTATIVES_MAX fois) doit
        // toujours pouvoir expirer AVANT la limite globale de PHP (max_execution_time, 30s par
        // défaut) : sinon PHP tue le script en pleine requête cURL par une erreur fatale non
        // interceptable (pas un Throwable normal), au lieu de laisser Guzzle lever une exception
        // propre que notre appelant peut attraper et afficher proprement. Un appel Gemini avec
        // pièce jointe (analyse d'un PDF) peut légitimement dépasser 30s, et une nouvelle
        // tentative après un 503 peut cumuler plusieurs de ces appels.
        set_time_limit(200);

        $parts = [['text' => $prompt]];

        if ($fichier !== null) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $fichier['mimeType'],
                    'data' => base64_encode(file_get_contents($fichier['chemin'])),
                ],
            ];
        }

        $reponse = $this->appellerAvecNouvellesTentatives($parts);

        if ($reponse->failed()) {
            throw new RuntimeException('Échec de l\'appel à Gemini : '.$reponse->body());
        }

        $texte = data_get($reponse->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($texte)) {
            throw new RuntimeException('Réponse Gemini inattendue (pas de contenu texte).');
        }

        $donnees = json_decode($texte, true);

        if (! is_array($donnees)) {
            throw new RuntimeException('Réponse Gemini non conforme au format JSON attendu.');
        }

        return $donnees;
    }

    /**
     * Le palier gratuit de Gemini renvoie parfois une erreur 503 ("modèle actuellement en forte
     * demande") purement transitoire — on retente automatiquement quelques fois avant
     * d'abandonner, plutôt que de faire échouer la génération dès la première surcharge.
     * Toute autre erreur (clé invalide, quota, 4xx...) n'est jamais retentée.
     */
    private function appellerAvecNouvellesTentatives(array $parts): Response
    {
        $tentative = 1;

        while (true) {
            $reponse = Http::timeout(60)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [['parts' => $parts]],
                    'generationConfig' => ['response_mime_type' => 'application/json'],
                ]
            );

            if ($reponse->successful() || $reponse->status() !== 503 || $tentative >= self::TENTATIVES_MAX) {
                return $reponse;
            }

            usleep($this->delaiEntreTentativesMs * 1000);
            $tentative++;
        }
    }
}
