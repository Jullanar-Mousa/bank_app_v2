<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\RiskScore;
use App\Models\Transaction;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertToolController extends Controller
{
    /** get_alerts: قراءة فقط */
    public function index(Request $request): JsonResponse
    {
        $rows = Alert::query()
            ->leftJoin('risk_scores', 'risk_scores.id', '=', 'alerts.risk_score_id')
            ->select('alerts.id', 'alerts.status', 'alerts.priority', 'risk_scores.score')
            ->orderByDesc('risk_scores.score')
            ->limit(20)
            ->get();

        Audit::log($request, 'mcp.alerts_list');

        return response()->json($rows);
    }

    /** get_alert_details: بدون بيانات شخصية (masking) */
    public function show(Request $request, Alert $alert): JsonResponse
    {
        $score = $alert->risk_score_id ? RiskScore::find($alert->risk_score_id) : null;
        $txn   = $score?->transaction_id ? Transaction::find($score->transaction_id) : null;

        Audit::log($request, 'mcp.alert_details', $alert);

        return response()->json([
            'alert' => $alert->only(['id', 'status', 'priority', 'case_id', 'created_at']),
            'score' => $score?->only(['score', 'decision', 'rule_score', 'model_probability']),
            'transaction' => $txn?->only(['amount', 'channel', 'city', 'transaction_time']),
        ]);
    }

    /** explain_score */
    public function explain(Request $request, Alert $alert): JsonResponse
    {
        $score = $alert->risk_score_id ? RiskScore::find($alert->risk_score_id) : null;

        abort_if(! $score, 404, 'ما في درجة مرتبطة بهالتنبيه.');

        Audit::log($request, 'mcp.explain_score', $alert);

        return response()->json([
            'score'    => $score->score,
            'decision' => $score->decision,
            'reasons'  => $score->reasons,
        ]);
    }
}