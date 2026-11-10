<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\FraudCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $reports = FraudCase::where('created_by', auth()->id())->latest()->paginate(10);
        return view('customer.reports.index', compact('reports'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        FraudCase::create([
            'case_number' => 'REP-' . strtoupper(Str::random(8)),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => 'medium',
            'status' => 'open',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('customer.reports.index')->with('success', 'تم استلام بلاغك وسيقوم الفريق بمتابعته.');
    }
}