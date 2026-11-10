<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountAction>
 */
class AccountActionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'transaction_id' => null,
            'alert_id' => null,
            'approval_id' => null,
            'action' => 'freeze', // freeze, unfreeze, block_transaction, require_otp
            'status' => 'pending',
            'requested_by' => null, // null = النظام
            'reason' => fake()->sentence(6),
            'executed_at' => null,
        ];
    }

    public function executed(): static
    {
        return $this->state(fn () => ['status' => 'executed', 'executed_at' => now()]);
    }
}