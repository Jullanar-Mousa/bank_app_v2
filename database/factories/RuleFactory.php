<?php

namespace Database\Factories;

use App\Models\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rule>
 */
class RuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rule_name' => fake()->unique()->slug(2),
            'description' => fake()->sentence(6),
            'weight' => fake()->numberBetween(5, 40),
            'priority' => fake()->numberBetween(1, 3),
            'is_active' => true,
            'conditions' => ['all' => [['field' => 'amount_to_avg_ratio', 'op' => '>', 'value' => 3]]],
            'action' => 'add_score',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}