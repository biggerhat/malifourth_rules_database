<?php

namespace Database\Factories;

use App\Enums\IndexTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Index>
 */
class IndexFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(IndexTypeEnum::cases()),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(2)).'</p>',
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
