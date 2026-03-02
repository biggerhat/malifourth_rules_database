<?php

namespace Database\Factories;

use App\Enums\SuitEnum;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Strategy>
 */
class StrategyFactory extends Factory
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
            'suit' => $this->faker->randomElement(SuitEnum::cases()),
            'setup' => $this->faker->paragraph(),
            'rules' => $this->faker->paragraph(),
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
