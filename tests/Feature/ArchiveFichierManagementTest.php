<?php

namespace Tests\Feature;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveFichierManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_renders_with_intitule_numero_description_and_file_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/archives/fichiers/create');

        $response->assertOk();
        $response->assertSee('name="intitule"', false);
        $response->assertSee('name="numero"', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('name="fichier"', false);
        $response->assertDontSee('name="date"', false);
        $response->assertDontSee('name="heure"', false);
    }

    public function test_user_can_upload_a_file_with_intitule_numero_and_description(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/archives/fichiers', [
            'dossier_id' => $dossier->id,
            'intitule' => 'Convention de partenariat UAC',
            'numero' => 'DCUS-2026-014',
            'description' => 'Convention signée avec l\'UAC en 2026.',
            'fichier' => UploadedFile::fake()->create('convention.pdf', 200, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $fichier = ArchiveFichier::where('intitule', 'Convention de partenariat UAC')->first();
        $this->assertNotNull($fichier);
        $this->assertSame($user->id, $fichier->user_id);
        $this->assertSame($dossier->id, $fichier->folder_id);
        $this->assertSame('DCUS-2026-014', $fichier->numero);
        $this->assertNotNull($fichier->created_at);
        Storage::disk('public')->assertExists($fichier->chemin_fichier);
    }

    public function test_upload_without_a_folder_lands_at_the_root(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/archives/fichiers', [
            'intitule' => 'Note diverse',
            'fichier' => UploadedFile::fake()->create('note.pdf', 50, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('archive_fichiers', ['intitule' => 'Note diverse', 'folder_id' => null]);
    }

    public function test_upload_requires_intitule(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/archives/fichiers', [
            'fichier' => UploadedFile::fake()->create('note.pdf', 50, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('intitule');
    }

    public function test_upload_rejects_disallowed_file_types(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/archives/fichiers', [
            'intitule' => 'Fichier interdit',
            'fichier' => UploadedFile::fake()->create('script.exe', 50),
        ]);

        $response->assertSessionHasErrors('fichier');
    }

    public function test_user_can_update_their_fichiers_metadata(): void
    {
        $user = User::factory()->create();
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'intitule' => 'Ancien']);

        $response = $this->actingAs($user)->patch(route('archives.fichiers.update', $fichier), [
            'intitule' => 'Nouveau', 'numero' => 'X-1', 'description' => 'Maj',
        ]);

        $response->assertRedirect();
        $this->assertSame('Nouveau', $fichier->fresh()->intitule);
    }

    public function test_user_can_download_their_own_fichier(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'chemin_fichier' => $path, 'nom_fichier' => 'rapport.pdf']);

        $response = $this->actingAs($user)->get(route('archives.fichiers.download', $fichier));

        $response->assertOk();
    }

    public function test_user_can_delete_their_own_fichier_and_it_is_removed_from_disk(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'chemin_fichier' => $path]);

        $response = $this->actingAs($user)->delete(route('archives.fichiers.destroy', $fichier));

        $response->assertRedirect();
        $this->assertDatabaseMissing('archive_fichiers', ['id' => $fichier->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_a_user_cannot_view_update_delete_or_download_another_users_fichier(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $autre = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $owner->id, 'chemin_fichier' => $path]);

        $this->actingAs($autre)->patch(route('archives.fichiers.update', $fichier), ['intitule' => 'Piraté'])->assertForbidden();
        $this->actingAs($autre)->delete(route('archives.fichiers.destroy', $fichier))->assertForbidden();
        $this->actingAs($autre)->get(route('archives.fichiers.download', $fichier))->assertForbidden();
    }
}
