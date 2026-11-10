<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuleController extends Controller
{
    /**
     * عرض قائمة القواعد الأمنية
     */
    public function index(): View
    {
        $rules = Rule::orderBy('priority', 'asc')->paginate(15);

        return view('rules.index', compact('rules'));
    }

    /**
     * صفحة إنشاء قاعدة جديدة
     */
    public function create(): View
    {
        return view('rules.form', ['rule' => new Rule()]);
    }

    /**
     * حفظ قاعدة جديدة في قاعدة البيانات
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rule_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|integer|min:1|max:100',
            'priority' => 'required|integer|min:1|max:1000',
            'action' => 'required|in:flag,review,otp,block',
            'is_active' => 'nullable|boolean',
            'conditions_json' => 'nullable|string',
        ]);

        $conditions = [];
        if (!empty($validated['conditions_json'])) {
            $decoded = json_decode($validated['conditions_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $conditions = $decoded;
            }
        }

        Rule::create([
            'rule_name' => $validated['rule_name'],
            'description' => $validated['description'],
            'weight' => $validated['weight'],
            'priority' => $validated['priority'],
            'action' => $validated['action'],
            'is_active' => $request->boolean('is_active'),
            'conditions' => $conditions,
        ]);

        return redirect()->route('rules.index')->with('status', 'تمت إضافة القاعدة الأمنية بنجاح.');
    }

    /**
     * صفحة تعديل قاعدة حالية
     */
    public function edit(Rule $rule): View
    {
        return view('rules.form', compact('rule'));
    }

    /**
     * تحديث بيانات القاعدة
     */
    public function update(Request $request, Rule $rule): RedirectResponse
    {
        $validated = $request->validate([
            'rule_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|integer|min:1|max:100',
            'priority' => 'required|integer|min:1|max:1000',
            'action' => 'required|in:flag,review,otp,block',
            'is_active' => 'nullable|boolean',
            'conditions_json' => 'nullable|string',
        ]);

        $conditions = [];
        if (!empty($validated['conditions_json'])) {
            $decoded = json_decode($validated['conditions_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $conditions = $decoded;
            }
        }

        $rule->update([
            'rule_name' => $validated['rule_name'],
            'description' => $validated['description'],
            'weight' => $validated['weight'],
            'priority' => $validated['priority'],
            'action' => $validated['action'],
            'is_active' => $request->boolean('is_active'),
            'conditions' => $conditions,
        ]);

        return redirect()->route('rules.index')->with('status', 'تم تحديث القاعدة بنجاح.');
    }

    /**
     * تبديل سريع لحالة التفعيل (Active/Inactive)
     */
    public function toggleStatus(Rule $rule): RedirectResponse
    {
        $rule->update([
            'is_active' => ! $rule->is_active,
        ]);

        $statusText = $rule->is_active ? 'تفعيل' : 'تعطيل';

        return redirect()->route('rules.index')->with('status', "تم {$statusText} القاعدة بنجاح.");
    }

    /**
     * حذف قاعدة
     */
    public function destroy(Rule $rule): RedirectResponse
    {
        $rule->delete();

        return redirect()->route('rules.index')->with('status', 'تم حذف القاعدة بنجاح.');
    }
}