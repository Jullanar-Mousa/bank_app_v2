<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    private const FIRST_NAMES = ['محمد', 'سارة', 'لينا', 'خالد', 'ريم', 'عمر', 'هبة', 'يوسف', 'نور', 'سامر', 'دانا', 'طارق', 'رنا', 'باسل', 'ملك'];
    private const LAST_NAMES = ['العلي', 'الحسن', 'نصر', 'الشامي', 'دياب', 'حداد', 'المصري', 'قبّاني', 'سليمان', 'عيسى'];
    private const CITIES = ['Damascus', 'Aleppo', 'Homs', 'Latakia', 'Hama'];

    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->randomElement(self::FIRST_NAMES).' '.fake()->randomElement(self::LAST_NAMES),
            'phone' => fake()->unique()->numerify('09########'),
            'email' => fake()->unique()->safeEmail(),
            'avg_balance' => fake()->randomFloat(2, 1500, 20000),
            'usage_pattern' => [
                'typical_channel' => fake()->randomElement(['mobile', 'web']),
                'typical_city' => fake()->randomElement(self::CITIES),
                'active_hours' => [8, 22],
                'avg_txn_amount' => fake()->numberBetween(50, 400),
            ],
        ];
    }
}