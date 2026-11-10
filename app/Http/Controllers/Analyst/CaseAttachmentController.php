<?php

namespace App\Http\Controllers\Analyst;

use App\Http\Controllers\Controller;
use App\Models\CaseAttachment;
use App\Models\FraudCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CaseAttachmentController extends Controller
{
    public function store(Request $request, FraudCase $case): RedirectResponse
    {
        $request->validate([
            'attachment' => ['required', 'file', 'max:5120'], // 5MB Max
        ]);

        $file = $request->file('attachment');
        $path = $file->store('case_attachments', 'public');

        $case->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('analyst.cases.show', $case)->with('success', 'تم رفع المرفق بنجاح.');
    }

    public function destroy(CaseAttachment $attachment): RedirectResponse
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'تم حذف المرفق.');
    }
}   