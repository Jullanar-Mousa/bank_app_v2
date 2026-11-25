<?php

namespace App\Mcp\Tools;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetAccountHistoryTool extends Tool
{
    protected string $description = 'استعراض السجل الزمني لمعاملات حساب محدد والكشف عن الأجهزة المشتركة مع حسابات أخرى.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'account_id' => $schema->integer()
                ->description('معرف الحساب البنكي (Account ID).')
                ->required(),
            'limit' => $schema->integer()
                ->description('عدد المعاملات الأخيرة المراد استرجاعها (الافتراضي 15).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $accountId = $request->integer('account_id');
        $limit = $request->integer('limit', 15);

        $account = Account::find($accountId);
        if (! $account) {
            return Response::error("الحساب #{$accountId} غير موجود.");
        }

        $transactions = Transaction::where('account_id', $accountId)
            ->orderByDesc('transaction_time')
            ->limit($limit)
            ->get();

        $deviceIds = $transactions->pluck('device_id')->filter()->unique()->values()->toArray();

        // كشف الحسابات الأخرى التي استخدمت نفس الأجهزة
        $sharedDeviceAccounts = [];
        if (! empty($deviceIds)) {
            $sharedDeviceAccounts = Transaction::where('account_id', '!=', $accountId)
                ->whereIn('device_id', $deviceIds)
                ->select('account_id', 'device_id')
                ->distinct()
                ->get()
                ->toArray();
        }

        $result = [
            'account_summary' => [
                'id' => $account->id,
                'account_number' => $account->account_number,
                'balance' => $account->balance,
                'status' => $account->status,
            ],
            'recent_transactions' => $transactions->map(fn ($tx) => [
                'id' => $tx->id,
                'amount' => $tx->amount,
                'channel' => $tx->channel,
                'city' => $tx->city,
                'ip_address' => $tx->ip_address,
                'device_id' => $tx->device_id,
                'status' => $tx->status,
                'time' => $tx->transaction_time,
            ])->toArray(),
            'shared_device_links' => $sharedDeviceAccounts,
        ];

        return Response::text(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}