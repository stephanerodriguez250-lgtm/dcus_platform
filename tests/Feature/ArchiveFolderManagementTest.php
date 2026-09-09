<?php

namespace Tests\Feature;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveFolderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_root_folder(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/archives/dossiers', ['nom' => 'Contrats']);

        $response->assertRedirect();
        $this->assertDatabaseHas('archive_folders', [
            'user_id' => $user->id, 'parent_id' => null, 'nom' => 'Contrats',
        ]);
    }

    public function test_user_can_create_a_nested_subfolder(): void
    {
        $user = User::factory()->create();
        $parent = ArchiveFolder::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/archives/dossiers', [
            'nom' => 'Sous-dossier', 'parent_id' => $parent->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('archive_folders', [
            'user_id' => $user->id, 'parent_id' => $parent->id, 'nom' => 'Sous-dossier',
        ]);
    }

    public function test_duplicate_folder_name_at_the_same_level_is_rejected(): void
    {
        $user = User::factory()->create();
        ArchiveFolder::factory()->create(['user_id' => $user->id, 'parent_id' => null, 'nom' => 'Contrats']);

        $response = $this->actingAs($user)->post('/archives/dossiers', ['nom' => 'Contrats']);

        $response->assertSessionHasErrors('nom');
        $this->assertSame(1, ArchiveFolder::where('nom', 'Contrats')->count());
    }

    public function test_same_folder_name_is_allowed_in_a_different_folder(): void
    {
        $user = User::factory()->create();
        $dossierA = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'A']);
        $dossierB = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'B']);
        ArchiveFolder::factory()->create(['user_id' => $user->id, 'parent_id' => $dossierA->id, 'nom' => 'Commun']);

        $response = $this->actingAs($user)->post('/archives/dossiers', [
            'nom' => 'Commun', 'parent_id' => $dossierB->id,
        ]);

        $response->assertRedirect();
        $this->assertSame(2, ArchiveFolder::where('nom', 'Commun')->count());
    }

    public function test_user_can_rename_their_folder(): void
    {
        $user = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'Ancien nom']);

        $response = $this->actingAs($user)->patch(route('archives.dossiers.update', $dossier), ['nom' => 'Nouveau nom']);

        $response->assertRedirect();
        $this->assertSame('Nouveau nom', $dossier->fresh()->nom);
    }

    public function test_user_cannot_rename_another_users_folder(): void
    {
        $owner = User::factory()->create();
        $autre = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $owner->id, 'nom' => 'Ancien nom']);

        $response = $this->actingAs($autre)->patch(route('archives.dossiers.update', $dossier), ['nom' => 'Piraté']);

        $response->assertForbidden();
        $this->assertSame('Ancien nom', $dossier->fresh()->nom);
    }

    public function test_deleting_a_folder_cascades_to_children_and_removes_files_from_disk(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $racine = ArchiveFolder::factory()->create(['user_id' => $user->id]);
        $enfant = ArchiveFolder::factory()->create(['user_id' => $user->id, 'parent_id' => $racine->id]);

        $file = UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf');
        $path = $file->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create([
            'user_id' => $user->id, 'folder_id' => $enfant->id, 'chemin_fichier' => $path,
        ]);

        $response = $this->actingAs($user)->delete(route('archives.dossiers.destroy', $racine));

        $response->assertRedirect();
        $this->assertDatabaseMissing('archive_folders', ['id' => $racine->id]);
        $this->assertDatabaseMissing('archive_folders', ['id' => $enfant->id]);
        $this->assertDatabaseMissing('archive_fichiers', ['id' => $fichier->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_user_cannot_delete_another_users_folder(): void
    {
        $owner = User::factory()->create();
        $autre = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($autre)->delete(route('archives.dossiers.destroy', $dossier));

        $response->assertForbidden();
        $this->assertDatabaseHas('archive_folders', ['id' => $dossier->id]);
    }
}
