<?php

namespace Database\Factories;

use App\Models\RiskScore;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * الحدود: سماح 0-39، تحقق OTP 40-74، إيقاف 75-100
 *
 * @extends Factory<RiskScore>
 */
class RiskScoreFactory extends Factory
{
    private const REASON_POOL = [
        ['feature' => 'new_device', 'impact' => 0.31, 'text' => 'جهاز جديد غير معروف للعميل'],
        ['feature' => 'sim_changed_recently', 'impact' => 0.27, 'text' => 'تغيير الشريحة قبل ساعات'],
        ['feature' => 'new_beneficiary', 'impact' => 0.22, 'text' => 'مستفيد أُضيف قبل دقائق'],
        ['feature' => 'amount_vs_avg', 'impact' => 0.35, 'text' => 'المبلغ أكبر بكثير من المعتاد'],
        ['feature' => 'unusual_location', 'impact' => 0.18, 'text' => 'موقع غير معتاد'],
    ];

    public function definition(): array
    {
        return $this->withScore(fake()->numberBetween(5, 39), 'allow');
    }

    public function allow(): static
    {
        return $this->state(fn () => $this->withScore(fake()->numberBetween(5, 39), 'allow'));
    }

    public function otp(): static
    {
        return $this->state(fn () => $this->withScore(fake()->numberBetween(40, 74), 'otp_required'));
    }

    public function block(): static
    {
        return $this->state(fn () => $this->withScore(fake()->numberBetween(75, 99), 'block'));
    }

    private function withScore(int $score, string $decision): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'event_id' => null,
            'model_run_id' => null,
            'score' => $score,
            'decision' => $decision,
            'reasons' => $score >= 40
                ? fake()->randomElements(self::REASON_POOL, fake()->numberBetween(2, 3))
                : [],
            'evaluated_at' => now(),
            'rule_score' => (int) round($score * 0.6),
            'model_probability' => round($score / 100, 4),
            'used_fallback' => false,
            'latency_ms' => fake()->numberBetween(60, 250),
            'features' => [],
        ];
    }
}