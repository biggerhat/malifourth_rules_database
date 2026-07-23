<?php

namespace Database\Factories;

use App\Enums\FactionEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardErrata>
 */
class CardErrataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'faction' => $this->faker->randomElement(FactionEnum::cases())->value,
            'card_name' => $this->faker->unique()->name(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'published_at' => now(),
            'published_by' => User::factory(),
        ]);
    }
}
