<?php

namespace Database\Factories;

use App\Models\Label;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Label>
 */
class LabelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'event_id' => null,
            'alert_id' => null,
            'is_fraud' => false,
            'source' => 'analyst', // analyst, customer, investigation
            'labeled_by' => null,
        ];
    }

    public function fraud(): static
    {
        return $this->state(fn () => ['is_fraud' => true]);
    }
}