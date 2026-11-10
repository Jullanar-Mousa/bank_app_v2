<?php

namespace App\Http\Controllers\Analyst;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountAction;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountActionController extends Controller
{
    /**
     * عرض قائمة طلبات وإجراءات تجميد وتقييد الحسابات
     */
    public function index(Request $request): View
    {
        $query = AccountAction::with(['account.customer', 'requester', 'alert']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $actions = $query->latest('id')->paginate(15)->withQueryString();

        // جلب قائمة الحسابات لتمكين المحلل من الاختيار منها في النموذج
        $accounts = Account::with('customer')->latest('id')->take(50)->get();

        return view('analyst.account-actions.index', compact('actions', 'accounts'));
    }

    /**
     * رفع طلب تجميد / تقييد حساب إلى المشرف للاعتماد
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'action' => ['required', 'in:freeze,restrict,monitor,unfreeze'],
            'reason' => ['required', 'string', 'max:1000'],
            'alert_id' => ['nullable', 'exists:alerts,id'],
        ]);

        $actionRecord = AccountAction::create([
            'account_id' => $validated['account_id'],
            'action' => $validated['action'],
            'reason' => $validated['reason'],
            'alert_id' => $validated['alert_id'] ?? null,
            'requested_by' => auth()->id(),
            'status' => 'pending_approval',
        ]);

        // توثيق الطلب في سجل التدقيق المصرفي
        AuditLogService::log(
            action: 'ACCOUNT_ACTION_REQUESTED',
            entity: $actionRecord,
            newValues: [
                'account_id' => $actionRecord->account_id,
                'action' => $actionRecord->action,
                'reason' => $actionRecord->reason,
                'status' => 'pending_approval',
            ]
        );

        return back()->with('status', 'تم رفع طلب اتخاذ الإجراء بنجاح بانتظار اعتماد المشرف.');
    }
}