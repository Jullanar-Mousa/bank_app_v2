<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'beneficiary_id' => null,
            'device_id' => null,
            'amount' => fake()->randomFloat(2, 20, 400),
            'channel' => fake()->randomElement(['mobile', 'web']),
            'ip_address' => fake()->ipv4(),
            'city' => fake()->randomElement(['Damascus', 'Aleppo', 'Homs', 'Latakia', 'Hama']),
            'status' => 'completed',
            'transaction_time' => fake()->dateTimeBetween('-30 days', '-1 hour'),
            'idempotency_key' => (string) Str::uuid(),
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn () => ['status' => 'blocked']);
    }

    public function large(): static
    {
        return $this->state(fn () => ['amount' => fake()->randomFloat(2, 3000, 9000)]);
    }
}