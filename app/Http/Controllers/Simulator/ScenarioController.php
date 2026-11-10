<?php

namespace App\Http\Controllers\Simulator;

use App\Http\Controllers\Controller;
use App\Models\SimulationScenario;
use App\Models\Transaction;
use App\Services\FraudDecisionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    /**
     * عرض السيناريوهات المتاحة
     */
    public function index(): View
    {
        $scenarios = SimulationScenario::all();

        return view('simulator.index', compact('scenarios'));
    }

    /**
     * تشغيل سيناريو محدد واختبار المحرك
     */
    public function run(Request $request, SimulationScenario $scenario, FraudDecisionService $decisionService): RedirectResponse
    {
        $payload = $scenario->payload ?? [];

        // إنشاء المعاملة بالحقول المتوافقة مع بنية جدول transactions
        $transaction = Transaction::create([
            'account_id' => is_numeric($payload['account_id'] ?? null) ? (int) $payload['account_id'] : 1,
            'beneficiary_id' => is_numeric($payload['beneficiary_id'] ?? null) ? (int) $payload['beneficiary_id'] : 2,
            'device_id' => is_numeric($payload['device_id'] ?? null) ? (int) $payload['device_id'] : null,
            'amount' => $payload['amount'] ?? 1000.0,
            'channel' => $payload['channel'] ?? 'web',
            'ip_address' => $payload['ip_address'] ?? '127.0.0.1',
            'city' => $payload['city'] ?? 'Damascus',
            'status' => 'pending',
            'transaction_time' => now(),
            'idempotency_key' => (string) Str::uuid(),
        ]);

        // تقييم المعاملة عبر المحرك
        $result = $decisionService->process($transaction);

        return redirect()->route('simulator.scenarios.index')->with(
            'status',
            "تم تنفيذ السيناريو بنجاح! القرار المتخذ: [{$result['decision']}] — درجة الخطورة: {$result['risk_score']}%" .
            ($result['alert_id'] ? " (تم توليد تنبيه برقم #{$result['alert_id']})" : '')
        );
    }
}