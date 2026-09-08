<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_disabled_account_cannot_login(): void
    {
        $user = User::factory()->inactif()->create(['password' => bcrypt('password123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password123']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_lockout_resets_on_successful_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password123']);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
