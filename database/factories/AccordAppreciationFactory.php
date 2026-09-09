<?php

namespace Database\Factories;

use App\Models\Accord;
use App\Models\AccordAppreciation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccordAppreciation>
 */
class AccordAppreciationFactory extends Factory
{
    protected $model = AccordAppreciation::class;

    public function definition(): array
    {
        return [
            'accord_id' => Accord::factory(),
            'origine' => fake()->company(),
            'objet' => fake()->sentence(),
            'avis' => fake()->paragraph(),
            'observations_forme' => fake()->sentence(),
            'observations_fond' => fake()->sentence(),
            'redige_par' => User::factory(),
        ];
    }
}
