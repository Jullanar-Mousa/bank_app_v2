<?php

namespace App\Http\Controllers\Analyst;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\FraudCase;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaseController extends Controller
{
    /**
     * عرض جميع قضايا الاحتيال المجمعة
     */
    public function index(Request $request): View
    {
        $query = FraudCase::with(['assignee'])->withCount('alerts');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $cases = $query->latest('id')->paginate(15)->withQueryString();

        return view('analyst.cases.index', compact('cases'));
    }

    /**
     * عرض تفاصيل القضية والتنبيهات والمرفقات والملاحظات المرتبطة بها
     */
    public function show(FraudCase $case): View
    {
        $case->load([
            'assignee',
            'alerts.riskScore.transaction.account',
            'alerts.transaction',
            'notes.user',
            'attachments',
        ]);

        // جلب المحللين والمسؤولين لإمكانية إعادة إسناد القضية
        $analysts = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['fraud_analyst', 'admin', 'supervisor'])
              ->orWhereIn('role_name', ['fraud_analyst', 'admin', 'supervisor']);
        })->get();

        if ($analysts->isEmpty()) {
            $analysts = User::all();
        }

        return view('analyst.cases.show', compact('case', 'analysts'));
    }

    /**
     * تصعيد تنبيه (Alert) وتحويله إلى قضية جديدة أو ربطه بقضية حالية
     */
    public function escalateFromAlert(Request $request, Alert $alert): RedirectResponse
    {
        $validated = $request->validate([
            'action_type' => 'required|in:new_case,existing_case',
            'case_id' => 'nullable|required_if:action_type,existing_case|exists:cases,id',
            'title' => 'nullable|required_if:action_type,new_case|string|max:255',
            'priority' => 'nullable|in:low,medium,high,critical',
        ]);

        if ($validated['action_type'] === 'new_case') {
            $case = FraudCase::create([
                'title' => $validated['title'] ?? ('قضية اشتباه رقم #' . $alert->id),
                'assigned_to' => auth()->id(),
                'status' => 'open',
                'priority' => $validated['priority'] ?? $alert->priority,
            ]);

            AuditLogService::log(
                action: 'CASE_CREATED',
                entity: $case,
                newValues: [
                    'title' => $case->title,
                    'priority' => $case->priority,
                    'assigned_to' => $case->assigned_to,
                ]
            );
        } else {
            $case = FraudCase::findOrFail($validated['case_id']);
        }

        // ربط التنبيه بالقضية وتحديث حالته
        $oldAlertStatus = $alert->status;
        $alert->update([
            'case_id' => $case->id,
            'status' => 'escalated',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        AuditLogService::log(
            action: 'ALERT_ESCALATED_TO_CASE',
            entity: $alert,
            oldValues: ['status' => $oldAlertStatus, 'case_id' => null],
            newValues: ['status' => 'escalated', 'case_id' => $case->id]
        );

        return redirect()->route('analyst.cases.show', $case)->with(
            'status',
            "تمت إحالة التنبيه #{$alert->id} إلى القضية بنجاح."
        );
    }

    /**
     * تحديث حالة وبيانات القضية
     */
    public function update(Request $request, FraudCase $case): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,investigating,escalated_to_legal,closed_fraud,closed_clean',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldValues = [
            'status' => $case->status,
            'priority' => $case->priority,
            'assigned_to' => $case->assigned_to,
        ];

        $case->update($validated);

        AuditLogService::log(
            action: 'CASE_UPDATED',
            entity: $case,
            oldValues: $oldValues,
            newValues: $validated
        );

        return redirect()->route('analyst.cases.show', $case)->with('status', 'تم تحديث حالة وبيانات القضية بنجاح.');
    }
}