<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AccountEvent>
 */
class AccountEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'device_id' => null,
            'event_type' => 'login',
            'ip_address' => fake()->ipv4(),
            'details' => [],
            'event_time' => fake()->dateTimeBetween('-30 days', '-1 hour'),
            'idempotency_key' => (string) Str::uuid(),
        ];
    }

    public function ofType(string $type): static
    {
        return $this->state(fn () => ['event_type' => $type]);
    }
}