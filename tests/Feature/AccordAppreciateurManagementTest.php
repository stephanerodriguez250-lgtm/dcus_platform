<?php

namespace Tests\Feature;

use App\Models\AccordAppreciateur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccordAppreciateurManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_authorize_an_agent_to_apprecier(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($admin)->post('/accords/appreciateurs', ['user_id' => $agent->id]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accord_appreciateurs', ['user_id' => $agent->id, 'accorde_par' => $admin->id]);
    }

    public function test_admin_can_revoke_an_authorization(): void
    {
        $admin = User::factory()->admin()->create();
        $appreciateur = AccordAppreciateur::factory()->create();

        $response = $this->actingAs($admin)->delete(route('accords.appreciateurs.destroy', $appreciateur));

        $response->assertRedirect();
        $this->assertDatabaseMissing('accord_appreciateurs', ['id' => $appreciateur->id]);
    }

    public function test_non_admin_cannot_manage_appreciateurs(): void
    {
        $secretaire = User::factory()->secretaire()->create();

        $response = $this->actingAs($secretaire)->get('/accords/appreciateurs');

        $response->assertForbidden();
    }

    public function test_cannot_authorize_the_same_agent_twice(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        AccordAppreciateur::factory()->create(['user_id' => $agent->id]);

        $response = $this->actingAs($admin)->post('/accords/appreciateurs', ['user_id' => $agent->id]);

        $response->assertSessionHasErrors('user_id');
    }
}
