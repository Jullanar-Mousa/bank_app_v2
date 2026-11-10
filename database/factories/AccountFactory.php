<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'account_number' => 'SY'.fake()->unique()->numerify('##########'),
            'type' => fake()->randomElement(['checking', 'savings']),
            'status' => 'active',
            'balance' => fake()->randomFloat(2, 1500, 25000),
        ];
    }

    public function frozen(): static
    {
        return $this->state(fn () => ['status' => 'frozen']);
    }
}