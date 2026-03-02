<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Errata>
 */
class ErrataFactory extends Factory
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
            'sort_order' => $this->faker->numberBetween(0, 10),
            'content' => $this->faker->paragraph(),
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
