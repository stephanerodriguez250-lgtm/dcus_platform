<?php

namespace Tests\Unit\Models;

use App\Models\Decision;
use Tests\TestCase;

class DecisionTest extends TestCase
{
    public function test_is_en_retard_when_echeance_is_past_and_not_closed(): void
    {
        $decision = new Decision([
            'echeance' => now()->subDay(),
            'statut' => 'en_cours',
        ]);

        $this->assertTrue($decision->isEnRetard());
    }

    public function test_is_not_en_retard_when_echeance_is_in_the_future(): void
    {
        $decision = new Decision([
            'echeance' => now()->addDay(),
            'statut' => 'en_cours',
        ]);

        $this->assertFalse($decision->isEnRetard());
    }

    public function test_is_not_en_retard_when_cloturee_even_if_echeance_is_past(): void
    {
        $decision = new Decision([
            'echeance' => now()->subDay(),
            'statut' => 'cloturee',
        ]);

        $this->assertFalse($decision->isEnRetard());
    }

    public function test_is_not_en_retard_when_annulee_even_if_echeance_is_past(): void
    {
        $decision = new Decision([
            'echeance' => now()->subDay(),
            'statut' => 'annulee',
        ]);

        $this->assertFalse($decision->isEnRetard());
    }

    public function test_is_not_en_retard_when_no_echeance(): void
    {
        $decision = new Decision(['statut' => 'en_cours']);

        $this->assertFalse($decision->isEnRetard());
    }

    public function test_statut_label_falls_back_to_raw_value_for_unknown_statut(): void
    {
        $decision = new Decision(['statut' => 'inconnu']);

        $this->assertSame('inconnu', $decision->statut_label);
    }

    public function test_progression_color_thresholds(): void
    {
        $this->assertSame('danger', (new Decision(['progression' => 10]))->progression_color);
        $this->assertSame('warning', (new Decision(['progression' => 30]))->progression_color);
        $this->assertSame('info', (new Decision(['progression' => 60]))->progression_color);
        $this->assertSame('success', (new Decision(['progression' => 100]))->progression_color);
    }
}
