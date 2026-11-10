<?php

namespace App\Http\Controllers\Analyst;

use App\Http\Controllers\Controller;
use App\Models\CaseNote;
use App\Models\FraudCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CaseNoteController extends Controller
{
    public function store(Request $request, FraudCase $case): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'min:3'],
        ]);

        $case->notes()->create([
            'user_id' => auth()->id(),
            'note' => $validated['note'],
        ]);

        return redirect()->route('analyst.cases.show', $case)->with('success', 'تمت إضافة الملاحظة بنجاح.');
    }
}