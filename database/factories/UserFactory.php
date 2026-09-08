<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'agent',
            'telephone' => fake()->phoneNumber(),
            'poste' => fake()->jobTitle(),
            'actif' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }

    public function secretaire(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'secretaire']);
    }

    public function inactif(): static
    {
        return $this->state(fn (array $attributes) => ['actif' => false]);
    }
}
