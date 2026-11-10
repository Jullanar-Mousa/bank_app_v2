<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OtpChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OtpChallengeController extends Controller
{
    public function index(): View
    {
        $challenge = OtpChallenge::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        return view('customer.otp.index', compact('challenge'));
    }

    public function verify(Request $request, OtpChallenge $challenge): RedirectResponse
    {
        $validated = $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        if ($challenge->expires_at < now() || $challenge->status !== 'pending') {
            return back()->with('error', 'انتهت صلاحية الرمز، يرجى إعادة المحاولة.');
        }

        if (! Hash::check($validated['otp_code'], $challenge->code_hash)) {
            $challenge->increment('attempts');
            return back()->with('error', 'رمز التحقق غير صحيح.');
        }

        $challenge->update(['status' => 'verified']);

        if ($challenge->transaction) {
            $challenge->transaction->update(['status' => 'approved', 'decision' => 'allow']);
        }

        return redirect()->route('customer.transactions.index')->with('success', 'تم التحقق وتمرير المعاملة بنجاح.');
    }
}