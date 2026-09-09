<?php

namespace Tests\Feature;

use App\Models\Accord;
use App\Models\User;
use App\Services\GeminiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AccordAppreciationSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_secretaire_can_request_an_ai_suggestion(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();

        $this->mock(GeminiClient::class, function ($mock) {
            $mock->shouldReceive('genererJson')->once()->andReturn([
                'origine' => 'UAC', 'objet' => 'Objet', 'avis' => 'Avis',
                'observations_forme' => '- RAS', 'observations_fond' => '- RAS',
            ]);
        });

        $response = $this->actingAs($secretaire)->postJson("/accords/{$accord->id}/apprecier/suggestion");

        $response->assertOk();
        $response->assertJson(['origine' => 'UAC']);
    }

    public function test_agent_without_authorization_cannot_request_a_suggestion(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $accord = Accord::factory()->create();

        $response = $this->actingAs($agent)->postJson("/accords/{$accord->id}/apprecier/suggestion");

        $response->assertForbidden();
    }

    public function test_a_gemini_failure_returns_a_readable_error_without_crashing(): void
    {
        $secretaire = User::factory()->secretaire()->create();
        $accord = Accord::factory()->create();

        $this->mock(GeminiClient::class, function ($mock) {
            $mock->shouldReceive('genererJson')->once()->andThrow(new RuntimeException('Quota dépassé'));
        });

        $response = $this->actingAs($secretaire)->postJson("/accords/{$accord->id}/apprecier/suggestion");

        $response->assertStatus(422);
        $response->assertJson(['error' => 'La génération IA a échoué : Quota dépassé']);
    }
}
