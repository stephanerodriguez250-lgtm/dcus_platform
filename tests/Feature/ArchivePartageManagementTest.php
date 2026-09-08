<?php

namespace Tests\Feature;

use App\Models\ArchiveFichier;
use App\Models\ArchivePartage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivePartageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_sender_sees_their_sent_shares(): void
    {
        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $original = ArchiveFichier::factory()->create(['user_id' => $expediteur->id, 'intitule' => 'Convention UAC']);
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);
        ArchivePartage::factory()->create([
            'fichier_original_id' => $original->id,
            'fichier_copie_id' => $copie->id,
            'partage_par' => $expediteur->id,
            'destinataire_id' => $destinataire->id,
        ]);

        $response = $this->actingAs($expediteur)->get('/archives/partages');

        $response->assertOk();
        $response->assertSee('Convention UAC');
        $response->assertSee($destinataire->nom_complet);
    }

    public function test_sender_does_not_see_shares_made_by_other_users(): void
    {
        $expediteur = User::factory()->create();
        $autre = User::factory()->create();
        ArchivePartage::factory()->create(['partage_par' => $autre->id]);

        $response = $this->actingAs($expediteur)->get('/archives/partages');

        $response->assertOk();
        $this->assertCount(0, $response->viewData('partages'));
    }

    public function test_sender_can_revoke_a_share_without_affecting_the_recipients_copy(): void
    {
        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);
        $partage = ArchivePartage::factory()->create([
            'partage_par' => $expediteur->id,
            'destinataire_id' => $destinataire->id,
            'fichier_copie_id' => $copie->id,
        ]);

        $response = $this->actingAs($expediteur)->delete("/archives/partages/{$partage->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('archive_partages', ['id' => $partage->id]);
        $this->assertDatabaseHas('archive_fichiers', ['id' => $copie->id]);
    }

    public function test_recipient_cannot_revoke_a_share_they_received(): void
    {
        $destinataire = User::factory()->create();
        $partage = ArchivePartage::factory()->create(['destinataire_id' => $destinataire->id]);

        $response = $this->actingAs($destinataire)->delete("/archives/partages/{$partage->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('archive_partages', ['id' => $partage->id]);
    }

    public function test_unrelated_user_cannot_revoke_the_share(): void
    {
        $autre = User::factory()->create();
        $partage = ArchivePartage::factory()->create();

        $response = $this->actingAs($autre)->delete("/archives/partages/{$partage->id}");

        $response->assertForbidden();
    }
}
