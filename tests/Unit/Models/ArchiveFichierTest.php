<?php

namespace Tests\Unit\Models;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveFichierTest extends TestCase
{
    use RefreshDatabase;

    public function test_taille_formatee_formats_bytes_as_ko_or_mo(): void
    {
        $petit = ArchiveFichier::factory()->make(['taille' => 500]);
        $moyen = ArchiveFichier::factory()->make(['taille' => 2048]);
        $gros = ArchiveFichier::factory()->make(['taille' => 2 * 1048576]);

        $this->assertSame('500 o', $petit->taille_formatee);
        $this->assertSame('2 Ko', $moyen->taille_formatee);
        $this->assertSame('2 Mo', $gros->taille_formatee);
    }

    public function test_icone_matches_known_extensions_and_falls_back_for_unknown(): void
    {
        $pdf = ArchiveFichier::factory()->make(['type_fichier' => 'pdf']);
        $inconnu = ArchiveFichier::factory()->make(['type_fichier' => 'zip']);

        $this->assertStringContainsString('bi-file-earmark-pdf', $pdf->icone);
        $this->assertStringContainsString('bi-file-earmark', $inconnu->icone);
    }

    public function test_dossier_relation_resolves_the_owning_folder(): void
    {
        $user = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $user->id]);
        $fichier = ArchiveFichier::factory()->create(['user_id' => $user->id, 'folder_id' => $dossier->id]);

        $this->assertTrue($fichier->dossier->is($dossier));
    }
}
