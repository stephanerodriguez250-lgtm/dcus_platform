<?php

namespace Tests\Unit\Models\Concerns;

use App\Models\Accord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasHashedRouteKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_key_is_not_the_raw_id(): void
    {
        $accord = Accord::factory()->create();

        $this->assertNotSame((string) $accord->id, (string) $accord->getRouteKey());
        $this->assertFalse(is_numeric($accord->getRouteKey()));
    }

    public function test_resolve_route_binding_finds_the_model_from_its_hashed_key(): void
    {
        $accord = Accord::factory()->create();

        $resolu = (new Accord)->resolveRouteBinding($accord->getRouteKey());

        $this->assertNotNull($resolu);
        $this->assertSame($accord->id, $resolu->id);
    }

    public function test_resolve_route_binding_returns_null_for_an_invalid_hash(): void
    {
        $resolu = (new Accord)->resolveRouteBinding('hash-invalide');

        $this->assertNull($resolu);
    }

    public function test_route_generates_a_url_without_the_raw_id_as_a_bare_segment(): void
    {
        $accord = Accord::factory()->create();

        $url = route('accords.show', $accord);
        $dernierSegment = last(explode('/', rtrim($url, '/')));

        $this->assertNotSame((string) $accord->id, $dernierSegment);
        $this->assertSame($accord->getRouteKey(), $dernierSegment);
    }

    public function test_visiting_a_tampered_hash_404s_instead_of_crashing(): void
    {
        $user = User::factory()->create();
        $accord = Accord::factory()->create();

        $response = $this->actingAs($user)->get('/accords/'.$accord->getRouteKey().'x');

        $response->assertNotFound();
    }
}
