<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\FraudDecisionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $accountNumber = auth()->user()->account_number ?? auth()->user()->id;
        $transactions = Transaction::where('account_id', $accountNumber)->latest()->paginate(15);

        return view('customer.transactions.index', compact('transactions'));
    }

    public function store(Request $request, FraudDecisionService $decisionService): RedirectResponse
    {
        $validated = $request->validate([
            'beneficiary_account' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $accountNumber = auth()->user()->account_number ?? auth()->user()->id;

        $transaction = Transaction::create([
            'uuid' => (string) Str::uuid(),
            'reference_id' => 'TXN-' . strtoupper(Str::random(10)),
            'account_id' => (string) $accountNumber,
            'beneficiary_account' => $validated['beneficiary_account'],
            'amount' => $validated['amount'],
            'currency' => strtoupper($validated['currency']),
            'channel' => 'web',
            'ip_address' => $request->ip(),
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $evaluation = $decisionService->process($transaction);

        if ($evaluation['decision'] === 'otp') {
            return redirect()->route('customer.otp.index')->with('warning', 'العملية تتطلب تأكيد رمز الأمان (OTP).');
        } elseif ($evaluation['decision'] === 'block') {
            return redirect()->route('customer.transactions.index')->with('error', 'تم تعليق العملية لأسباب أمنية، يرجى مراجعة المصرف.');
        }

        return redirect()->route('customer.transactions.index')->with('success', 'تم تنفيذ العملية بنجاح.');
    }
}