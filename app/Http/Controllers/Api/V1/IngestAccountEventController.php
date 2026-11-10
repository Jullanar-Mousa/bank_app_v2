<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountEvent;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngestAccountEventController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'idempotency_key'    => 'required|string|max:100',
            'account_number'     => 'required|string|exists:accounts,account_number',
            'event_type'         => 'required|in:login,add_beneficiary,change_phone,raise_limit,account_open',
            'device_fingerprint' => 'nullable|string',
            'ip'                 => 'nullable|ip',
            'details'            => 'nullable|array',
            'event_time'         => 'nullable|date',
        ]);

        $existing = AccountEvent::where('idempotency_key', $data['idempotency_key'])->first();
        if ($existing) {
            return response()->json(['status' => 'duplicate', 'event_id' => $existing->id], 200);
        }

        $account = Account::where('account_number', $data['account_number'])->firstOrFail();

        $device = null;
        if (! empty($data['device_fingerprint'])) {
            $device = Device::where('device_fingerprint', $data['device_fingerprint'])->first()
                ?? Device::forceCreate([
                    'device_fingerprint' => $data['device_fingerprint'],
                    'device_type'        => 'unknown',
                    'last_ip'            => $data['ip'] ?? null,
                ]);
        }

        $event = AccountEvent::forceCreate([
            'account_id'      => $account->id,
            'device_id'       => $device?->id,
            'event_type'      => $data['event_type'],
            'ip_address'      => $data['ip'] ?? null,
            'details'         => $data['details'] ?? null,
            'event_time'      => $data['event_time'] ?? now(),
            'idempotency_key' => $data['idempotency_key'],
        ]);

        return response()->json([
            'status'   => 'received',
            'event_id' => $event->id,
            'decision' => null,
        ], 202);
    }
}