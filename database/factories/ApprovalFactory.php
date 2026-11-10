<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * مبدأ العينين: طالب + موافق مختلفين
 *
 * @extends Factory<Approval>
 */
class ApprovalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => 'freeze_account', // freeze_account, unfreeze_account, close_case
            'subject_type' => Account::class,
            'subject_id' => Account::factory(),
            'requested_by' => User::factory(),
            'approved_by' => null,
            'status' => 'pending',
            'reason' => fake()->sentence(8),
            'decided_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'approved_by' => User::factory(),
            'status' => 'approved',
            'decided_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'approved_by' => User::factory(),
            'status' => 'rejected',
            'decided_at' => now(),
        ]);
    }
}