<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Character>
 */
class CharacterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(1, 1000000),
            'name' => fake()->name(),
            'status' => fake()->randomElement(['Alive', 'Dead', 'unknown']),
            'species' => fake()->randomElement(['Human', 'Alien', 'Robot']),
            'type' => fake()->optional()->word(),
            'gender' => fake()->randomElement(['Male', 'Female', 'Genderless', 'unknown']),
            'image' => fake()->imageUrl(),
            'origin_location_id' => null,
            'current_location_id' => null,
        ];
    }
}
