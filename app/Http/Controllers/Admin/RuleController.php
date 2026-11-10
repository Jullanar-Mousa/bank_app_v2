<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rule as FraudRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $rules = FraudRule::orderBy('priority', 'asc')->get();

        return view('admin.rules.index', compact('rules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.rules.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:rules,name'],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'priority' => ['required', 'integer', 'min:1'],
            'conditions' => ['required', 'json'],
            'action' => ['required', 'string', 'in:allow,otp,block,review'],
            'active' => ['boolean'],
        ]);

        FraudRule::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'weight' => $validated['weight'],
            'priority' => $validated['priority'],
            'conditions' => json_decode($validated['conditions'], true),
            'action' => $validated['action'],
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('admin.rules.index')->with('success', 'تم إنشاء القاعدة بنجاح.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FraudRule $rule): View
    {
        return view('admin.rules.show', compact('rule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FraudRule $rule): View
    {
        return view('admin.rules.edit', compact('rule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FraudRule $rule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:rules,name,' . $rule->id],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'priority' => ['required', 'integer', 'min:1'],
            'conditions' => ['required', 'json'],
            'action' => ['required', 'string', 'in:allow,otp,block,review'],
            'active' => ['boolean'],
        ]);

        $rule->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'weight' => $validated['weight'],
            'priority' => $validated['priority'],
            'conditions' => json_decode($validated['conditions'], true),
            'action' => $validated['action'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()->route('admin.rules.index')->with('success', 'تم تحديث القاعدة بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FraudRule $rule): RedirectResponse
    {
        $rule->delete();

        return redirect()->route('admin.rules.index')->with('success', 'تم حذف القاعدة بنجاح.');
    }
}