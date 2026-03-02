<?php

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scheme>
 */
class SchemeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'season_id' => Season::factory(),
            'prerequisites' => $this->faker->paragraph(),
            'reveal' => $this->faker->paragraph(),
            'scoring' => $this->faker->paragraph(),
            'additional' => $this->faker->paragraph(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => now(),
            'published_by' => 1,
        ]);
    }
}
