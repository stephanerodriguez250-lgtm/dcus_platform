<?php

namespace Database\Factories;

use App\Models\ArchiveFichier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArchiveFichier>
 */
class ArchiveFichierFactory extends Factory
{
    protected $model = ArchiveFichier::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'folder_id' => null,
            'intitule' => fake()->sentence(3),
            'numero' => fake()->bothify('DCUS-####'),
            'description' => fake()->optional()->paragraph(),
            'nom_fichier' => 'document.pdf',
            'chemin_fichier' => 'archives/fichiers/document.pdf',
            'type_fichier' => 'pdf',
            'taille' => fake()->numberBetween(1000, 500000),
        ];
    }
}
