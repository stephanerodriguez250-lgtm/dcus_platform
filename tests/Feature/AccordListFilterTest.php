<?php

namespace Tests\Feature;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccordListFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_accords_by_etape(): void
    {
        $user = User::factory()->create();

        $recu = Accord::factory()->create();
        $apprecie = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $apprecie->id]);
        $envoye = Accord::factory()->create(['envoye_le' => now()]);
        AccordAppreciation::factory()->create(['accord_id' => $envoye->id]);
        $signe = Accord::factory()->create(['envoye_le' => now(), 'date_signature' => now()]);
        AccordAppreciation::factory()->create(['accord_id' => $signe->id]);

        $casAttendus = ['recu' => $recu, 'apprecie' => $apprecie, 'envoye' => $envoye, 'signe' => $signe];

        foreach ($casAttendus as $etape => $accordAttendu) {
            $response = $this->actingAs($user)->get(route('accords.index', ['etape' => $etape]));
            $ids = $response->viewData('accords')->pluck('id');

            $this->assertTrue($ids->contains($accordAttendu->id), "L'accord attendu pour l'étape {$etape} est absent.");
            $this->assertCount(1, $ids, "Le filtre {$etape} devrait ne renvoyer qu'un seul accord.");
        }
    }

    public function test_filters_accords_by_date_arrivee_range(): void
    {
        $user = User::factory()->create();
        $ancien = Accord::factory()->create(['date_arrivee' => '2025-01-01']);
        $recent = Accord::factory()->create(['date_arrivee' => '2026-06-15']);

        $response = $this->actingAs($user)->get(route('accords.index', [
            'date_debut' => '2026-01-01',
            'date_fin' => '2026-12-31',
        ]));

        $ids = $response->viewData('accords')->pluck('id');
        $this->assertTrue($ids->contains($recent->id));
        $this->assertFalse($ids->contains($ancien->id));
    }

    public function test_filters_accords_by_institution_dorigine(): void
    {
        $user = User::factory()->create();
        $uac = Accord::factory()->create(['institution_partenaire' => 'UAC']);
        $unstim = Accord::factory()->create(['institution_partenaire' => 'UNSTIM']);

        $response = $this->actingAs($user)->get(route('accords.index', ['institution' => 'UAC']));

        $ids = $response->viewData('accords')->pluck('id');
        $this->assertTrue($ids->contains($uac->id));
        $this->assertFalse($ids->contains($unstim->id));
    }

    public function test_index_exposes_the_distinct_list_of_institutions_for_the_filter_dropdown(): void
    {
        $user = User::factory()->create();
        Accord::factory()->create(['institution_partenaire' => 'UAC']);
        Accord::factory()->create(['institution_partenaire' => 'UAC']);
        Accord::factory()->create(['institution_partenaire' => 'UNSTIM']);

        $response = $this->actingAs($user)->get(route('accords.index'));

        $institutions = $response->viewData('institutions');
        $this->assertSame(['UAC', 'UNSTIM'], $institutions->sort()->values()->all());
    }
}
