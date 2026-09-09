<?php

namespace Tests\Feature;

use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveDossierMoveTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_move_a_dossier_into_another_dossier(): void
    {
        $user = User::factory()->create();
        $cible = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'Cible']);
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'Deplace']);

        $response = $this->actingAs($user)->patch("/archives/dossiers/{$dossier->id}/deplacer", [
            'parent_id' => $cible->id,
        ]);

        $response->assertRedirect();
        $this->assertSame($cible->id, $dossier->fresh()->parent_id);
    }

    public function test_owner_can_move_a_dossier_back_to_the_root(): void
    {
        $user = User::factory()->create();
        $parent = ArchiveFolder::factory()->create(['user_id' => $user->id]);
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id, 'parent_id' => $parent->id]);

        $response = $this->actingAs($user)->patch("/archives/dossiers/{$dossier->id}/deplacer", [
            'parent_id' => null,
        ]);

        $response->assertRedirect();
        $this->assertNull($dossier->fresh()->parent_id);
    }

    public function test_cannot_move_a_dossier_into_itself(): void
    {
        $user = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patch("/archives/dossiers/{$dossier->id}/deplacer", [
            'parent_id' => $dossier->id,
        ]);

        $response->assertRedirect();
        $this->assertNull($dossier->fresh()->parent_id);
    }

    public function test_cannot_move_a_dossier_into_its_own_descendant(): void
    {
        $user = User::factory()->create();
        $racine = ArchiveFolder::factory()->create(['user_id' => $user->id]);
        $enfant = ArchiveFolder::factory()->create(['user_id' => $user->id, 'parent_id' => $racine->id]);

        $response = $this->actingAs($user)->patch("/archives/dossiers/{$racine->id}/deplacer", [
            'parent_id' => $enfant->id,
        ]);

        $response->assertRedirect();
        $this->assertNull($racine->fresh()->parent_id);
    }

    public function test_another_user_cannot_move_a_dossier_they_do_not_own(): void
    {
        $owner = User::factory()->create();
        $autre = User::factory()->create();
        $cible = ArchiveFolder::factory()->create(['user_id' => $autre->id]);
        $dossier = ArchiveFolder::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($autre)->patch("/archives/dossiers/{$dossier->id}/deplacer", [
            'parent_id' => $cible->id,
        ]);

        $response->assertForbidden();
        $this->assertNull($dossier->fresh()->parent_id);
    }

    public function test_cannot_move_a_dossier_into_a_folder_owned_by_another_user(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();
        $dossierDeLAutre = ArchiveFolder::factory()->create(['user_id' => $autre->id]);
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patch("/archives/dossiers/{$dossier->id}/deplacer", [
            'parent_id' => $dossierDeLAutre->id,
        ]);

        $response->assertForbidden();
        $this->assertNull($dossier->fresh()->parent_id);
    }

    public function test_moving_a_dossier_into_a_folder_with_a_name_collision_does_not_move_it(): void
    {
        $user = User::factory()->create();
        $cible = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'Destination']);
        ArchiveFolder::factory()->create(['user_id' => $user->id, 'parent_id' => $cible->id, 'nom' => 'Conflit']);
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'Conflit']);

        $response = $this->actingAs($user)->patch("/archives/dossiers/{$dossier->id}/deplacer", [
            'parent_id' => $cible->id,
        ]);

        $response->assertRedirect();
        $this->assertNull($dossier->fresh()->parent_id);
    }
}
