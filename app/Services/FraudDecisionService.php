<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\RiskScore;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Watchlist;
use Illuminate\Support\Facades\Cache;

class FraudDecisionService
{
    public function __construct(
        protected RuleEngineService $ruleEngine,
        protected MachineLearningService $mlService
    ) {}

    /**
     * تقييم المعاملة واتخاذ القرار الأمني المناسب
     */
    public function process(Transaction $transaction): array
    {
        // 1. فحص القوائم السوداء (حظر فوري ومباشر)
        if ($this->isBlacklisted($transaction)) {
            $transaction->update(['status' => 'blocked']);

            $alert = $this->recordEvaluationAndAlert(
                $transaction,
                100.0,
                'block',
                'critical',
                ['rule_score' => 100, 'matched_rules' => ['Blacklist Hit']],
                ['probability' => 1.0, 'used_fallback' => false, 'features' => []]
            );

            return [
                'decision' => 'block',
                'risk_score' => 100,
                'severity' => 'critical',
                'alert_id' => $alert?->id,
            ];
        }

        // 2. فحص القوائم البيضاء (سماح فوري ومباشر)
        if ($this->isWhitelisted($transaction)) {
            $transaction->update(['status' => 'approved']);

            $this->recordEvaluationAndAlert(
                $transaction,
                0.0,
                'allow',
                'low',
                ['rule_score' => 0, 'matched_rules' => ['Whitelist Hit']],
                ['probability' => 0.0, 'used_fallback' => false, 'features' => []]
            );

            return [
                'decision' => 'allow',
                'risk_score' => 0,
                'severity' => 'low',
                'alert_id' => null,
            ];
        }

        // 3. جلب معايير المحرك الحية من الإعدادات
        $config = $this->getEngineConfigurations();

        // 4. تقييم محرك القواعد
        $ruleResult = $this->ruleEngine->evaluate($transaction);
        $ruleScore = (float) ($ruleResult['rule_score'] ?? 0);

        // 5. تقييم نموذج الذكاء الاصطناعي (أو تجاوزه إن كانت الخدمة معطلة)
        if ($config['ml_service_enabled']) {
            $mlResult = $this->mlService->predict($transaction);
            $mlScore = ((float) ($mlResult['probability'] ?? 0)) * 100;
            
            // احتساب الدرجة الهجينة وفق الأوزان
            $finalScore = ($ruleScore * $config['weight_rule_engine']) + ($mlScore * $config['weight_ml_engine']);
        } else {
            $mlResult = [
                'probability' => 0.0,
                'used_fallback' => true,
                'features' => ['ml_disabled_by_settings' => true],
            ];
            // الاعتماد الكامل على القواعد
            $finalScore = $ruleScore;
        }

        $finalScore = min(100.0, max(0.0, $finalScore));

        // 6. اتخاذ القرار التلقائي استناداً إلى العتبات المحفوظة
        if ($finalScore >= $config['threshold_block']) {
            $decision = 'block';
            $transactionStatus = 'blocked';
        } elseif ($finalScore >= $config['threshold_review']) {
            $decision = 'review';
            $transactionStatus = 'under_review';
        } else {
            $decision = 'allow';
            $transactionStatus = 'approved';
        }

        // 7. تحديد مستوى الخطورة / أولوية التنبيه
        if ($finalScore >= $config['threshold_critical_alert']) {
            $severity = 'critical';
        } elseif ($finalScore >= $config['threshold_high_alert']) {
            $severity = 'high';
        } elseif ($finalScore >= $config['threshold_medium_alert']) {
            $severity = 'medium';
        } else {
            $severity = 'low';
        }

        $transaction->update(['status' => $transactionStatus]);

        // 8. تسجيل التقييم في risk_scores وتوليد التنبيه
        $alert = $this->recordEvaluationAndAlert(
            $transaction,
            $finalScore,
            $decision,
            $severity,
            $ruleResult,
            $mlResult
        );

        return [
            'decision' => $decision,
            'risk_score' => round($finalScore, 2),
            'severity' => $severity,
            'alert_id' => $alert?->id,
        ];
    }

