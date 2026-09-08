<?php

namespace Database\Factories;

use App\Models\Codir;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Codir>
 */
class CodirFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'objet' => fake()->sentence(4),
            'date' => fake()->date(),
            'heure_debut' => '09:00',
            'heure_fin' => '11:00',
            'lieu' => 'Salle de réunion DCUS',
            'presidente' => fake()->name(),
            'rapporteur' => fake()->name(),
            'statut' => 'planifie',
            'created_by' => User::factory(),
        ];
    }
}
