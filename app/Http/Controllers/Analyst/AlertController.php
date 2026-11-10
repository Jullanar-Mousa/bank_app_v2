<?php

namespace App\Http\Controllers\Analyst;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    /**
     * عرض قائمة التنبيهات
     */
    public function index(Request $request): View
    {
        $query = Alert::with(['riskScore.transaction.account', 'transaction', 'assignee', 'fraudCase']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->boolean('only_mine')) {
            $query->where('assigned_to', auth()->id());
        }

        $alerts = $query->latest('id')->paginate(15)->withQueryString();

        return view('analyst.alerts.index', compact('alerts'));
    }

    /**
     * عرض تفاصيل التنبيه وغرفة الفحص
     */
    public function show(Alert $alert): View
    {
        $alert->load(['riskScore.transaction.account', 'transaction', 'assignee', 'fraudCase', 'labels']);

        return view('analyst.alerts.show', compact('alert'));
    }

    /**
     * اتخاذ إجراء على التنبيه بواسطة المحلل
     */
    public function update(Request $request, Alert $alert): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:new,in_review,escalated,closed',
            'decision' => 'nullable|in:confirmed_fraud,false_positive,suspicious,dismissed',
            'decision_reason' => 'nullable|string|max:1000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldValues = [
            'status' => $alert->status,
            'decision' => $alert->decision,
            'assigned_to' => $alert->assigned_to,
        ];

        if (!empty($validated['decision'])) {
            $validated['decided_by'] = auth()->id();
            $validated['decided_at'] = now();
        }

        $alert->update($validated);

        // مزامنة حالة المعاملة إذا كان القرار حاسماً
        $transaction = $alert->transaction ?? $alert->riskScore?->transaction;
        if ($transaction && !empty($validated['decision'])) {
            if ($validated['decision'] === 'confirmed_fraud') {
                $transaction->update(['status' => 'blocked']);
            } elseif ($validated['decision'] === 'false_positive') {
                $transaction->update(['status' => 'approved']);
            }
        }

        // تسجيل الإجراء في سجل التدقيق الأمني
        AuditLogService::log(
            action: 'ALERT_DECISION_UPDATED',
            entity: $alert,
            oldValues: $oldValues,
            newValues: $validated
        );

        return redirect()->route('analyst.alerts.show', $alert)->with('status', 'تم حفظ قرار الفحص وتحديث حالة التنبيه بنجاح.');
    }
}