<?php

namespace Tests\Feature;

use App\Models\ArchiveFichier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveFichierPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_preview_their_own_fichier_inline(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'chemin_fichier' => $path]);

        $response = $this->actingAs($user)->get(route('archives.fichiers.apercu', $fichier));

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
    }

    public function test_another_user_cannot_preview_the_fichier(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $autre = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $owner->id, 'chemin_fichier' => $path]);

        $response = $this->actingAs($autre)->get(route('archives.fichiers.apercu', $fichier));

        $response->assertForbidden();
    }

    public function test_archives_index_shows_a_preview_link_for_each_file(): void
    {
        $user = User::factory()->create();
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/archives');

        $response->assertOk();
        $response->assertSee(route('archives.fichiers.apercu', $fichier), false);
    }

    public function test_pdf_preview_serves_an_explicit_safe_content_type_with_nosniff(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'chemin_fichier' => $path, 'type_fichier' => 'pdf']);

        $response = $this->actingAs($user)->get(route('archives.fichiers.apercu', $fichier));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', $response->headers->get('content-type'));
        $response->assertHeader('x-content-type-options', 'nosniff');
    }

    public function test_image_preview_serves_an_explicit_safe_content_type(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = UploadedFile::fake()->image('photo.jpg')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'chemin_fichier' => $path, 'type_fichier' => 'jpg']);

        $response = $this->actingAs($user)->get(route('archives.fichiers.apercu', $fichier));

        $response->assertOk();
        $this->assertStringStartsWith('image/jpeg', $response->headers->get('content-type'));
        $response->assertHeader('x-content-type-options', 'nosniff');
    }

    public function test_non_previewable_file_type_falls_back_to_a_forced_download_instead_of_inline(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.docx', 50)->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'chemin_fichier' => $path, 'type_fichier' => 'docx']);

        $response = $this->actingAs($user)->get(route('archives.fichiers.apercu', $fichier));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }
}
