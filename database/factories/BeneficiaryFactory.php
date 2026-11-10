<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Beneficiary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Beneficiary>
 */
class BeneficiaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'beneficiary_account' => 'SY'.fake()->numerify('##########'),
            'beneficiary_name' => fake()->randomElement(['رامي', 'هالة', 'جمال', 'منى', 'فادي', 'أمل']).' '.fake()->randomElement(['الأحمد', 'ناصيف', 'الزعبي', 'كنعان']),
            'bank_name' => fake()->randomElement(['Bank Alpha', 'Bank Beta', 'Bank Gamma']),
            'added_at' => now()->subDays(fake()->numberBetween(31, 200)),
        ];
    }

    public function justAdded(int $minutesAgo = 10): static
    {
        return $this->state(fn () => ['added_at' => now()->subMinutes($minutesAgo)]);
    }
}