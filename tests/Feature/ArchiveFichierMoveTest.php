<?php

namespace Tests\Feature;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveFichierMoveTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_move_a_fichier_into_another_folder(): void
    {
        $user = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id]);
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'folder_id' => null]);

        $response = $this->actingAs($user)->patch(route('archives.fichiers.deplacer', $fichier), [
            'dossier_id' => $dossier->getRouteKey(),
        ]);

        $response->assertRedirect();
        $this->assertSame($dossier->id, $fichier->fresh()->folder_id);
    }

    public function test_owner_can_move_a_fichier_back_to_the_root(): void
    {
        $user = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id]);
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'folder_id' => $dossier->id]);

        $response = $this->actingAs($user)->patch(route('archives.fichiers.deplacer', $fichier), [
            'dossier_id' => null,
        ]);

        $response->assertRedirect();
        $this->assertNull($fichier->fresh()->folder_id);
    }

    public function test_another_user_cannot_move_a_fichier_they_do_not_own(): void
    {
        $owner = User::factory()->create();
        $autre = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $autre->id]);
        $fichier = ArchiveFichier::factory()->create(['user_id' => $owner->id, 'folder_id' => null]);

        $response = $this->actingAs($autre)->patch(route('archives.fichiers.deplacer', $fichier), [
            'dossier_id' => $dossier->getRouteKey(),
        ]);

        $response->assertForbidden();
        $this->assertNull($fichier->fresh()->folder_id);
    }

    public function test_cannot_move_a_fichier_into_a_folder_owned_by_another_user(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();
        $dossierDeLAutre = ArchiveFolder::factory()->create(['user_id' => $autre->id]);
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'folder_id' => null]);

        $response = $this->actingAs($user)->patch(route('archives.fichiers.deplacer', $fichier), [
            'dossier_id' => $dossierDeLAutre->getRouteKey(),
        ]);

        $response->assertForbidden();
        $this->assertNull($fichier->fresh()->folder_id);
    }
}
