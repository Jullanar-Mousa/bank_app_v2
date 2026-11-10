<?php

namespace App\Http\Controllers\Analyst;

use App\Http\Controllers\Controller;
use App\Models\AiProposal;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiProposalController extends Controller
{
    /**
     * عرض مقترحات وتوصيات الذكاء الاصطناعي
     */
    public function index(Request $request): View
    {
        $query = AiProposal::with(['alert.transaction.account', 'fraudCase', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status',$request->input('status'));
        } else {
            // افتراضياً عرض المقترحات قيد الانتظار أولاً ثم المراجعة
            $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')");
        }

        $proposals =$query->latest('id')->paginate(15)->withQueryString();

        return view('analyst.ai-proposals.index', compact('proposals'));
    }

    /**
     * اعتماد مقترح الذكاء الاصطناعي وتنفيذه
     */
    public function approve(AiProposal $proposal): RedirectResponse
    {
        $oldStatus =$proposal->status;

        $proposal->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLogService::log(
            action: 'AI_PROPOSAL_APPROVED',
            entity: $proposal,
            oldValues: ['status' => $oldStatus],
            newValues: [
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'tool_name' => $proposal->tool_name,
            ]
        );

        return back()->with('status', 'تمت الموافقة على مقترح الذكاء الاصطناعي واعتماده بنجاح.');
    }

    /**
     * رفض مقترح الذكاء الاصطناعي
     */
    public function reject(Request $request, AiProposal$proposal): RedirectResponse
    {
        $oldStatus =$proposal->status;

        $payload =$proposal->payload ?? [];
        if ($request->filled('reason')) {
            $payload['rejection_reason'] =$request->input('reason');
        }

        $proposal->update([
            'status' => 'rejected',
            'payload' => $payload,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLogService::log(
            action: 'AI_PROPOSAL_REJECTED',
            entity: $proposal,
            oldValues: ['status' => $oldStatus],
            newValues: [
                'status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'tool_name' => $proposal->tool_name,
            ]
        );

        return back()->with('status', 'تم رفض مقترح الذكاء الاصطناعي وحفظ السبب.');
    }
}