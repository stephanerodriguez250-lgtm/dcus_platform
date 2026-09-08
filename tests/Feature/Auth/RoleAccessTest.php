<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/utilisateurs');

        $response->assertOk();
    }

    public function test_agent_cannot_access_user_management(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->get('/utilisateurs');

        $response->assertForbidden();
    }

    public function test_secretaire_cannot_access_user_management(): void
    {
        $secretaire = User::factory()->secretaire()->create();

        $response = $this->actingAs($secretaire)->get('/utilisateurs');

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_from_user_management(): void
    {
        $response = $this->get('/utilisateurs');

        $response->assertRedirect('/login');
    }
}
