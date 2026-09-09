<?php

namespace Database\Factories;

use App\Models\Accord;
use App\Models\AccordRapportConformite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccordRapportConformite>
 */
class AccordRapportConformiteFactory extends Factory
{
    protected $model = AccordRapportConformite::class;

    public function definition(): array
    {
        return [
            'accord_id' => Accord::factory(),
            'resume' => fake()->paragraph(),
            'points_conformes' => fake()->sentence(),
            'points_non_conformes' => fake()->sentence(),
            'genere_par' => User::factory(),
            'genere_le' => now(),
        ];
    }
}
