<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AccountAction;
use App\Models\Alert;
use App\Models\FraudCase;
use App\Models\RuleSuggestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = [
            'unassigned_alerts' => Alert::whereNull('assigned_to')->where('status', 'new')->count(),
            'pending_approvals' => AccountAction::where('status', 'pending_approval')->count(),
            'pending_suggestions' => RuleSuggestion::where('status', 'pending')->count(),
            'active_cases' => FraudCase::whereIn('status', ['open', 'under_investigation'])->count(),
        ];

        $analysts = User::whereHas('roles', function ($q) {
            $q->where('name', 'fraud_analyst');
        })->withCount([
            'assignedAlerts as open_alerts_count' => function ($q) {
                $q->whereIn('status', ['assigned', 'in_progress']);
            },
        ])->get();

        $recentPendingAlerts = Alert::with('transaction')
            ->where('status', 'new')
            ->latest()
            ->take(6)
            ->get();

        return view('supervisor.dashboard', compact('stats', 'analysts', 'recentPendingAlerts'));
    }
}