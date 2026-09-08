<?php

namespace Database\Factories;

use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArchiveFolder>
 */
class ArchiveFolderFactory extends Factory
{
    protected $model = ArchiveFolder::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'parent_id' => null,
            'nom' => fake()->unique()->words(2, true),
        ];
    }
}
