<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Alert;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FraudNetworkGraph extends Component
{
    public ?int $accountId = null;
    public ?int $alertId = 1;
    public array $graphElements = [];
    public array $availableAccounts = [];

    public function mount(?int $accountId = null, ?int $alertId = 1): void
    {
        // جلب قائمة بأبرز الحسابات (يفضل التي لديها معاملات مشبوهة أولاً) للقائمة المنسدلة
        $this->availableAccounts = Account::select('id', 'account_number', 'status', 'balance')
            ->limit(25)
            ->get()
            ->toArray();

        // اختيار الحساب الممرر أو أول حساب في القاعدة تلقائياً
        $this->accountId = $accountId ?? ($this->availableAccounts[0]['id'] ?? null);
        $this->alertId = $alertId;

        $this->loadNetworkData();
    }

    public function loadNetworkData(): void
    {
        if (! $this->accountId) {
            $this->graphElements = [];
            return;
        }

        $targetAccount = Account::find($this->accountId);
        if (! $targetAccount) {
            $this->graphElements = [];
            return;
        }

        $nodes = [];
        $edges = [];
        $deviceIds = [];
        $beneficiaryIds = [];

        // جلب اسم العميل الأساسي
        $customer = DB::table('customers')->where('id', $targetAccount->customer_id)->first();
        $customerName = $customer->name ?? $customer->full_name ?? ('Customer #' . $targetAccount->customer_id);

        // 1. العقدة المركزية: الحساب قيد التحقيق
        $nodes['acc_' . $targetAccount->id] = [
            'data' => [
                'id' => 'acc_' . $targetAccount->id,
                'label' => $targetAccount->account_number . "\n(" . $customerName . ")",
                'type' => 'suspect',
                'details' => "الحساب المركزي | الرصيد: " . number_format($targetAccount->balance, 2) . " | الحالة: " . $targetAccount->status,
            ]
        ];

        // 2. المستوى الأول: معاملات الحساب الأساسي (المستفيدون + الأجهزة)
        $transactions = Transaction::where('account_id', $targetAccount->id)
            ->orderByDesc('transaction_time')
            ->limit(25)
            ->get();

        foreach ($transactions as $tx) {
            $isFlagged = in_array($tx->status, ['blocked', 'under_review', 'flagged', 'failed']);

            // أ) المستفيدون
            if ($tx->beneficiary_id) {
                $beneficiaryIds[] = $tx->beneficiary_id;
                $benKey = 'ben_' . $tx->beneficiary_id;

                if (! isset($nodes[$benKey])) {
                    $beneficiary = DB::table('beneficiaries')->where('id', $tx->beneficiary_id)->first();
                    $benName = $beneficiary->name ?? $beneficiary->beneficiary_name ?? ('Beneficiary #' . $tx->beneficiary_id);

                    $nodes[$benKey] = [
                        'data' => [
                            'id' => $benKey,
                            'label' => "🏦 " . $benName,
                            'type' => $isFlagged ? 'high_risk' : 'normal',
                            'details' => "مستفيد خارجي (#{$tx->beneficiary_id}) | المدينة: " . ($tx->city ?? 'غير محدد'),
                        ]
                    ];
                }

                $edges['tx_' . $tx->id] = [
                    'data' => [
                        'id' => 'tx_' . $tx->id,
                        'source' => 'acc_' . $targetAccount->id,
                        'target' => $benKey,
                        'amount' => number_format($tx->amount, 2) . ' (' . $tx->channel . ')',
                        'is_flagged' => $isFlagged,
                        'details' => "معاملة #{$tx->id} | الحالة: {$tx->status} | IP: {$tx->ip_address}",
                    ]
                ];
            }

            // ب) الأجهزة
            if ($tx->device_id) {
                $deviceIds[] = $tx->device_id;
                $devKey = 'dev_' . $tx->device_id;

                if (! isset($nodes[$devKey])) {
                    $device = DB::table('devices')->where('id', $tx->device_id)->first();
                    $devLabel = $device->device_name ?? $device->fingerprint ?? ('Device #' . $tx->device_id);

                    $nodes[$devKey] = [
                        'data' => [
                            'id' => $devKey,
                            'label' => "📱 " . \Illuminate\Support\Str::limit($devLabel, 18),
                            'type' => 'device',
                            'details' => "جهاز (#{$tx->device_id}) | آخر IP: {$tx->ip_address}",
                        ]
                    ];
                }

                $edges['link_dev_' . $targetAccount->id . '_' . $tx->device_id] = [
                    'data' => [
                        'id' => 'link_dev_' . $targetAccount->id . '_' . $tx->device_id,
                        'source' => 'acc_' . $targetAccount->id,
                        'target' => $devKey,
                        'amount' => 'استخدام جهاز',
                        'is_flagged' => false,
                        'details' => "دخول من هذا الجهاز عبر IP: {$tx->ip_address}",
                    ]
                ];
            }
        }

        $deviceIds = array_unique($deviceIds);
        $beneficiaryIds = array_unique($beneficiaryIds);

        // 3. المستوى الثاني (2-Hop): كشف الحسابات الأخرى التي شاركت نفس الجهاز أو المستفيد
        if (! empty($deviceIds) || ! empty($beneficiaryIds)) {
            $linkedTransactions = Transaction::where('account_id', '!=', $targetAccount->id)
                ->where(function ($query) use ($deviceIds, $beneficiaryIds) {
                    if (! empty($deviceIds)) {
                        $query->orWhereIn('device_id', $deviceIds);
                    }
                    if (! empty($beneficiaryIds)) {
                        $query->orWhereIn('beneficiary_id', $beneficiaryIds);
                    }
                })
                ->limit(30)
                ->get();

            foreach ($linkedTransactions as $ltx) {
                $peerAcc = Account::find($ltx->account_id);
                if (! $peerAcc) {
                    continue;
                }

                $peerKey = 'acc_' . $peerAcc->id;
                if (! isset($nodes[$peerKey])) {
                    $peerCustomer = DB::table('customers')->where('id', $peerAcc->customer_id)->first();
                    $peerName = $peerCustomer->name ?? $peerCustomer->full_name ?? ('Customer #' . $peerAcc->customer_id);

                    $nodes[$peerKey] = [
                        'data' => [
                            'id' => $peerKey,
                            'label' => "⚠️ " . $peerAcc->account_number . "\n(" . $peerName . ")",
                            'type' => 'linked_account',
                            'details' => "حساب مرتبط مشبوه! | الرصيد: " . number_format($peerAcc->balance, 2) . " | الحالة: " . $peerAcc->status,
                        ]
                    ];
                }

                // ربط الحساب المرتبط بالجهاز المشترك إن وجد
                if ($ltx->device_id && in_array($ltx->device_id, $deviceIds)) {
                    $edgeId = 'shared_dev_' . $peerAcc->id . '_' . $ltx->device_id;
                    $edges[$edgeId] = [
                        'data' => [
                            'id' => $edgeId,
                            'source' => $peerKey,
                            'target' => 'dev_' . $ltx->device_id,
                            'amount' => 'جهاز مشترك!',
                            'is_flagged' => true,
                            'details' => "تنبيه ارتباط: الحساب {$peerAcc->account_number} استخدم نفس الجهاز #{$ltx->device_id} (IP: {$ltx->ip_address})",
                        ]
                    ];
                }

                // ربط الحساب المرتبط بالمستفيد المشترك إن وجد
                if ($ltx->beneficiary_id && in_array($ltx->beneficiary_id, $beneficiaryIds)) {
                    $edgeId = 'shared_ben_' . $ltx->id;
                    $edges[$edgeId] = [
                        'data' => [
                            'id' => $edgeId,
                            'source' => $peerKey,
                            'target' => 'ben_' . $ltx->beneficiary_id,
                            'amount' => number_format($ltx->amount, 2) . ' (مستفيد مشترك)',
                            'is_flagged' => true,
                            'details' => "تنبيه ارتباط: الحساب {$peerAcc->account_number} حوّل لنفس المستفيد عبر معاملة #{$ltx->id}",
                        ]
                    ];
                }
            }
        }

        $this->graphElements = array_merge(array_values($nodes), array_values($edges));
    }

    public function exportAlertPdf()
    {
        $alertModel = class_exists(Alert::class)
            ? Alert::with(['transaction.account'])->find($this->alertId)
            : null;

        if ($alertModel) {
            $account = $alertModel->transaction?->account;
            $customer = $account ? DB::table('customers')->where('id', $account->customer_id)->first() : null;

            $data = [
                'alert' => [
                    'id' => $alertModel->id,
                    'risk_score' => $alertModel->riskScore?->score ?? $alertModel->risk_score ?? 'N/A',
                    'status' => $alertModel->status ?? 'new',
                    'trigger_reason' => $alertModel->reason ?? $alertModel->title ?? 'Suspicious Transaction Pattern',
                    'created_at' => $alertModel->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ],
                'transaction' => [
                    'reference' => 'TXN-' . ($alertModel->transaction?->id ?? '000'),
                    'amount' => number_format($alertModel->transaction?->amount ?? 0, 2),
                    'currency' => 'USD',
                    'type' => strtoupper($alertModel->transaction?->channel ?? 'WEB') . ' (' . ($alertModel->transaction?->city ?? 'N/A') . ')',
                ],
                'account' => [
                    'account_number' => $account?->account_number ?? 'N/A',
                    'customer_name' => $customer->name ?? $customer->full_name ?? ('Customer #' . ($account?->customer_id ?? 'N/A')),
                    'customer_email' => $customer->email ?? 'N/A',
                ],
            ];
        } else {
            $data = [
                'alert' => ['id' => $this->alertId, 'risk_score' => '90', 'status' => 'new', 'trigger_reason' => 'Manual Review', 'created_at' => now()->format('Y-m-d H:i:s')],
                'transaction' => ['reference' => 'TXN-000', 'amount' => '0.00', 'currency' => 'USD', 'type' => 'WEB'],
                'account' => ['account_number' => 'N/A', 'customer_name' => 'N/A', 'customer_email' => 'N/A'],
            ];
        }

        $data['generated_at'] = now()->format('Y-m-d H:i:s');
        $data['investigator'] = auth()->user()?->name ?? 'System Analyst';

        if (function_exists('activity')) {
            activity()
                ->withProperties(['alert_id' => $data['alert']['id']])
                ->log('Exported Fraud Alert PDF Report');
        }

        $pdf = Pdf::loadView('reports.alert-report', $data);
        return response()->streamDownload(fn () => print($pdf->output()), 'Alert_Report_' . $data['alert']['id'] . '.pdf');
    }

    public function render()
    {
        return view('livewire.fraud-network-graph');
    }
}