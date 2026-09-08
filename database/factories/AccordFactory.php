<?php

namespace Database\Factories;

use App\Models\Accord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Accord>
 */
class AccordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titre' => fake()->sentence(5),
            'institution_partenaire' => fake()->company(),
            'pays_partenaire' => fake()->country(),
            'universite_beneficiaire' => fake()->company(),
            'description' => fake()->paragraph(),
            'statut' => 'identifie',
            'created_by' => User::factory(),
        ];
    }
}
