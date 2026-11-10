<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\OtpChallenge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<OtpChallenge>
 */
class OtpChallengeFactory extends Factory
{
    private static ?string $hash = null;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'transaction_id' => null,
            'event_id' => null,
            // نخزن hash فقط (الرمز التجريبي 123456)، ونحسبه مرة وحدة لأن bcrypt بطيء
            'code_hash' => self::$hash ??= Hash::make('123456'),
            'status' => 'pending',
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
            'verified_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['status' => 'verified', 'attempts' => 1, 'verified_at' => now()]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed', 'attempts' => 3]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['status' => 'expired', 'expires_at' => now()->subMinutes(10)]);
    }
}