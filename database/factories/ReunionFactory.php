<?php

namespace Database\Factories;

use App\Models\Reunion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reunion>
 */
class ReunionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titre' => fake()->sentence(4),
            'date' => fake()->date(),
            'heure' => '09:00',
            'lieu' => 'Salle de réunion DCUS',
            'ordre_du_jour' => fake()->paragraph(),
            'statut' => 'planifiee',
            'convocateur' => fake()->name(),
            'created_by' => User::factory(),
        ];
    }
}
