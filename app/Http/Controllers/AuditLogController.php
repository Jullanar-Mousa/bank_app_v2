<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest('id');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', 'like', '%' . $request->input('entity_type') . '%');
        }

        $logs = $query->paginate(20)->withQueryString();
        $users = User::select('id', 'name', 'email')->get();

        return view('audit_logs.index', compact('logs', 'users'));
    }
}