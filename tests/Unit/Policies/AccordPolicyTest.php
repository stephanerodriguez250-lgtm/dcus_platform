<?php

namespace Tests\Unit\Policies;

use App\Models\Accord;
use App\Models\AccordAppreciateur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccordPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_secretaire_can_apprecier(): void
    {
        $admin = User::factory()->admin()->create();
        $secretaire = User::factory()->secretaire()->create();

        $this->assertTrue($admin->can('apprecier', Accord::class));
        $this->assertTrue($secretaire->can('apprecier', Accord::class));
    }

    public function test_plain_agent_cannot_apprecier_by_default(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $this->assertFalse($agent->can('apprecier', Accord::class));
    }

    public function test_agent_granted_via_accord_appreciateur_can_apprecier(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        AccordAppreciateur::factory()->create(['user_id' => $agent->id]);

        $this->assertTrue($agent->fresh()->can('apprecier', Accord::class));
    }
}
