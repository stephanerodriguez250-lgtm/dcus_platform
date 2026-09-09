<?php

namespace Tests\Unit\Services;

use App\Services\GeminiClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GeminiClientTest extends TestCase
{
    public function test_genererjson_envoie_le_prompt_et_decode_la_reponse(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '{"origine":"UAC","objet":"Test"}']]]],
                ],
            ], 200),
        ]);

        $client = new GeminiClient('fake-key', 'gemini-2.0-flash');
        $resultat = $client->genererJson('Analyse cet accord.');

        $this->assertSame(['origine' => 'UAC', 'objet' => 'Test'], $resultat);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'gemini-2.0-flash:generateContent')
                && str_contains($request->url(), 'key=fake-key')
                && $request['contents'][0]['parts'][0]['text'] === 'Analyse cet accord.'
                && $request['generationConfig']['response_mime_type'] === 'application/json';
        });
    }

    public function test_genererjson_joint_le_fichier_en_base64(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '{}']]]]],
            ], 200),
        ]);

        $chemin = tempnam(sys_get_temp_dir(), 'gemini_test_');
        file_put_contents($chemin, 'contenu-du-fichier');

        $client = new GeminiClient('fake-key', 'gemini-2.0-flash');
        $client->genererJson('Analyse.', ['chemin' => $chemin, 'mimeType' => 'application/pdf']);

        unlink($chemin);

        Http::assertSent(function ($request) {
            $partie = $request['contents'][0]['parts'][1]['inline_data'] ?? null;

            return $partie
                && $partie['mime_type'] === 'application/pdf'
                && base64_decode($partie['data']) === 'contenu-du-fichier';
        });
    }

    public function test_genererjson_leve_une_exception_si_la_cle_est_absente(): void
    {
        $client = new GeminiClient('', 'gemini-2.0-flash');

        $this->expectException(RuntimeException::class);

        $client->genererJson('Analyse.');
    }

    public function test_genererjson_leve_une_exception_si_la_reponse_nest_pas_du_json_valide(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'ceci nest pas du json']]]]],
            ], 200),
        ]);

        $client = new GeminiClient('fake-key', 'gemini-2.0-flash');

        $this->expectException(RuntimeException::class);

        $client->genererJson('Analyse.');
    }

    public function test_genererjson_leve_une_exception_si_lappel_echoue(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'quota dépassé'], 429),
        ]);

        $client = new GeminiClient('fake-key', 'gemini-2.0-flash');

        $this->expectException(RuntimeException::class);

        $client->genererJson('Analyse.');
    }
}
