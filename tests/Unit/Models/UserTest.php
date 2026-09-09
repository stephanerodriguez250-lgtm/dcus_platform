<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_actifs_sauf_returns_active_users_excluding_the_given_id(): void
    {
        $exclu = User::factory()->create(['actif' => true]);
        $actif = User::factory()->create(['actif' => true]);
        $inactif = User::factory()->create(['actif' => false]);

        $resultat = User::actifsSauf($exclu->id);

        $this->assertTrue($resultat->contains($actif));
        $this->assertFalse($resultat->contains($exclu));
        $this->assertFalse($resultat->contains($inactif));
    }
}
