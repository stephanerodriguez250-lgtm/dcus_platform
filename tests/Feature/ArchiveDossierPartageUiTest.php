<?php

namespace Tests\Feature;

use App\Models\ArchiveDossierPartage;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveDossierPartageUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_archives_index_shows_a_checkbox_for_each_folder(): void
    {
        $user = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/archives');

        $response->assertOk();
        $response->assertSee('name="dossier_ids[]"', false);
        $response->assertSee('value="'.$dossier->id.'"', false);
    }

    public function test_sent_shares_page_lists_shared_folders(): void
    {
        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $original = ArchiveFolder::factory()->create(['user_id' => $expediteur->id, 'nom' => 'Conventions']);
        $copie = ArchiveFolder::factory()->create(['user_id' => $destinataire->id]);
        ArchiveDossierPartage::factory()->create([
            'dossier_original_id' => $original->id,
            'dossier_copie_id' => $copie->id,
            'partage_par' => $expediteur->id,
            'destinataire_id' => $destinataire->id,
        ]);

        $response = $this->actingAs($expediteur)->get('/archives/partages');

        $response->assertOk();
        $response->assertSee('Conventions');
        $response->assertSee($destinataire->nom_complet);
    }
}
