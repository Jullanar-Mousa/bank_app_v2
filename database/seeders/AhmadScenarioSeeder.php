<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountEvent;
use App\Models\Alert;
use App\Models\Beneficiary;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Device;
use App\Models\FraudCase;
use App\Models\ModelRun;
use App\Models\RiskScore;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

/**
 * سيناريو أحمد: استيلاء على حساب.
 * 30 يوم سلوك عادي، ثم خلال 22 دقيقة: دخول من جهاز جديد، تغيير جوال/شريحة،
 * إضافة مستفيد، تحويل كبير.
 */
class AhmadScenarioSeeder extends Seeder
{
    private const HOME_IP = '82.137.10.20';
    private const ATTACKER_IP = '185.220.101.7';

    public function run(): void
    {
        if (Customer::where('email', 'ahmad@bank.test')->exists()) {
            $this->command?->warn('سيناريو أحمد موجود مسبقاً، تم التخطي.');
            return;
        }

        $user = UsersSeeder::makeUser('أحمد الخطيب', 'ahmad@bank.test', 'customer');

        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => 'أحمد الخطيب',
            'phone' => '0944000111',
            'email' => 'ahmad@bank.test',
            'avg_balance' => 8000,
            'usage_pattern' => [
                'typical_channel' => 'mobile',
                'typical_city' => 'Damascus',
                'active_hours' => [9, 22],
                'avg_txn_amount' => 150,
            ],
        ]);

        $account = Account::create([
            'customer_id' => $customer->id,
            'account_number' => 'SY-AHMAD-0001',
            'type' => 'checking',
            'status' => 'active',
            'balance' => 8500,
        ]);

        // --- السلوك العادي ---
        $homeDevice = Device::create([
            'device_fingerprint' => 'dev-ahmad-iphone',
            'device_type' => 'mobile',
            'last_ip' => self::HOME_IP,
            'last_location' => 'Damascus',
        ]);
        CustomerDevice::create([
            'customer_id' => $customer->id,
            'device_id' => $homeDevice->id,
            'is_trusted' => true,
            'last_used_at' => now()->subHours(20),
        ]);

        $regulars = collect([
            ['SY-FRIEND-1001', 'ماهر الخطيب', 90],
            ['SY-RENT-2002', 'مكتب العقارات الشامي', 60],
        ])->map(fn ($b) => Beneficiary::create([
            'account_id' => $account->id,
            'beneficiary_account' => $b[0],
            'beneficiary_name' => $b[1],
            'bank_name' => 'Bank Alpha',
            'added_at' => now()->subDays($b[2]),
        ]));

        for ($i = 1; $i <= 30; $i++) {
            Transaction::create([
                'account_id' => $account->id,
                'beneficiary_id' => $regulars->random()->id,
                'device_id' => $homeDevice->id,
                'amount' => fake()->numberBetween(40, 300),
                'channel' => 'mobile',
                'ip_address' => self::HOME_IP,
                'city' => 'Damascus',
                'status' => 'completed',
                'transaction_time' => now()->subDays(fake()->numberBetween(1, 29))
                    ->setTime(fake()->numberBetween(9, 21), fake()->numberBetween(0, 59)),
                'idempotency_key' => "seed-ahmad-hist-txn-{$i}",
            ]);
        }

        for ($i = 1; $i <= 15; $i++) {
            AccountEvent::create([
                'account_id' => $account->id,
                'device_id' => $homeDevice->id,
                'event_type' => 'login',
                'ip_address' => self::HOME_IP,
                'details' => [],
                'event_time' => now()->subDays(fake()->numberBetween(1, 29))->setTime(fake()->numberBetween(9, 21), 0),
                'idempotency_key' => "seed-ahmad-hist-login-{$i}",
            ]);
        }

        // --- الهجوم ---
        $t = now()->subHours(3);
        $modelRunId = ModelRun::query()->value('id');

        $attackerDevice = Device::create([
            'device_fingerprint' => 'dev-attacker-android',
            'device_type' => 'mobile',
            'sim_changed_at' => $t->copy()->addMinutes(8),
            'last_ip' => self::ATTACKER_IP,
            'last_location' => 'Istanbul',
        ]);

        // 1) دخول من جهاز جديد
        $login = $this->event($account, $attackerDevice, 'login', $t, [
            'new_device' => true, 'country' => 'TR', 'city' => 'Istanbul',
        ], 'seed-ahmad-attack-login');
        $this->score(eventId: $login->id, score: 46, decision: 'otp_required', ruleScore: 45, probability: 0.4100,
            reasons: [['feature' => 'new_device', 'impact' => 0.31, 'text' => 'دخول من جهاز جديد']],
            modelRunId: $modelRunId);

        // 2) تغيير رقم الجوال
        $phone = $this->event($account, $attackerDevice, 'change_phone', $t->copy()->addMinutes(8), [
            'old_phone' => '0944000111', 'new_phone' => '0955999888',
        ], 'seed-ahmad-attack-phone');
        $this->score(eventId: $phone->id, score: 72, decision: 'otp_required', ruleScore: 70, probability: 0.7400,
            reasons: [
                ['feature' => 'sim_changed_recently', 'impact' => 0.33, 'text' => 'تغيير الشريحة قبل دقائق'],
                ['feature' => 'new_device', 'impact' => 0.24, 'text' => 'جهاز جديد'],
            ], modelRunId: $modelRunId);

        // 3) إضافة مستفيد جديد
        $mule = Beneficiary::create([
            'account_id' => $account->id,
            'beneficiary_account' => 'SY-MULE-7788',
            'beneficiary_name' => 'مستفيد جديد',
            'bank_name' => 'Bank Gamma',
            'added_at' => $t->copy()->addMinutes(15),
        ]);
        $benEvent = $this->event($account, $attackerDevice, 'add_beneficiary', $t->copy()->addMinutes(15), [
            'beneficiary_account' => $mule->beneficiary_account,
        ], 'seed-ahmad-attack-beneficiary');
        $this->score(eventId: $benEvent->id, score: 66, decision: 'otp_required', ruleScore: 62, probability: 0.6800,
            reasons: [['feature' => 'new_beneficiary', 'impact' => 0.27, 'text' => 'إضافة مستفيد بعد تغيير الجوال']],
            modelRunId: $modelRunId);

        // 4) التحويل الكبير (يُوقف)
        $transfer = Transaction::create([
            'account_id' => $account->id,
            'beneficiary_id' => $mule->id,
            'device_id' => $attackerDevice->id,
            'amount' => 7400,
            'channel' => 'mobile',
            'ip_address' => self::ATTACKER_IP,
            'city' => 'Istanbul',
            'status' => 'blocked',
            'transaction_time' => $t->copy()->addMinutes(22),
            'idempotency_key' => 'seed-ahmad-attack-transfer',
        ]);
        $transferScore = $this->score(transactionId: $transfer->id, score: 94, decision: 'block', ruleScore: 90, probability: 0.9700,
            reasons: [
                ['feature' => 'amount_vs_avg', 'impact' => 0.36, 'text' => 'المبلغ ~50 ضعف متوسط عمليات العميل'],
                ['feature' => 'new_beneficiary', 'impact' => 0.29, 'text' => 'المستفيد أُضيف قبل 7 دقائق'],
                ['feature' => 'sim_changed_recently', 'impact' => 0.24, 'text' => 'تغيير الشريحة قبل 14 دقيقة'],
                ['feature' => 'unusual_location', 'impact' => 0.18, 'text' => 'إسطنبول بدل دمشق'],
            ], modelRunId: $modelRunId);

        // --- التنبيه والحالة بانتظار المحلل ---
        $case = FraudCase::create([
            'title' => 'اشتباه استيلاء على حساب: أحمد الخطيب',
            'status' => 'open',
            'priority' => 'high',
        ]);
        Alert::create([
            'case_id' => $case->id,
            'risk_score_id' => $transferScore->id,
            'status' => 'new',
            'priority' => 'high',
        ]);
    }

    private function event(Account $account, Device $device, string $type, $time, array $details, string $key): AccountEvent
    {
        return AccountEvent::create([
            'account_id' => $account->id,
            'device_id' => $device->id,
            'event_type' => $type,
            'ip_address' => self::ATTACKER_IP,
            'details' => $details,
            'event_time' => $time,
            'idempotency_key' => $key,
        ]);
    }

    private function score(
        int $score,
        string $decision,
        int $ruleScore,
        float $probability,
        array $reasons,
        ?int $modelRunId,
        ?int $transactionId = null,
        ?int $eventId = null,
    ): RiskScore {
        return RiskScore::create([
            'transaction_id' => $transactionId,
            'event_id' => $eventId,
            'model_run_id' => $modelRunId,
            'score' => $score,
            'decision' => $decision,
            'reasons' => $reasons,
            'evaluated_at' => now(),
            'rule_score' => $ruleScore,
            'model_probability' => $probability,
            'used_fallback' => false,
            'latency_ms' => fake()->numberBetween(80, 220),
            'features' => [],
        ]);
    }
}