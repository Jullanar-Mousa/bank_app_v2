<?php

namespace Database\Factories;

use App\Models\CaseNote;
use App\Models\FraudCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseNote>
 */
class CaseNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'case_id' => FraudCase::factory(),
            'user_id' => null,
            'note' => fake()->sentence(10),
            'is_ai_generated' => false,
        ];
    }

    public function aiGenerated(): static
    {
        return $this->state(fn () => ['is_ai_generated' => true]);
    }
}