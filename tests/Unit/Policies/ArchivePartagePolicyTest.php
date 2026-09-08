<?php

namespace Tests\Unit\Policies;

use App\Models\ArchivePartage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivePartagePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sender_can_delete_their_own_partage(): void
    {
        $expediteur = User::factory()->create();
        $partage = ArchivePartage::factory()->create(['partage_par' => $expediteur->id]);

        $this->assertTrue($expediteur->can('delete', $partage));
    }

    public function test_recipient_cannot_delete_a_partage_they_received(): void
    {
        $destinataire = User::factory()->create();
        $partage = ArchivePartage::factory()->create(['destinataire_id' => $destinataire->id]);

        $this->assertFalse($destinataire->can('delete', $partage));
    }

    public function test_unrelated_user_cannot_delete_the_partage(): void
    {
        $autre = User::factory()->create();
        $partage = ArchivePartage::factory()->create();

        $this->assertFalse($autre->can('delete', $partage));
    }
}
