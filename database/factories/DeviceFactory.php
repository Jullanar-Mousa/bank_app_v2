<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_fingerprint' => fake()->unique()->sha1(),
            'device_type' => fake()->randomElement(['mobile', 'web']),
            'sim_changed_at' => null,
            'last_ip' => fake()->ipv4(),
            'last_location' => fake()->randomElement(['Damascus', 'Aleppo', 'Homs', 'Latakia', 'Hama']),
        ];
    }

    public function simChangedRecently(int $hoursAgo = 2): static
    {
        return $this->state(fn () => ['sim_changed_at' => now()->subHours($hoursAgo)]);
    }
}