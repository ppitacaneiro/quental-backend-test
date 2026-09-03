<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(1, 1000000),
            'name' => fake()->city(),
            'type' => fake()->randomElement(['Planet', 'Space station', 'Microverse']),
            'dimension' => fake()->words(2, true),
        ];
    }
}