    /**
     * جلب إعدادات المحرك مع التخزين المؤقت لتحقيق أعلى أداء
     */
    protected function getEngineConfigurations(): array
    {
        return Cache::remember('fraud_engine_active_settings', 60, function () {
            $defaults = [
                'weight_rule_engine' => 0.50,
                'weight_ml_engine' => 0.50,
                'threshold_block' => 80,
                'threshold_review' => 50,
                'threshold_critical_alert' => 85,
                'threshold_high_alert' => 65,
                'threshold_medium_alert' => 40,
                'ml_service_enabled' => true,
            ];

            $saved = Setting::whereIn('key', array_keys($defaults))
                ->pluck('value', 'key')
                ->toArray();

            $merged = [];
            foreach ($defaults as $key => $defaultVal) {
                if (isset($saved[$key])) {
                    $merged[$key] = is_numeric($defaultVal) ? (float) $saved[$key] : (bool) $saved[$key];
                } else {
                    $merged[$key] = $defaultVal;
                }
            }

            return $merged;
        });
    }

    /**
     * حفظ نتيجة تقييم المخاطر وتوليد سجل التنبيه
     */
    protected function recordEvaluationAndAlert(
        Transaction $transaction,
        float $score,
        string $decision,
        string $severity,
        array $ruleResult,
        array $mlResult
    ): ?Alert {
        $riskScore = RiskScore::create([
            'transaction_id' => $transaction->id,
            'score' => (int) round($score),
            'decision' => $decision,
            'rule_score' => (int) round($ruleResult['rule_score'] ?? 0),
            'model_probability' => $mlResult['probability'] ?? 0.0,
            'used_fallback' => $mlResult['used_fallback'] ?? false,
            'reasons' => [
                'matched_rules' => $ruleResult['matched_rules'] ?? [],
                'ml_features' => $mlResult['features'] ?? [],
            ],
            'evaluated_at' => now(),
        ]);

        if (in_array($decision, ['review', 'otp', 'block']) || $severity !== 'low') {
            return Alert::create([
                'risk_score_id' => $riskScore->id,
                'status' => 'new',
                'priority' => match ($severity) {
                    'critical' => 'critical',
                    'high' => 'high',
                    'medium' => 'medium',
                    default => 'low',
                },
            ]);
        }

        return null;
    }

    protected function isBlacklisted(Transaction $transaction): bool
    {
        return Watchlist::where('list_type', 'blacklist')
            ->where(function ($query) use ($transaction) {
                $query->where(function ($q) use ($transaction) {
                    $q->where('entity_type', 'account')
                      ->where('entity_value', (string) $transaction->account_id);
                })
                ->orWhere(function ($q) use ($transaction) {
                    $q->where('entity_type', 'ip')
                      ->where('entity_value', $transaction->ip_address);
                });

                if ($transaction->beneficiary_id) {
                    $query->orWhere(function ($q) use ($transaction) {
                        $q->where('entity_type', 'beneficiary')
                          ->where('entity_value', (string) $transaction->beneficiary_id);
                    });
                }

                if ($transaction->device_id) {
                    $query->orWhere(function ($q) use ($transaction) {
                        $q->where('entity_type', 'device')
                          ->where('entity_value', (string) $transaction->device_id);
                    });
                }
            })
            ->exists();
    }

    protected function isWhitelisted(Transaction $transaction): bool
    {
        return Watchlist::where('list_type', 'whitelist')
            ->where(function ($query) use ($transaction) {
                $query->where(function ($q) use ($transaction) {
                    $q->where('entity_type', 'account')
                      ->where('entity_value', (string) $transaction->account_id);
                })
                ->orWhere(function ($q) use ($transaction) {
                    $q->where('entity_type', 'ip')
                      ->where('entity_value', $transaction->ip_address);
                });
            })
            ->exists();
    }
}