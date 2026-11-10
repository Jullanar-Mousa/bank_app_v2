<?php

namespace Database\Factories;

use App\Models\AiProposal;
use App\Models\FraudCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiProposal>
 */
class AiProposalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'case_id' => FraudCase::factory(),
            'alert_id' => null,
            'tool_name' => 'note_case_add', // note_case_add, action_propose
            'payload' => ['note' => fake()->sentence(12)],
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}