<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Beneficiary;
use App\Models\Device;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngestTransactionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'idempotency_key'     => 'required|string|max:100',
            'account_number'      => 'required|string|exists:accounts,account_number',
            'beneficiary_account' => 'required|string',
            'device_fingerprint'  => 'required|string',
            'amount'              => 'required|numeric|gt:0',
            'channel'             => 'required|string|max:50',
            'ip'                  => 'nullable|ip',
            'city'                => 'nullable|string|max:100',
            'transaction_time'    => 'nullable|date',
        ]);

        // منع التكرار
        $existing = Transaction::where('idempotency_key', $data['idempotency_key'])->first();
        if ($existing) {
            return response()->json(['status' => 'duplicate', 'transaction_id' => $existing->id], 200);
        }

        $account = Account::where('account_number', $data['account_number'])->firstOrFail();

        $beneficiary = Beneficiary::where('account_id', $account->id)
            ->where('beneficiary_account', $data['beneficiary_account'])
            ->first();

        abort_if(! $beneficiary, 422, 'المستفيد مو مضاف لهالحساب.');

        $device = Device::where('device_fingerprint', $data['device_fingerprint'])->first()
            ?? Device::forceCreate([
                'device_fingerprint' => $data['device_fingerprint'],
                'device_type'        => 'unknown',
                'last_ip'            => $data['ip'] ?? null,
                'last_location'      => $data['city'] ?? null,
            ]);

        $txn = Transaction::forceCreate([
            'account_id'       => $account->id,
            'beneficiary_id'   => $beneficiary->id,
            'device_id'        => $device->id,
            'amount'           => $data['amount'],
            'channel'          => $data['channel'],
            'ip_address'       => $data['ip'] ?? null,
            'city'             => $data['city'] ?? null,
            'status'           => 'pending',
            'transaction_time' => $data['transaction_time'] ?? now(),
            'idempotency_key'  => $data['idempotency_key'],
        ]);

        // التقييم والقرار بينضافوا بالمرحلة 4. هلأ بنخزّن العملية بس
        return response()->json([
            'status'         => 'received',
            'transaction_id' => $txn->id,
            'decision'       => null,
        ], 202);
    }
}