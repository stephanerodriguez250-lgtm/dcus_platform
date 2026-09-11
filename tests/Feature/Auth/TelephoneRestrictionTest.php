<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelephoneRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_a_users_telephone_with_digits_only(): void
    {
        $admin = User::factory()->admin()->create();
        $utilisateur = User::factory()->create();

        $response = $this->actingAs($admin)->put(route('utilisateurs.update', $utilisateur), [
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email,
            'role' => $utilisateur->role,
            'telephone' => '0121328863',
        ]);

        $response->assertRedirect(route('utilisateurs.index'));
        $this->assertSame('0121328863', $utilisateur->refresh()->telephone);
    }

    public function test_admin_cannot_set_a_users_telephone_with_non_digit_characters(): void
    {
        $admin = User::factory()->admin()->create();
        $utilisateur = User::factory()->create(['telephone' => '0121328863']);

        $response = $this->actingAs($admin)->put(route('utilisateurs.update', $utilisateur), [
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email,
            'role' => $utilisateur->role,
            'telephone' => '+229 01 21 32 88 63',
        ]);

        $response->assertSessionHasErrors('telephone');
        $this->assertSame('0121328863', $utilisateur->refresh()->telephone);
    }

    public function test_user_can_set_their_own_telephone_with_digits_only(): void
    {
        $utilisateur = User::factory()->create();

        $response = $this->actingAs($utilisateur)->put(route('profile.update'), [
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'telephone' => '0121328863',
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors('telephone');
        $this->assertSame('0121328863', $utilisateur->refresh()->telephone);
    }

    public function test_user_cannot_set_their_own_telephone_with_non_digit_characters(): void
    {
        $utilisateur = User::factory()->create(['telephone' => '0121328863']);

        $response = $this->actingAs($utilisateur)->put(route('profile.update'), [
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'telephone' => 'abc-123',
        ]);

        $response->assertSessionHasErrors('telephone');
        $this->assertSame('0121328863', $utilisateur->refresh()->telephone);
    }
}
