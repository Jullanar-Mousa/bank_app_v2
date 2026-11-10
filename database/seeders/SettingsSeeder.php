<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['allow_max', 39, 'أعلى درجة لسماح مباشر (0 إلى هذا الرقم)'],
            ['block_min', 75, 'أقل درجة للإيقاف، وما بينهما تحقق OTP'],
            ['otp_ttl_minutes', 5, 'مدة صلاحية رمز OTP بالدقائق'],
            ['otp_max_attempts', 3, 'عدد محاولات OTP المسموحة'],
            ['ai_timeout_ms', 300, 'مهلة خدمة الذكاء الاصطناعي قبل الرجوع للقواعد فقط'],
            ['rule_weight_share', 0.5, 'وزن درجة القواعد مقابل النموذج في الدرجة النهائية'],
        ];

        foreach ($settings as [$key, $value, $description]) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'description' => $description]);
        }
    }
}