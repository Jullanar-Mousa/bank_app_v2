<?php

namespace Database\Seeders;

use App\Models\Rule;
use App\Models\Watchlist;
use Illuminate\Database\Seeder;

class RulesAndWatchlistSeeder extends Seeder
{
    public function run(): void
    {
        // صيغة conditions مبدئية، بنثبّتها بمرحلة محرك التقييم
        $rules = [
            [
                'rule_name' => 'new_device',
                'description' => 'تسجيل دخول أو عملية من جهاز غير معروف للعميل',
                'weight' => 20, 'priority' => 1, 'action' => 'add_score',
                'conditions' => ['all' => [['field' => 'device_is_new', 'op' => '=', 'value' => true]]],
            ],
            [
                'rule_name' => 'sim_changed_recently',
                'description' => 'تغيير الشريحة خلال آخر 48 ساعة',
                'weight' => 25, 'priority' => 1, 'action' => 'add_score',
                'conditions' => ['all' => [['field' => 'sim_changed_hours', 'op' => '<', 'value' => 48]]],
            ],
            [
                'rule_name' => 'new_beneficiary_large_transfer',
                'description' => 'تحويل كبير لمستفيد أُضيف خلال آخر 24 ساعة',
                'weight' => 30, 'priority' => 2, 'action' => 'add_score',
                'conditions' => ['all' => [
                    ['field' => 'beneficiary_age_hours', 'op' => '<', 'value' => 24],
                    ['field' => 'amount_to_avg_ratio', 'op' => '>', 'value' => 3],
                ]],
            ],
            [
                'rule_name' => 'amount_far_above_average',
                'description' => 'المبلغ أكبر من 5 أضعاف متوسط عمليات العميل',
                'weight' => 20, 'priority' => 2, 'action' => 'add_score',
                'conditions' => ['all' => [['field' => 'amount_to_avg_ratio', 'op' => '>', 'value' => 5]]],
            ],
            [
                'rule_name' => 'unusual_location',
                'description' => 'مدينة أو دولة مختلفة عن نمط العميل',
                'weight' => 15, 'priority' => 3, 'action' => 'add_score',
                'conditions' => ['all' => [['field' => 'location_matches_pattern', 'op' => '=', 'value' => false]]],
            ],
            [
                'rule_name' => 'blacklist_hit',
                'description' => 'IP أو جهاز أو حساب مستفيد موجود بالقائمة السوداء',
                'weight' => 100, 'priority' => 0, 'action' => 'force_block',
                'conditions' => ['any' => [['field' => 'watchlist_blacklist_hit', 'op' => '=', 'value' => true]]],
            ],
        ];

        foreach ($rules as $rule) {
            Rule::updateOrCreate(['rule_name' => $rule['rule_name']], $rule + ['is_active' => true]);
        }

        $entries = [
            ['Blacklist', 'IP', '203.0.113.66', 'IP مرتبط باحتيال سابق'],
            ['Blacklist', 'Account', 'SY-BLACKLISTED-0001', 'حساب وسيط معروف'],
            ['Whitelist', 'Account', 'SY-PAYROLL-0001', 'حساب رواتب موثوق'],
        ];

        foreach ($entries as [$listType, $entityType, $value, $reason]) {
            Watchlist::updateOrCreate(
                ['list_type' => $listType, 'entity_type' => $entityType, 'entity_value' => $value],
                ['reason' => $reason, 'added_at' => now()]
            );
        }
    }
}