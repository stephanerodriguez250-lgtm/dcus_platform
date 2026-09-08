<?php

namespace Tests\Feature;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/archives');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_empty_state_at_root(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/archives');

        $response->assertOk();
        $response->assertSee('Archives');
    }

    public function test_root_lists_only_the_current_users_root_folders_and_files(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();

        $monDossier = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'MonDossier']);
        $monFichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'intitule' => 'MonFichier']);
        ArchiveFolder::factory()->create(['user_id' => $autre->id, 'nom' => 'DossierDeLAutre']);

        $response = $this->actingAs($user)->get('/archives');

        $response->assertOk();
        $response->assertSee('MonDossier');
        $response->assertSee('MonFichier');
        $response->assertDontSee('DossierDeLAutre');
    }

    public function test_browsing_a_subfolder_shows_its_breadcrumb_and_contents(): void
    {
        $user = User::factory()->create();
        $racine = ArchiveFolder::factory()->create(['user_id' => $user->id, 'nom' => 'Racine']);
        $enfant = ArchiveFolder::factory()->create([
            'user_id' => $user->id, 'parent_id' => $racine->id, 'nom' => 'Enfant',
        ]);
        ArchiveFichier::factory()->create([
            'user_id' => $user->id, 'folder_id' => $enfant->id, 'intitule' => 'FichierEnfant',
        ]);

        $response = $this->actingAs($user)->get("/archives/{$enfant->id}");

        $response->assertOk();
        $response->assertSee('Racine');
        $response->assertSee('Enfant');
        $response->assertSee('FichierEnfant');
    }

    public function test_a_user_cannot_browse_another_users_folder(): void
    {
        $owner = User::factory()->create();
        $autre = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($autre)->get("/archives/{$dossier->id}");

        $response->assertForbidden();
    }
}
