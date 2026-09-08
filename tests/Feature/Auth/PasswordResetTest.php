<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_request_shows_generic_message_for_existing_email(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/mot-de-passe-oublie', ['email' => $user->email]);

        $response->assertSessionHas('status');
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_reset_link_request_shows_same_generic_message_for_unknown_email(): void
    {
        $response = $this->post('/mot-de-passe-oublie', ['email' => 'inconnu@example.com']);

        $response->assertSessionHas('status');
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_user_can_reset_password_with_valid_token_and_is_logged_in(): void
    {
        NotificationFacade::fake();

        $user = User::factory()->create(['password' => bcrypt('old-password')]);

        $this->post('/mot-de-passe-oublie', ['email' => $user->email]);

        $token = null;
        NotificationFacade::assertSentTo($user, function (ResetPasswordNotification $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $response = $this->post('/reinitialiser-mot-de-passe', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
    }

    public function test_disabled_account_is_not_auto_logged_in_after_reset(): void
    {
        NotificationFacade::fake();

        $user = User::factory()->inactif()->create();

        $this->post('/mot-de-passe-oublie', ['email' => $user->email]);

        $token = Password::createToken($user);

        $response = $this->post('/reinitialiser-mot-de-passe', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/reinitialiser-mot-de-passe', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
