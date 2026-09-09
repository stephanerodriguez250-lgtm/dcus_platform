<?php

namespace Database\Factories;

use App\Models\ArchiveDossierPartage;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArchiveDossierPartage>
 */
class ArchiveDossierPartageFactory extends Factory
{
    protected $model = ArchiveDossierPartage::class;

    public function definition(): array
    {
        return [
            'dossier_original_id' => ArchiveFolder::factory(),
            'dossier_copie_id' => ArchiveFolder::factory(),
            'zip_path' => 'archives/dossier-partages/'.fake()->uuid().'.zip',
            'partage_par' => User::factory(),
            'destinataire_id' => User::factory(),
        ];
    }
}
