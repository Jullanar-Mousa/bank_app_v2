<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Rule as FraudRule;
use App\Models\RuleSuggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuleSuggestionReviewController extends Controller
{
    public function index(): View
    {
        $suggestions = RuleSuggestion::with('suggestedBy')
            ->latest()
            ->paginate(15);

        return view('supervisor.rule-suggestions.index', compact('suggestions'));
    }

    public function approve(RuleSuggestion $suggestion): RedirectResponse
    {
        $suggestion->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // تحويل المقترح المعتمد تلقائياً إلى قاعدة نشطة
        FraudRule::create([
            'name' => $suggestion->name,
            'description' => $suggestion->description,
            'weight' => 20.0,
            'priority' => 10,
            'conditions' => $suggestion->conditions,
            'action' => $suggestion->action,
            'active' => true,
        ]);

        return back()->with('success', 'تم اعتماد المقترح وإضافته كقاعدة نشطة في النظام.');
    }

    public function reject(Request $request, RuleSuggestion $suggestion): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $suggestion->update([
            'status' => 'rejected',
            'supervisor_notes' => $validated['notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'تم رفض مقترح القاعدة.');
    }
}