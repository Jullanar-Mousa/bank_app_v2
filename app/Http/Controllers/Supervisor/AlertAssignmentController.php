<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlertAssignmentController extends Controller
{
    public function update(Request $request, Alert $alert): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $alert->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'assigned',
        ]);

        return back()->with('success', 'تم إسناد التنبيه للمحلل بنجاح.');
    }
}