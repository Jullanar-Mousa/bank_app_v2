<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if (! $user) {
            return view('welcome');
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'هذا الحساب معطّل. يرجى التواصل مع الإدارة.',
            ]);
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('fraud_analyst')) {
            return redirect()->route('analyst.dashboard');
        }

        if ($user->hasRole('supervisor')) {
            return redirect()->route('supervisor.dashboard');
        }

        if ($user->hasRole('customer')) {
            return redirect()->route('customer.dashboard');
        }

        return view('welcome');
    }
}