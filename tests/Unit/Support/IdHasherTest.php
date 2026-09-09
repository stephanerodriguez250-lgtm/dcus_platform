<?php

namespace Tests\Unit\Support;

use App\Support\IdHasher;
use Tests\TestCase;

class IdHasherTest extends TestCase
{
    public function test_encoder_produces_a_non_numeric_string(): void
    {
        $hash = IdHasher::encoder(1);

        $this->assertIsString($hash);
        $this->assertFalse(is_numeric($hash));
    }

    public function test_decoder_reverses_encoder(): void
    {
        $hash = IdHasher::encoder(42);

        $this->assertSame(42, IdHasher::decoder($hash));
    }

    public function test_decoder_returns_null_for_garbage_input(): void
    {
        $this->assertNull(IdHasher::decoder('ceci-n-est-pas-un-hash-valide'));
        $this->assertNull(IdHasher::decoder(''));
    }

    public function test_two_different_ids_never_produce_the_same_hash(): void
    {
        $this->assertNotSame(IdHasher::encoder(1), IdHasher::encoder(2));
    }
}
