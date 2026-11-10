<?php

namespace App\Http\Controllers\Analyst;

use App\Http\Controllers\Controller;
use App\Models\RuleSuggestion;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuleSuggestionController extends Controller
{
    /**
     * عرض قائمة القواعد المقترحة وحالات اعتمادها
     */
    public function index(Request $request): View
    {
        $query = RuleSuggestion::with(['suggester', 'reviewer', 'rule']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $suggestions = $query->latest('id')->paginate(15)->withQueryString();

        return view('analyst.rule-suggestions.index', compact('suggestions'));
    }

    /**
     * تقديم مقترح قاعدة جديدة بواسطة المحلل
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'action_recommendation' => ['required', 'in:allow,otp,review,block'],
            'conditions_text' => ['nullable', 'string', 'max:2000'],
        ]);

        $conditions = [
            'action' => $validated['action_recommendation'],
            'rules_summary' => $validated['conditions_text'] ?? '',
        ];

        $suggestion = RuleSuggestion::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'conditions' => $conditions,
            'suggested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        AuditLogService::log(
            action: 'RULE_SUGGESTION_CREATED',
            entity: $suggestion,
            newValues: [
                'title' => $suggestion->title,
                'status' => 'pending',
                'suggested_by' => auth()->id(),
            ]
        );

        return back()->with('status', 'تم رفع مقترح القاعدة الرقابية بنجاح بانتظار مراجعة إدارة النظام.');
    }
}