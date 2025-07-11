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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->text(50),
            'type' => $this->faker->randomElement(IndexTypeEnum::cases()),
            'published_by' => User::factory(),
        ];
    }
}
