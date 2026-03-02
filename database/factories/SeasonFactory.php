<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Season>
 */
class SeasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Gaining Grounds '.$this->faker->year(),
            'content' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
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
