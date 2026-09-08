<?php

namespace Tests\Feature;

use App\Models\ArchiveFichier;
use App\Models\ArchivePartage;
use App\Models\User;
use App\Notifications\FichierPartageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchivePartageStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_archives_index_shows_a_checkbox_and_share_button_for_each_file(): void
    {
        $user = User::factory()->create();
        ArchiveFichier::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/archives');

        $response->assertOk();
        $response->assertSee('name="fichier_ids[]"', false);
        $response->assertSee('id="partagerModal"', false);
    }

    public function test_user_can_share_a_file_with_one_recipient(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create([
            'user_id' => $expediteur->id, 'chemin_fichier' => $path, 'intitule' => 'Convention UAC',
        ]);

        $response = $this->actingAs($expediteur)->post('/archives/partages', [
            'fichier_ids' => [$fichier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);

        $response->assertRedirect();
        $copie = ArchiveFichier::where('user_id', $destinataire->id)->where('intitule', 'Convention UAC')->first();
        $this->assertNotNull($copie);
        $this->assertNull($copie->folder_id);
        $this->assertNotSame($fichier->chemin_fichier, $copie->chemin_fichier);
        Storage::disk('public')->assertExists($copie->chemin_fichier);

        $this->assertDatabaseHas('archive_partages', [
            'fichier_original_id' => $fichier->id,
            'fichier_copie_id' => $copie->id,
            'partage_par' => $expediteur->id,
            'destinataire_id' => $destinataire->id,
        ]);

        Notification::assertSentTo($destinataire, FichierPartageNotification::class);
    }

    public function test_user_can_share_multiple_files_with_multiple_recipients_in_one_request(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataireA = User::factory()->create();
        $destinataireB = User::factory()->create();
        $path1 = UploadedFile::fake()->create('un.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        $path2 = UploadedFile::fake()->create('deux.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier1 = ArchiveFichier::factory()->create(['user_id' => $expediteur->id, 'chemin_fichier' => $path1]);
        $fichier2 = ArchiveFichier::factory()->create(['user_id' => $expediteur->id, 'chemin_fichier' => $path2]);

        $response = $this->actingAs($expediteur)->post('/archives/partages', [
            'fichier_ids' => [$fichier1->id, $fichier2->id],
            'destinataire_ids' => [$destinataireA->id, $destinataireB->id],
        ]);

        $response->assertRedirect();
        $this->assertSame(4, ArchivePartage::count());
        $this->assertSame(2, ArchiveFichier::where('user_id', $destinataireA->id)->count());
        $this->assertSame(2, ArchiveFichier::where('user_id', $destinataireB->id)->count());
    }

    public function test_sharing_requires_at_least_one_file(): void
    {
        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();

        $response = $this->actingAs($expediteur)->post('/archives/partages', [
            'destinataire_ids' => [$destinataire->id],
        ]);

        $response->assertSessionHasErrors('fichier_ids');
    }

    public function test_sharing_requires_at_least_one_recipient(): void
    {
        $expediteur = User::factory()->create();
        $fichier = ArchiveFichier::factory()->create(['user_id' => $expediteur->id]);

        $response = $this->actingAs($expediteur)->post('/archives/partages', [
            'fichier_ids' => [$fichier->id],
        ]);

        $response->assertSessionHasErrors('destinataire_ids');
    }

    public function test_user_cannot_share_a_file_they_do_not_own(): void
    {
        $expediteur = User::factory()->create();
        $autre = User::factory()->create();
        $destinataire = User::factory()->create();
        $fichierDeLAutre = ArchiveFichier::factory()->create(['user_id' => $autre->id]);

        $response = $this->actingAs($expediteur)->post('/archives/partages', [
            'fichier_ids' => [$fichierDeLAutre->id],
            'destinataire_ids' => [$destinataire->id],
        ]);

        $response->assertForbidden();
        $this->assertSame(0, ArchivePartage::count());
    }

    public function test_user_cannot_select_themselves_as_a_recipient(): void
    {
        $expediteur = User::factory()->create();
        $fichier = ArchiveFichier::factory()->create(['user_id' => $expediteur->id]);

        $response = $this->actingAs($expediteur)->post('/archives/partages', [
            'fichier_ids' => [$fichier->id],
            'destinataire_ids' => [$expediteur->id],
        ]);

        $response->assertSessionHasErrors('destinataire_ids.0');
    }

    public function test_shared_copy_shows_provenance_on_the_recipients_archives_page(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create(['nom' => 'Dossou', 'prenom' => 'Marc']);
        $destinataire = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create([
            'user_id' => $expediteur->id, 'chemin_fichier' => $path, 'intitule' => 'Convention UAC',
        ]);

        $this->actingAs($expediteur)->post('/archives/partages', [
            'fichier_ids' => [$fichier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);

        $response = $this->actingAs($destinataire)->get('/archives');

        $response->assertOk();
        $response->assertSee('Convention UAC');
        $response->assertSee('Partagé par Marc Dossou');
    }

    public function test_deleting_the_original_file_does_not_delete_the_recipients_copy(): void
    {
        Storage::fake('public');
        Notification::fake();

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $expediteur->id, 'chemin_fichier' => $path]);

        $this->actingAs($expediteur)->post('/archives/partages', [
            'fichier_ids' => [$fichier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);
        $copie = ArchiveFichier::where('user_id', $destinataire->id)->firstOrFail();

        $this->actingAs($expediteur)->delete("/archives/fichiers/{$fichier->id}");

        $this->assertDatabaseMissing('archive_fichiers', ['id' => $fichier->id]);
        $this->assertDatabaseHas('archive_fichiers', ['id' => $copie->id]);
    }

    public function test_deleting_the_copy_does_not_delete_the_original_file(): void
    {
        Storage::fake('public');
        Notification::fake();

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $path = UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $expediteur->id, 'chemin_fichier' => $path]);

        $this->actingAs($expediteur)->post('/archives/partages', [
            'fichier_ids' => [$fichier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);
        $copie = ArchiveFichier::where('user_id', $destinataire->id)->firstOrFail();

        $this->actingAs($destinataire)->delete("/archives/fichiers/{$copie->id}");

        $this->assertDatabaseMissing('archive_fichiers', ['id' => $copie->id]);
        $this->assertDatabaseHas('archive_fichiers', ['id' => $fichier->id]);
    }
}
