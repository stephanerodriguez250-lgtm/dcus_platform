<?php

namespace Database\Factories;

use App\Models\ArchiveFichier;
use App\Models\ArchivePartage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArchivePartage>
 */
class ArchivePartageFactory extends Factory
{
    protected $model = ArchivePartage::class;

    public function definition(): array
    {
        return [
            'fichier_original_id' => ArchiveFichier::factory(),
            'fichier_copie_id' => ArchiveFichier::factory(),
            'partage_par' => User::factory(),
            'destinataire_id' => User::factory(),
        ];
    }
}
