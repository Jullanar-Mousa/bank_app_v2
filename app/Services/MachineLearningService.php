<?php

namespace App\Services;

use App\Models\MlModel;
use App\Models\Transaction;

class MachineLearningService
{
    /**
     * تقييم المعاملة وتقدير احتمالية الاحتيال (0.0 إلى 1.0)
     */
    public function predict(Transaction $transaction): array
    {
        $amount = (float) $transaction->amount;
        $hasNoDevice = is_null($transaction->device_id);

        $features = [
            'amount' => $amount,
            'channel' => $transaction->channel,
            'has_device' => ! $hasNoDevice,
            'hour' => (int) ($transaction->transaction_time ? $transaction->transaction_time->format('H') : now()->format('H')),
        ];

        $activeModel = MlModel::where('status', 'active')->latest()->first();

        // حساب احتمالية تجريبية مبنية على الخصائص (Fallback Logic)
        $probability = 0.05;

        if ($amount > 5000000) {
            $probability += 0.55;
        } elseif ($amount > 1000000) {
            $probability += 0.30;
        }

        if ($hasNoDevice) {
            $probability += 0.15;
        }

        $probability = min(0.99, max(0.01, $probability));

        return [
            'probability' => round($probability, 4),
            'used_fallback' => is_null($activeModel),
            'model_id' => $activeModel?->id,
            'features' => $features,
        ];
    }
}