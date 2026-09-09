<?php

namespace Tests\Unit\Models;

use App\Models\ArchiveDossierPartage;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveDossierPartageTest extends TestCase
{
    use RefreshDatabase;

    public function test_relations_resolve_original_copy_and_users(): void
    {
        $expediteur = User::factory()->create();
        $destinataire = User::factory()->create();
        $original = ArchiveFolder::factory()->create(['user_id' => $expediteur->id]);
        $copie = ArchiveFolder::factory()->create(['user_id' => $destinataire->id]);

        $partage = ArchiveDossierPartage::factory()->create([
            'dossier_original_id' => $original->id,
            'dossier_copie_id' => $copie->id,
            'partage_par' => $expediteur->id,
            'destinataire_id' => $destinataire->id,
            'zip_path' => 'archives/dossier-partages/test.zip',
        ]);

        $this->assertTrue($partage->dossierOriginal->is($original));
        $this->assertTrue($partage->dossierCopie->is($copie));
        $this->assertTrue($partage->partagePar->is($expediteur));
        $this->assertTrue($partage->destinataire->is($destinataire));
        $this->assertSame('archives/dossier-partages/test.zip', $partage->zip_path);
    }

    public function test_deleting_the_copy_cascades_to_the_partage_row(): void
    {
        $copie = ArchiveFolder::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create(['dossier_copie_id' => $copie->id]);

        $copie->delete();

        $this->assertDatabaseMissing('archive_dossier_partages', ['id' => $partage->id]);
    }

    public function test_deleting_the_original_keeps_the_partage_row_with_a_null_pointer(): void
    {
        $original = ArchiveFolder::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create(['dossier_original_id' => $original->id]);

        $original->delete();

        $this->assertDatabaseHas('archive_dossier_partages', ['id' => $partage->id, 'dossier_original_id' => null]);
    }
}
