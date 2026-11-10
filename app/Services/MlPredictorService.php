<?php

namespace App\Services;

use App\Models\MlModel;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MlPredictorService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ai.url', env('AI_SERVICE_URL', 'http://127.0.0.1:8001'));
        $this->timeout = (int) config('services.ai.timeout', env('AI_SERVICE_TIMEOUT', 3));
    }

    /**
     * Get fraud prediction probability from the external ML service.
     */
    public function predict(Transaction $transaction): array
    {
        $activeModel = MlModel::where('status', 'active')->latest()->first();
        $modelName = $activeModel ? $activeModel->name : 'default_xgb_model';
        $modelVersion = $activeModel ? $activeModel->version : 'v1.0';

        $features = $this->extractFeatures($transaction);

        try {
            $response = Http::timeout($this->timeout)->post("{$this->baseUrl}/predict", [
                'model_name' => $modelName,
                'features' => $features,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'probability' => (float) ($data['fraud_probability'] ?? 0.0),
                    'risk_score' => round(((float) ($data['fraud_probability'] ?? 0.0)) * 100, 2),
                    'model_name' => $modelName,
                    'model_version' => $modelVersion,
                    'status' => 'success',
                ];
            }

            Log::warning('AI Service returned non-200 status: ' . $response->status(), [
                'transaction_id' => $transaction->id,
                'response' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AI Service connection failed: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
            ]);
        }

        // Fallback في حال تعطل خدمة بايثون أو تجاوز المهلة
        return [
            'probability' => 0.0,
            'risk_score' => 0.0,
            'model_name' => $modelName,
            'model_version' => $modelVersion,
            'status' => 'fallback',
        ];
    }

    /**
     * Extract analytical features needed by the ML model.
     */
    protected function extractFeatures(Transaction $transaction): array
    {
        // حساب إجمالي معاملات الحساب السابقة
        $historicalCount = Transaction::where('account_id', $transaction->account_id)
            ->where('id', '!=', $transaction->id)
            ->count();

        // حساب متوسط مبالغ الحساب السابقة
        $averageAmount = Transaction::where('account_id', $transaction->account_id)
            ->where('id', '!=', $transaction->id)
            ->avg('amount') ?: (float) $transaction->amount;

        $amountRatio = $averageAmount > 0 ? ((float) $transaction->amount / $averageAmount) : 1.0;

        return [
            'amount' => (float) $transaction->amount,
            'channel' => $transaction->channel,
            'historical_count' => $historicalCount,
            'average_amount' => round($averageAmount, 2),
            'amount_ratio' => round($amountRatio, 4),
            'hour_of_day' => (int) ($transaction->created_at ? $transaction->created_at->format('H') : now()->format('H')),
            'day_of_week' => (int) ($transaction->created_at ? $transaction->created_at->format('N') : now()->format('N')),
        ];
    }
}