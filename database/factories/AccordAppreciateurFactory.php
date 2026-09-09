<?php

namespace Database\Factories;

use App\Models\AccordAppreciateur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccordAppreciateur>
 */
class AccordAppreciateurFactory extends Factory
{
    protected $model = AccordAppreciateur::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'accorde_par' => User::factory(),
        ];
    }
}
