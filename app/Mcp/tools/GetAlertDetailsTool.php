<?php

namespace App\Mcp\Tools;

use App\Models\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetAlertDetailsTool extends Tool
{
    protected string $description = 'استرجاع التفاصيل الكاملة لتنبيه احتيال محدد بما في ذلك درجة الخطورة، المعاملة المرتبطة، بيانات الحساب، والمستفيد.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'alert_id' => $schema->integer()
                ->description('المعرف الرقمي للتنبيه (Alert ID) المراد التحقيق فيه.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $alertId = $request->integer('alert_id');
        $alert = Alert::with(['transaction.account', 'riskScore'])->find($alertId);

        if (! $alert) {
            return Response::error("لم يتم العثور على تنبيه بالمعرف #{$alertId}.");
        }

        $tx = $alert->transaction;
        $account = $tx?->account;
        $customer = $account ? DB::table('customers')->where('id', $account->customer_id)->first() : null;
        $beneficiary = $tx?->beneficiary_id ? DB::table('beneficiaries')->where('id', $tx->beneficiary_id)->first() : null;

        $payload = [
            'alert' => [
                'id' => $alert->id,
                'priority' => $alert->priority,
                'status' => $alert->status,
                'created_at' => $alert->created_at?->toDateTimeString(),
            ],
            'risk_assessment' => [
                'score' => $alert->riskScore?->score ?? $alert->risk_score ?? null,
                'factors' => $alert->riskScore?->factors ?? $alert->riskScore?->shap_values ?? null,
            ],
            'transaction' => $tx ? [
                'id' => $tx->id,
                'amount' => $tx->amount,
                'channel' => $tx->channel,
                'status' => $tx->status,
                'ip_address' => $tx->ip_address,
                'city' => $tx->city,
                'device_id' => $tx->device_id,
                'transaction_time' => $tx->transaction_time,
            ] : null,
            'account' => $account ? [
                'id' => $account->id,
                'account_number' => $account->account_number,
                'type' => $account->type,
                'status' => $account->status,
                'balance' => $account->balance,
                'customer_name' => $customer->name ?? $customer->full_name ?? 'غير محدد',
            ] : null,
            'beneficiary' => $beneficiary ? [
                'id' => $beneficiary->id,
                'name' => $beneficiary->name ?? $beneficiary->beneficiary_name ?? 'غير محدد',
            ] : null,
        ];

        return Response::text(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}