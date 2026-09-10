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
            'institution_origine' => fake()->company(),
            'institution_partenaire' => fake()->company(),
            'reference' => 'MESRS-'.fake()->unique()->numerify('####/####'),
            'date_arrivee' => fake()->date(),
            'heure_arrivee' => '09:00',
            'created_by' => User::factory(),
        ];
    }
}
