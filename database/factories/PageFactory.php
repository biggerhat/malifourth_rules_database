<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
            'page_number' => fake()->unique()->numberBetween(1, 100),
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
