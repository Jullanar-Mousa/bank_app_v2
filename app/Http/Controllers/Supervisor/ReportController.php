<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $alertsCount = Alert::whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])->count();
        $confirmedFraud = Alert::where('decision', 'confirmed_fraud')
            ->whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])
            ->count();
        $falsePositives = Alert::where('decision', 'false_positive')
            ->whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])
            ->count();

        $totalTransactions = Transaction::whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])->count();

        $metrics = [
            'total_alerts' => $alertsCount,
            'confirmed_fraud' => $confirmedFraud,
            'false_positives' => $falsePositives,
            'total_transactions' => $totalTransactions,
            'fraud_rate' => $totalTransactions > 0 ? round(($confirmedFraud / $totalTransactions) * 100, 3) : 0,
        ];

        return view('supervisor.reports.index', compact('metrics', 'dateFrom', 'dateTo'));
    }
}