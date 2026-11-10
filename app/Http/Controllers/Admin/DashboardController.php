<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\FraudCase;
use App\Models\RiskScore;
use App\Models\Rule;
use App\Models\Transaction;
use App\Models\Watchlist;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // 1. الإحصائيات العامة للمعاملات
        $totalTransactions = Transaction::count();
        $blockedTransactions = Transaction::where('status', 'blocked')->count();
        $underReviewTransactions = Transaction::where('status', 'under_review')->count();
        $approvedTransactions = Transaction::where('status', 'approved')->count();

        // 2. إجمالي المبالغ المحمية (المحظورة)
        $preventedFraudAmount = Transaction::where('status', 'blocked')->sum('amount');

        // 3. إحصائيات التنبيهات الأمنية
        $totalAlerts = Alert::count();
        $newAlerts = Alert::where('status', 'new')->count();
        $criticalAlerts = Alert::where('priority', 'critical')->count();
        $highAlerts = Alert::where('priority', 'high')->count();

        // 4. إحصائيات القضايا
        $totalCases = FraudCase::count();
        $openCases = FraudCase::whereIn('status', ['open', 'investigating'])->count();

        // 5. قواعد الرصد والقوائم
        $activeRulesCount = Rule::where('is_active', true)->count();
        $blacklistCount = Watchlist::where('list_type', 'blacklist')->count();

        // 6. آخر التنبيهات الحرجة المسجلة
        $recentAlerts = Alert::with(['riskScore.transaction'])
            ->latest()
            ->take(6)
            ->get();

        // 7. آخر المعاملات المقيمة مؤخراً
        $recentScores = RiskScore::with(['transaction'])
            ->latest('evaluated_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalTransactions',
            'blockedTransactions',
            'underReviewTransactions',
            'approvedTransactions',
            'preventedFraudAmount',
            'totalAlerts',
            'newAlerts',
            'criticalAlerts',
            'highAlerts',
            'totalCases',
            'openCases',
            'activeRulesCount',
            'blacklistCount',
            'recentAlerts',
            'recentScores'
        ));
    }
}