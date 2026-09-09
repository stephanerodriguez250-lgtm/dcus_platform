<?php

namespace Tests\Unit\Models;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccordTest extends TestCase
{
    use RefreshDatabase;

    public function test_etape_is_recu_when_freshly_created(): void
    {
        $accord = Accord::factory()->create();

        $this->assertSame('recu', $accord->etape);
        $this->assertSame('Reçu', $accord->etape_label);
    }

    public function test_etape_is_apprecie_once_an_appreciation_exists(): void
    {
        $accord = Accord::factory()->create();
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $this->assertSame('apprecie', $accord->fresh()->etape);
    }

    public function test_etape_is_envoye_once_marked_sent(): void
    {
        $accord = Accord::factory()->create(['envoye_le' => now()]);
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $this->assertSame('envoye', $accord->fresh()->etape);
    }

    public function test_etape_is_signe_once_signature_is_recorded(): void
    {
        $accord = Accord::factory()->create(['envoye_le' => now(), 'date_signature' => now()]);
        AccordAppreciation::factory()->create(['accord_id' => $accord->id]);

        $this->assertSame('signe', $accord->fresh()->etape);
    }

    public function test_calculer_date_expiration_adds_years_for_ans_unit(): void
    {
        $accord = Accord::factory()->make([
            'date_signature' => '2026-01-15',
            'duree_valeur' => 3,
            'duree_unite' => 'ans',
        ]);

        $this->assertSame('2029-01-15', $accord->calculerDateExpiration()->format('Y-m-d'));
    }

    public function test_calculer_date_expiration_adds_months_for_mois_unit(): void
    {
        $accord = Accord::factory()->make([
            'date_signature' => '2026-01-15',
            'duree_valeur' => 6,
            'duree_unite' => 'mois',
        ]);

        $this->assertSame('2026-07-15', $accord->calculerDateExpiration()->format('Y-m-d'));
    }

    public function test_calculer_date_expiration_is_null_without_signature_or_duree(): void
    {
        $accord = Accord::factory()->make(['date_signature' => null]);

        $this->assertNull($accord->calculerDateExpiration());
    }

    public function test_duree_label_combines_value_and_unit(): void
    {
        $accord = Accord::factory()->make(['duree_valeur' => 2, 'duree_unite' => 'ans']);

        $this->assertSame('2 ans', $accord->duree_label);
    }
}
