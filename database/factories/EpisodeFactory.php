<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(1, 1000000),
            'name' => fake()->sentence(3),
            'air_date' => fake()->date('F j, Y'),
            'episode_code' => sprintf('S%02dE%02d', fake()->numberBetween(1, 5), fake()->numberBetween(1, 20)),
        ];
    }
}
