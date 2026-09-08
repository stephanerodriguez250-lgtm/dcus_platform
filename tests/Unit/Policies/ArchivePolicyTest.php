<?php

namespace Tests\Unit\Policies;

use App\Models\ArchiveFichier;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_update_and_delete_their_folder(): void
    {
        $owner = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($owner->can('view', $dossier));
        $this->assertTrue($owner->can('update', $dossier));
        $this->assertTrue($owner->can('delete', $dossier));
    }

    public function test_another_user_cannot_view_update_or_delete_the_folder(): void
    {
        $owner = User::factory()->create();
        $autre = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($autre->can('view', $dossier));
        $this->assertFalse($autre->can('update', $dossier));
        $this->assertFalse($autre->can('delete', $dossier));
    }

    public function test_owner_can_view_update_and_delete_their_fichier(): void
    {
        $owner = User::factory()->create();
        $fichier = ArchiveFichier::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($owner->can('view', $fichier));
        $this->assertTrue($owner->can('update', $fichier));
        $this->assertTrue($owner->can('delete', $fichier));
    }

    public function test_admin_has_no_bypass_on_another_users_archive(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $dossier = ArchiveFolder::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($admin->can('view', $dossier));
    }
}
