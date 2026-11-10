<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OtpChallenge;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $accountNumber = auth()->user()->account_number ?? auth()->user()->id;

        $recentTransactions = Transaction::where('account_id', $accountNumber)
            ->latest()
            ->take(5)
            ->get();

        $pendingOtp = OtpChallenge::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        return view('customer.dashboard', compact('recentTransactions', 'pendingOtp'));
    }
}