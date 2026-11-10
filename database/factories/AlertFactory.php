<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\RiskScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'case_id' => null,
            'risk_score_id' => RiskScore::factory()->block(),
            'status' => 'new',
            'priority' => 'high',
            'assigned_to' => null,
            'decision' => null,
            'decision_reason' => null,
            'decided_by' => null,
            'decided_at' => null,
        ];
    }
}