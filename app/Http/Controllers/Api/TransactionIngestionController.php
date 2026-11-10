<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\FraudDecisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionIngestionController extends Controller
{
    /**
     * استقبال المعاملات القادمة من النظام المصرفي وتمريرها لمحرك الفحص
     */
    public function store(Request $request, FraudDecisionService $decisionService): JsonResponse
    {
        $validated = $request->validate([
            'idempotency_key' => ['required', 'string', 'max:100', 'unique:transactions,idempotency_key'],
            'account_id'      => ['required', 'integer', 'exists:accounts,id'],
            'beneficiary_id'  => ['nullable', 'integer', 'exists:beneficiaries,id'],
            'device_id'       => ['nullable', 'integer', 'exists:devices,id'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'channel'         => ['required', 'string', 'in:atm,pos,web,mobile,branch'],
            'ip_address'      => ['nullable', 'ip'],
            'city'            => ['nullable', 'string', 'max:255'],
            'transaction_time'=> ['nullable', 'date'],
        ]);

        $transaction = Transaction::create([
            'idempotency_key'  => $validated['idempotency_key'],
            'account_id'       => $validated['account_id'],
            'beneficiary_id'   => $validated['beneficiary_id'] ?? null,
            'device_id'        => $validated['device_id'] ?? null,
            'amount'           => $validated['amount'],
            'channel'          => $validated['channel'],
            'ip_address'       => $validated['ip_address'] ?? $request->ip(),
            'city'             => $validated['city'] ?? 'Damascus',
            'status'           => 'pending',
            'transaction_time' => $validated['transaction_time'] ?? now(),
        ]);

        // تمرير المعاملة لمحرك اتخاذ القرار الهجين
        $evaluation = $decisionService->process($transaction);

        return response()->json([
            'success'     => true,
            'message'     => 'تم استلام المعاملة وتدقيقها بنجاح.',
            'transaction' => [
                'id'              => $transaction->id,
                'idempotency_key' => $transaction->idempotency_key,
                'status'          => $transaction->status,
                'decision'        => $evaluation['decision'] ?? 'evaluated',
                'risk_score'      => $evaluation['risk_score'] ?? null,
                'alert_id'        => $evaluation['alert_id'] ?? null,
            ],
        ], 200);
    }
}