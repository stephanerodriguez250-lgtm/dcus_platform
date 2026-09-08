<?php

namespace Tests\Feature\Auth;

use App\Models\Accord;
use App\Models\Codir;
use App\Models\CodirAcces;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_cannot_create_accord(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->get('/accords/create');

        $response->assertForbidden();
    }

    public function test_secretaire_can_create_accord(): void
    {
        $secretaire = User::factory()->secretaire()->create();

        $response = $this->actingAs($secretaire)->get('/accords/create');

        $response->assertOk();
    }

    public function test_agent_can_view_but_not_edit_accord(): void
    {
        $creator = User::factory()->admin()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        $accord = Accord::factory()->create(['created_by' => $creator->id]);

        $this->actingAs($agent)->get("/accords/{$accord->id}")->assertOk();
        $this->actingAs($agent)->get("/accords/{$accord->id}/edit")->assertForbidden();
    }

    public function test_admin_can_administer_codir_reports_agent_cannot(): void
    {
        $admin = User::factory()->admin()->create();
        $secretaire = User::factory()->secretaire()->create();
        $codir = Codir::factory()->create(['created_by' => $admin->id]);

        // secretaire peut gérer le CODIR (manage) mais pas administrer les rapports (admin only)
        $response = $this->actingAs($secretaire)->post("/codirs/{$codir->id}/acces", [
            'user_id' => $secretaire->id,
            'action' => 'donner',
        ]);

        $response->assertForbidden();
    }

    public function test_user_without_access_cannot_download_codir_pdf(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        $codir = Codir::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($agent)->get("/codirs/{$codir->id}/pdf");

        $response->assertForbidden();
    }

    public function test_user_with_granted_access_can_download_codir_pdf(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        $codir = Codir::factory()->create(['created_by' => $admin->id]);

        CodirAcces::create([
            'codir_id' => $codir->id,
            'user_id' => $agent->id,
            'accorde_par' => $admin->id,
        ]);

        $response = $this->actingAs($agent)->get("/codirs/{$codir->id}/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
