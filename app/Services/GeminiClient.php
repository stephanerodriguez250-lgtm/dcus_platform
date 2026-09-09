<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Client HTTP minimal pour l'API Gemini (palier gratuit — voir GEMINI_API_KEY dans .env).
 * Force le modèle à répondre en JSON strict (response_mime_type) pour éviter un parsing
 * fragile côté appelant.
 */
class GeminiClient
{
    private string $apiKey;

    private string $model;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? (string) config('services.gemini.key');
        $this->model = $model ?? (string) config('services.gemini.model');
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

        $parts = [['text' => $prompt]];

        if ($fichier !== null) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $fichier['mimeType'],
                    'data' => base64_encode(file_get_contents($fichier['chemin'])),
                ],
            ];
        }

        $reponse = Http::timeout(60)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
            [
                'contents' => [['parts' => $parts]],
                'generationConfig' => ['response_mime_type' => 'application/json'],
            ]
        );

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
}
