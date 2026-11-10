<?php

namespace Database\Seeders;

use App\Models\Rule;
use App\Models\Setting;
use App\Models\SimulationScenario;
use App\Models\Watchlist;
use Illuminate\Database\Seeder;

class SimulatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. الإعدادات الافتراضية
        $settings = [
            'threshold_block' => '80',
            'threshold_otp' => '50',
            'weight_rule_engine' => '0.6',
            'weight_ml_engine' => '0.4',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 2. إدخالات القوائم المحظورة والموثوقة
        Watchlist::firstOrCreate(
            ['entity_type' => 'ip', 'entity_value' => '192.168.1.105'],
            [
                'list_type' => 'blacklist',
                'reason' => 'عنوان IP مرتبط بمحاولات اختراق سابقة',
                'added_at' => now(),
            ]
        );

        Watchlist::firstOrCreate(
            ['entity_type' => 'account', 'entity_value' => 'ACC_FRAUD_999'],
            [
                'list_type' => 'blacklist',
                'reason' => 'حساب تم الإبلاغ عنه لعمليات احتيال',
                'added_at' => now(),
            ]
        );

        // 3. قواعد كشف مسبقة في المحرك (باستخدام rule_name و is_active)
        Rule::firstOrCreate(
            ['rule_name' => 'High Value Transaction'],
            [
                'description' => 'المعاملات التي تتجاوز قيمتها 5,000,000 ليرة',
                'weight' => 55,
                'priority' => 1,
                'conditions' => ['amount' => ['operator' => '>', 'value' => 5000000]],
                'action' => 'otp',
                'is_active' => true,
            ]
        );

        Rule::firstOrCreate(
            ['rule_name' => 'ATM High Velocity Out of Hours'],
            [
                'description' => 'سحب صراف آلي مرتفع في أوقات متأخرة',
                'weight' => 45,
                'priority' => 2,
                'conditions' => ['channel' => 'atm'],
                'action' => 'review',
                'is_active' => true,
            ]
        );

// 4. سيناريوهات المحاكاة
        SimulationScenario::updateOrCreate(
            ['name' => 'عملية عادية وآمنة (Low Risk Transfer)'],
            [
                'description' => 'تحويل مبلغ اعتيادي من قناة الويب بدون أي مؤشرات خطر.',
                'risk_level' => 'low',
                'payload' => [
                    'account_id' => 1,
                    'beneficiary_id' => 2,
                    'device_id' => null,
                    'amount' => 50000,
                    'channel' => 'web',
                    'ip_address' => '10.0.0.1',
                    'city' => 'Damascus',
                ],
            ]
        );

        SimulationScenario::updateOrCreate(
            ['name' => 'تحويل مبلغ ضخم مشبوه (High Value Trigger)'],
            [
                'description' => 'تحويل يتجاوز حد قاعدة المبالغ الضخمة لاختبار تفعيل رمز التحقق (OTP).',
                'risk_level' => 'high',
                'payload' => [
                    'account_id' => 1,
                    'beneficiary_id' => 2,
                    'device_id' => null,
                    'amount' => 7500000,
                    'channel' => 'web',
                    'ip_address' => '10.0.0.1',
                    'city' => 'Damascus',
                ],
            ]
        );

        SimulationScenario::updateOrCreate(
            ['name' => 'محاولة اختراق من IP محظور (Blacklist Trigger)'],
            [
                'description' => 'محاكاة معاملة قادمة من عنوان IP مدرج في القائمة السوداء للاختبار المباشر للحظر (Block).',
                'risk_level' => 'critical',
                'payload' => [
                    'account_id' => 1,
                    'beneficiary_id' => 2,
                    'device_id' => null,
                    'amount' => 120000,
                    'channel' => 'mobile',
                    'ip_address' => '192.168.1.105',
                    'city' => 'Unknown',
                ],
            ]
        );
    }
}