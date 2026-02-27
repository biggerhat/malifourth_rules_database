<?php

namespace Database\Factories;

use App\Enums\FaqCategoryEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faq>
 */
class FaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'category' => fake()->randomElement(FaqCategoryEnum::cases()),
            'answer' => '<p>'.implode('</p><p>', fake()->paragraphs(2)).'</p>',
            'sort_order' => fake()->numberBetween(0, 100),
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
