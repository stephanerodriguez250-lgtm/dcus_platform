<?php

namespace Tests\Feature;

use App\Models\Accord;
use App\Models\Reunion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_accords_csv_export_downloads_successfully(): void
    {
        $user = User::factory()->create();
        Accord::factory()->create(['created_by' => $user->id]);

        $response = $this->actingAs($user)->get('/exports/accords/csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Intitul', $response->getContent());
    }

    public function test_reunions_csv_export_downloads_successfully(): void
    {
        $user = User::factory()->create();
        Reunion::factory()->create(['created_by' => $user->id]);

        $response = $this->actingAs($user)->get('/exports/reunions/csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Intitul', $response->getContent());
    }
}
