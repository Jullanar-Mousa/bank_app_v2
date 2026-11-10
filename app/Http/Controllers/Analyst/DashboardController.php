<?php

namespace App\Http\Controllers\Analyst;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\FraudCase;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $userId = auth()->id();

        $stats = [
            'my_open_alerts' => Alert::where('assigned_to', $userId)
                ->whereIn('status', ['assigned', 'in_progress', 'in_review'])
                ->count(),
            'unassigned_alerts' => Alert::whereNull('assigned_to')
                ->where('status', 'new')
                ->count(),
            'my_active_cases' => FraudCase::where('assigned_to', $userId)
                ->whereIn('status', ['open', 'investigating'])
                ->count(),
            'critical_alerts' => Alert::where('priority', 'critical')
                ->whereIn('status', ['new', 'assigned', 'in_review'])
                ->count(),
        ];

        $urgentAlerts = Alert::with(['riskScore.transaction', 'transaction'])
            ->whereIn('status', ['new', 'assigned', 'in_review'])
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->latest('id')
            ->take(8)
            ->get();

        return view('analyst.dashboard', compact('stats', 'urgentAlerts'));
    }
}