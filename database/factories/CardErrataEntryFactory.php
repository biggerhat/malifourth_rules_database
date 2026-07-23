<?php

namespace Database\Factories;

use App\Models\CardErrata;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardErrataEntry>
 */
class CardErrataEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'card_errata_id' => CardErrata::factory(),
            'what_changed' => $this->faker->sentence(),
            'what_it_was' => $this->faker->sentence(),
            'what_it_is_now' => $this->faker->sentence(),
            'sort_order' => 0,
        ];
    }
}
