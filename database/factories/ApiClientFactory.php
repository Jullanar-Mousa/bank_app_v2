<?php

namespace Database\Factories;

use App\Models\ApiClient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ApiClient>
 */
class ApiClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'عميل تكامل '.fake()->numerify('###'),
            'key_id' => 'key_'.Str::lower(Str::random(12)),
            'secret' => Str::random(40), // الموديل يشفّره (encrypted)
            'is_active' => true,
            'last_used_at' => null,
            'revoked_at' => null,
            'created_by' => null,
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn () => ['is_active' => false, 'revoked_at' => now()]);
    }
}