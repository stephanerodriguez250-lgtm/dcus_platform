<?php

namespace Tests\Feature;

use App\Models\ArchiveDossierPartage;
use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\ArchivePartage;
use App\Models\User;
use App\Notifications\DossierPartageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveDossierPartageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_share_a_folder_with_a_recipient(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $expediteur->id, 'nom' => 'Conventions']);
        $path = UploadedFile::fake()->create('rapport.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        ArchiveFichier::factory()->create(['user_id' => $expediteur->id, 'folder_id' => $dossier->id, 'chemin_fichier' => $path]);

        $response = $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$dossier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);

        $response->assertRedirect();

        $copie = ArchiveFolder::where('user_id', $destinataire->id)->where('nom', 'Conventions')->first();
        $this->assertNotNull($copie);
        $dossierCollecteur = ArchiveFolder::where('user_id', $destinataire->id)->where('nom', 'Partages reçus')->first();
        $this->assertNotNull($dossierCollecteur);
        $this->assertSame($dossierCollecteur->id, $copie->parent_id);
        $this->assertSame(1, $copie->fichiers()->count());

        $partage = ArchiveDossierPartage::first();
        $this->assertNotNull($partage);
        $this->assertSame($dossier->id, $partage->dossier_original_id);
        $this->assertSame($copie->id, $partage->dossier_copie_id);
        $this->assertNotNull($partage->zip_path);
        Storage::disk('public')->assertExists($partage->zip_path);

        Notification::assertSentTo($destinataire, DossierPartageNotification::class);
    }

    public function test_sharing_a_folder_recursively_copies_nested_subfolders_and_files(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $racine = ArchiveFolder::factory()->create(['user_id' => $expediteur->id, 'nom' => 'Racine']);
        $enfant = ArchiveFolder::factory()->create(['user_id' => $expediteur->id, 'parent_id' => $racine->id, 'nom' => 'Enfant']);
        $path = UploadedFile::fake()->create('doc.pdf', 20, 'application/pdf')->store('archives/fichiers', 'public');
        ArchiveFichier::factory()->create(['user_id' => $expediteur->id, 'folder_id' => $enfant->id, 'chemin_fichier' => $path]);

        $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$racine->id],
            'destinataire_ids' => [$destinataire->id],
        ]);

        $copieRacine = ArchiveFolder::where('user_id', $destinataire->id)->where('nom', 'Racine')->firstOrFail();
        $copieEnfant = ArchiveFolder::where('user_id', $destinataire->id)->where('parent_id', $copieRacine->id)->first();
        $this->assertNotNull($copieEnfant);
        $this->assertSame('Enfant', $copieEnfant->nom);
        $this->assertSame(1, $copieEnfant->fichiers()->count());
    }

    public function test_name_collision_inside_the_partages_recus_folder_is_auto_renamed(): void
    {
        Notification::fake();

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $expediteur->id, 'nom' => 'Conventions']);

        // Premier partage : crée "Partages reçus" > "Conventions"
        $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$dossier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);
        // Deuxième partage du même dossier : collision de nom dans le même dossier collecteur
        $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$dossier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);

        $dossierCollecteur = ArchiveFolder::where('user_id', $destinataire->id)->where('nom', 'Partages reçus')->firstOrFail();
        $this->assertSame(2, ArchiveFolder::where('user_id', $destinataire->id)->where('nom', 'like', 'Conventions%')->count());
        $this->assertDatabaseHas('archive_folders', [
            'user_id' => $destinataire->id, 'parent_id' => $dossierCollecteur->id, 'nom' => 'Conventions (2)',
        ]);
    }

    public function test_user_cannot_share_a_folder_they_do_not_own(): void
    {
        $expediteur = User::factory()->create();
        $autre = User::factory()->create();
        $destinataire = User::factory()->create();
        $dossierDeLAutre = ArchiveFolder::factory()->create(['user_id' => $autre->id]);

        $response = $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$dossierDeLAutre->id],
            'destinataire_ids' => [$destinataire->id],
        ]);

        $response->assertForbidden();
        $this->assertSame(0, ArchiveDossierPartage::count());
    }

    public function test_recipient_can_download_the_zip_archive(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $expediteur->id]);

        $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$dossier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);
        $partage = ArchiveDossierPartage::first();

        $response = $this->actingAs($destinataire)->get(route('archives.dossier-partages.telecharger', $partage));

        $response->assertOk();
    }

    public function test_a_user_other_than_the_recipient_cannot_download_the_zip(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $autre = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $expediteur->id]);

        $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$dossier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);
        $partage = ArchiveDossierPartage::first();

        $this->actingAs($expediteur)->get(route('archives.dossier-partages.telecharger', $partage))->assertForbidden();
        $this->actingAs($autre)->get(route('archives.dossier-partages.telecharger', $partage))->assertForbidden();
    }

    public function test_sender_can_revoke_a_folder_share_without_affecting_the_recipients_copy(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $expediteur->id]);

        $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$dossier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);
        $partage = ArchiveDossierPartage::first();
        $zipPath = $partage->zip_path;
        $copieId = $partage->dossier_copie_id;

        $response = $this->actingAs($expediteur)->delete(route('archives.dossier-partages.destroy', $partage));

        $response->assertRedirect();
        $this->assertDatabaseMissing('archive_dossier_partages', ['id' => $partage->id]);
        Storage::disk('public')->assertMissing($zipPath);
        $this->assertDatabaseHas('archive_folders', ['id' => $copieId]);
    }

    public function test_recipient_cannot_revoke_a_folder_share(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $expediteur->id]);

        $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$dossier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);
        $partage = ArchiveDossierPartage::first();

        $response = $this->actingAs($destinataire)->delete(route('archives.dossier-partages.destroy', $partage));

        $response->assertForbidden();
        $this->assertDatabaseHas('archive_dossier_partages', ['id' => $partage->id]);
    }

    public function test_an_optional_note_is_stored_on_the_folder_share_and_passed_to_the_notification(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $expediteur->id]);

        $this->actingAs($expediteur)->post('/archives/partages', [
            'dossier_ids' => [$dossier->id],
            'destinataire_ids' => [$destinataire->id],
            'note' => 'Voir la clause 4.',
        ]);

        $this->assertDatabaseHas('archive_dossier_partages', [
            'dossier_original_id' => $dossier->id,
            'note' => 'Voir la clause 4.',
        ]);

        Notification::assertSentTo($destinataire, function (DossierPartageNotification $notification) {
            return $notification->note === 'Voir la clause 4.';
        });
    }

    public function test_can_share_files_and_folders_together_in_one_request(): void
    {
        Notification::fake();
        Storage::fake('public');

        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $expediteur->id]);
        $path = UploadedFile::fake()->create('rapport.pdf', 50, 'application/pdf')->store('archives/fichiers', 'public');
        $fichier = ArchiveFichier::factory()->create(['user_id' => $expediteur->id, 'chemin_fichier' => $path]);

        $response = $this->actingAs($expediteur)->post('/archives/partages', [
            'fichier_ids' => [$fichier->id],
            'dossier_ids' => [$dossier->id],
            'destinataire_ids' => [$destinataire->id],
        ]);

        $response->assertRedirect();
        $this->assertSame(1, ArchivePartage::count());
        $this->assertSame(1, ArchiveDossierPartage::count());
    }
}
