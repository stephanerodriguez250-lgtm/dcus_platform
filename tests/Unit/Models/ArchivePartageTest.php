<?php

namespace Tests\Unit\Models;

use App\Models\ArchiveFichier;
use App\Models\ArchivePartage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivePartageTest extends TestCase
{
    use RefreshDatabase;

    public function test_relations_resolve_original_copy_and_users(): void
    {
        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $original = ArchiveFichier::factory()->create(['user_id' => $expediteur->id]);
        $copie = ArchiveFichier::factory()->create(['user_id' => $destinataire->id]);

        $partage = ArchivePartage::factory()->create([
            'fichier_original_id' => $original->id,
            'fichier_copie_id' => $copie->id,
            'partage_par' => $expediteur->id,
            'destinataire_id' => $destinataire->id,
        ]);

        $this->assertTrue($partage->fichierOriginal->is($original));
        $this->assertTrue($partage->fichierCopie->is($copie));
        $this->assertTrue($partage->partagePar->is($expediteur));
        $this->assertTrue($partage->destinataire->is($destinataire));
    }

    public function test_deleting_the_copy_cascades_to_the_partage_row(): void
    {
        $copie = ArchiveFichier::factory()->create();
        $partage = ArchivePartage::factory()->create(['fichier_copie_id' => $copie->id]);

        $copie->delete();

        $this->assertDatabaseMissing('archive_partages', ['id' => $partage->id]);
    }

    public function test_deleting_the_original_keeps_the_partage_row_with_a_null_pointer(): void
    {
        $original = ArchiveFichier::factory()->create();
        $partage = ArchivePartage::factory()->create(['fichier_original_id' => $original->id]);

        $original->delete();

        $this->assertDatabaseHas('archive_partages', ['id' => $partage->id, 'fichier_original_id' => null]);
    }

    public function test_fichier_exposes_partage_origine_when_it_is_a_received_copy(): void
    {
        $expediteur = User::factory()->create();
        $copie = ArchiveFichier::factory()->create();

        ArchivePartage::factory()->create([
            'fichier_copie_id' => $copie->id,
            'partage_par' => $expediteur->id,
        ]);

        $this->assertNotNull($copie->fresh()->partageOrigine);
        $this->assertTrue($copie->fresh()->partageOrigine->partagePar->is($expediteur));
    }

    public function test_fichier_has_no_partage_origine_when_it_was_not_received_by_share(): void
    {
        $fichier = ArchiveFichier::factory()->create();

        $this->assertNull($fichier->partageOrigine);
    }
}
