<?php

namespace Database\Factories;

use App\Models\Watchlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Watchlist>
 */
class WatchlistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'list_type' => 'Blacklist',
            'entity_type' => 'IP',
            'entity_value' => fake()->unique()->ipv4(),
            'reason' => fake()->sentence(5),
            'added_at' => now(),
        ];
    }

    public function whitelist(): static
    {
        return $this->state(fn () => ['list_type' => 'Whitelist']);
    }
}