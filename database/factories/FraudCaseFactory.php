<?php

namespace Database\Factories;

use App\Models\FraudCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FraudCase>
 */
class FraudCaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'حالة اشتباه رقم '.fake()->unique()->numerify('####'),
            'assigned_to' => null,
            'status' => 'open',
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
        ];
    }
}