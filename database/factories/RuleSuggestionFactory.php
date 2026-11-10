<?php

namespace Database\Factories;

use App\Models\RuleSuggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RuleSuggestion>
 */
class RuleSuggestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'suggested_by' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(10),
            'conditions' => ['all' => [['field' => 'amount_to_avg_ratio', 'op' => '>', 'value' => 4]]],
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
            'rule_id' => null,
        ];
    }
}