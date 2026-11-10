<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountEvent;
use App\Models\Alert;
use App\Models\Beneficiary;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Device;
use App\Models\ModelRun;
use App\Models\RiskScore;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::factory(15)->create();

        foreach ($customers as $customer) {
            $devices = Device::factory(fake()->numberBetween(1, 2))->create();

            foreach ($devices as $device) {
                CustomerDevice::factory()->create([
                    'customer_id' => $customer->id,
                    'device_id' => $device->id,
                ]);
            }

            $accounts = Account::factory(fake()->numberBetween(1, 2))->create([
                'customer_id' => $customer->id,
            ]);

            foreach ($accounts as $account) {
                $beneficiaries = Beneficiary::factory(fake()->numberBetween(2, 3))->create([
                    'account_id' => $account->id,
                ]);

                for ($i = 0; $i < fake()->numberBetween(10, 25); $i++) {
                    $device = $devices->random();
                    Transaction::factory()->create([
                        'account_id' => $account->id,
                        'beneficiary_id' => $beneficiaries->random()->id,
                        'device_id' => $device->id,
                        'ip_address' => $device->last_ip,
                        'city' => $device->last_location,
                    ]);
                }

                for ($i = 0; $i < fake()->numberBetween(5, 12); $i++) {
                    $device = $devices->random();
                    AccountEvent::factory()->create([
                        'account_id' => $account->id,
                        'device_id' => $device->id,
                        'ip_address' => $device->last_ip,
                    ]);
                }
            }
        }

        // ربط أول عميل بمستخدم بوابة العميل
        $portalUser = User::where('email', 'customer@bank.test')->first();
        if ($portalUser) {
            $customers->first()->update(['user_id' => $portalUser->id]);
        }

        $this->seedQueueAlerts();
    }

    /** 8 تنبيهات بدرجات وأولويات مختلفة لاختبار لوحة المحلل */
    private function seedQueueAlerts(): void
    {
        $modelRunId = ModelRun::query()->value('id');

        Transaction::inRandomOrder()->limit(8)->get()->each(function (Transaction $txn, int $i) use ($modelRunId) {
            $isBlock = $i % 2 === 0;

            $score = $isBlock
                ? RiskScore::factory()->block()->create(['transaction_id' => $txn->id, 'model_run_id' => $modelRunId])
                : RiskScore::factory()->otp()->create(['transaction_id' => $txn->id, 'model_run_id' => $modelRunId]);

            if ($isBlock) {
                $txn->update(['status' => 'blocked']);
            }

            Alert::factory()->create([
                'risk_score_id' => $score->id,
                'priority' => $score->score >= 85 ? 'high' : ($score->score >= 60 ? 'medium' : 'low'),
            ]);
        });
    }
}