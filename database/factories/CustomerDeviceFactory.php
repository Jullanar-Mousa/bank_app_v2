<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerDevice>
 */
class CustomerDeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'device_id' => Device::factory(),
            'is_trusted' => true,
            'last_used_at' => now()->subDays(fake()->numberBetween(0, 10)),
        ];
    }
}