<?php

namespace Tests\Unit\Policies;

use App\Models\ArchiveDossierPartage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveDossierPartagePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sender_can_delete_their_own_partage(): void
    {
        $expediteur = User::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create(['partage_par' => $expediteur->id]);

        $this->assertTrue($expediteur->can('delete', $partage));
    }

    public function test_recipient_cannot_delete_a_partage_they_received(): void
    {
        $destinataire = User::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create(['destinataire_id' => $destinataire->id]);

        $this->assertFalse($destinataire->can('delete', $partage));
    }

    public function test_recipient_can_download_the_zip(): void
    {
        $destinataire = User::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create(['destinataire_id' => $destinataire->id]);

        $this->assertTrue($destinataire->can('download', $partage));
    }

    public function test_sender_cannot_download_the_zip(): void
    {
        $expediteur = User::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create(['partage_par' => $expediteur->id]);

        $this->assertFalse($expediteur->can('download', $partage));
    }

    public function test_unrelated_user_can_neither_delete_nor_download(): void
    {
        $autre = User::factory()->create();
        $partage = ArchiveDossierPartage::factory()->create();

        $this->assertFalse($autre->can('delete', $partage));
        $this->assertFalse($autre->can('download', $partage));
    }
}
