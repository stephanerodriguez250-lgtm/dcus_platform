<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\UserInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_invite_a_new_user_by_email(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/utilisateurs', [
            'email' => 'invite@dcus.bj',
            'role' => 'agent',
        ]);

        $response->assertRedirect(route('utilisateurs.index'));
        $this->assertDatabaseHas('user_invitations', [
            'email' => 'invite@dcus.bj',
            'role' => 'agent',
        ]);
        Notification::assertSentOnDemand(UserInvitationNotification::class);
    }

    public function test_agent_cannot_invite_a_user(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->post('/utilisateurs', [
            'email' => 'invite@dcus.bj',
            'role' => 'agent',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('user_invitations', ['email' => 'invite@dcus.bj']);
    }

    public function test_cannot_invite_an_email_that_already_has_an_account(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = User::factory()->create();

        $response = $this->actingAs($admin)->post('/utilisateurs', [
            'email' => $existing->email,
            'role' => 'agent',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_page_shows_for_valid_token(): void
    {
        $token = 'plain-text-token';
        $invitation = UserInvitation::factory()->create([
            'email' => 'invite@dcus.bj',
            'token' => hash('sha256', $token),
        ]);

        $response = $this->get("/inscription/{$token}");

        $response->assertOk();
        $response->assertSee($invitation->email);
    }

    public function test_registration_page_rejects_invalid_token(): void
    {
        $response = $this->get('/inscription/not-a-real-token');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_registration_page_rejects_expired_token(): void
    {
        $token = 'plain-text-token';
        UserInvitation::factory()->expired()->create([
            'token' => hash('sha256', $token),
        ]);

        $response = $this->get("/inscription/{$token}");

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_complete_registration_and_is_logged_in(): void
    {
        $token = 'plain-text-token';
        $invitation = UserInvitation::factory()->create([
            'email' => 'invite@dcus.bj',
            'role' => 'secretaire',
            'token' => hash('sha256', $token),
        ]);

        $response = $this->post('/inscription', [
            'token' => $token,
            'nom' => 'Dossou',
            'prenom' => 'Marc',
            'service' => 'SSCP',
            'password' => 'NouveauPass2026!',
            'password_confirmation' => 'NouveauPass2026!',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'invite@dcus.bj')->first();
        $this->assertNotNull($user);
        $this->assertSame('secretaire', $user->role);
        $this->assertSame('SSCP', $user->service);
        $this->assertTrue(Hash::check('NouveauPass2026!', $user->password));
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseMissing('user_invitations', ['id' => $invitation->id]);
    }

    public function test_registration_requires_a_valid_service(): void
    {
        $token = 'plain-text-token';
        UserInvitation::factory()->create([
            'email' => 'invite@dcus.bj',
            'token' => hash('sha256', $token),
        ]);

        $response = $this->post('/inscription', [
            'token' => $token,
            'nom' => 'Dossou',
            'prenom' => 'Marc',
            'service' => 'INVALID',
            'password' => 'NouveauPass2026!',
            'password_confirmation' => 'NouveauPass2026!',
        ]);

        $response->assertSessionHasErrors('service');
        $this->assertDatabaseMissing('users', ['email' => 'invite@dcus.bj']);
    }

    public function test_admin_can_revoke_a_pending_invitation(): void
    {
        $admin = User::factory()->admin()->create();
        $invitation = UserInvitation::factory()->create();

        $response = $this->actingAs($admin)->delete(route('utilisateurs.invitations.revoquer', $invitation));

        $response->assertRedirect();
        $this->assertDatabaseMissing('user_invitations', ['id' => $invitation->id]);
    }

    public function test_admin_can_resend_a_pending_invitation(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $invitation = UserInvitation::factory()->create(['email' => 'invite@dcus.bj']);
        $originalToken = $invitation->token;

        $response = $this->actingAs($admin)->post(route('utilisateurs.invitations.renvoyer', $invitation));

        $response->assertRedirect();
        $this->assertNotSame($originalToken, $invitation->fresh()->token);
        Notification::assertSentOnDemand(UserInvitationNotification::class);
    }
}
