<?php

namespace Tests\Unit\Models;

use App\Models\Codir;
use App\Models\CodirAcces;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodirTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_granted_access_can_download(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = User::factory()->create();
        $codir = Codir::factory()->create(['created_by' => $admin->id]);

        CodirAcces::create([
            'codir_id' => $codir->id,
            'user_id' => $agent->id,
            'accorde_par' => $admin->id,
        ]);

        $this->assertTrue($codir->userPeutTelecharger($agent->id));
    }

    public function test_user_without_granted_access_cannot_download(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = User::factory()->create();
        $codir = Codir::factory()->create(['created_by' => $admin->id]);

        $this->assertFalse($codir->userPeutTelecharger($agent->id));
    }
}
