<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AccountAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $pendingActions = AccountAction::with('requestedBy')
            ->where('status', 'pending_approval')
            ->latest()
            ->paginate(15);

        return view('supervisor.approvals.index', compact('pendingActions'));
    }

    public function decide(Request $request, AccountAction $approval): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'supervisor_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $approval->update([
            'status' => $validated['decision'],
            'supervisor_notes' => $validated['supervisor_notes'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'تم تسجيل اعتماد الإجراء بنجاح.');
    }
}