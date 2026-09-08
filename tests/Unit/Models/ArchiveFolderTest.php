<?php

namespace Tests\Unit\Models;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveFolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_folder_can_have_nested_children(): void
    {
        $user = User::factory()->create();
        $racine = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'Racine']);
        $enfant = ArchiveFolder::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $racine->id,
            'nom' => 'Enfant',
        ]);

        $this->assertTrue($racine->children->contains($enfant));
        $this->assertTrue($enfant->parent->is($racine));
    }

    public function test_fil_ariane_returns_path_from_root_to_self(): void
    {
        $user = User::factory()->create();
        $racine = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'Racine']);
        $enfant = ArchiveFolder::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $racine->id,
            'nom' => 'Enfant',
        ]);
        $petitEnfant = ArchiveFolder::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $enfant->id,
            'nom' => 'PetitEnfant',
        ]);

        $chemin = $petitEnfant->filAriane();

        $this->assertSame(['Racine', 'Enfant', 'PetitEnfant'], array_map(fn ($d) => $d->nom, $chemin));
    }

    public function test_fichiers_recursifs_collects_files_from_all_descendants(): void
    {
        $user = User::factory()->create();
        $racine = ArchiveFolder::factory()->create(['user_id' => $user->id]);
        $enfant = ArchiveFolder::factory()->create(['user_id' => $user->id, 'parent_id' => $racine->id]);

        $fichierRacine = ArchiveFichier::factory()->create(['user_id' => $user->id, 'folder_id' => $racine->id]);
        $fichierEnfant = ArchiveFichier::factory()->create(['user_id' => $user->id, 'folder_id' => $enfant->id]);

        $fichiers = $racine->fichiersRecursifs();

        $this->assertCount(2, $fichiers);
        $this->assertTrue($fichiers->contains($fichierRacine));
        $this->assertTrue($fichiers->contains($fichierEnfant));
    }
}
