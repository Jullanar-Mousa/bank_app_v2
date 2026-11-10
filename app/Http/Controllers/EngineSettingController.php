<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class EngineSettingController extends Controller
{
    /**
     * الإعدادات الافتراضية للنظام في حال لم تكن مسجلة بقاعدة البيانات
     */
    protected array $defaults = [
        'weight_rule_engine' => 0.50,
        'weight_ml_engine' => 0.50,
        'threshold_block' => 80,
        'threshold_review' => 50,
        'threshold_critical_alert' => 85,
        'threshold_high_alert' => 65,
        'threshold_medium_alert' => 40,
        'ml_service_enabled' => true,
    ];

    /**
     * عرض شاشة إعدادات العتبات والمحرك
     */
    public function edit(): View
    {
        $settings = [];

        foreach ($this->defaults as $key => $defaultVal) {
            $setting = Setting::where('key', $key)->first();
            $settings[$key] = $setting ? $setting->value : $defaultVal;
        }

        return view('settings.engine', compact('settings'));
    }

    /**
     * تحديث وحفظ الإعدادات
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weight_rule_engine' => 'required|numeric|min:0|max:1',
            'weight_ml_engine' => 'required|numeric|min:0|max:1',
            'threshold_block' => 'required|integer|min:1|max:100',
            'threshold_review' => 'required|integer|min:1|max:100|lte:threshold_block',
            'threshold_critical_alert' => 'required|integer|min:1|max:100',
            'threshold_high_alert' => 'required|integer|min:1|max:100|lte:threshold_critical_alert',
            'threshold_medium_alert' => 'required|integer|min:1|max:100|lte:threshold_high_alert',
            'ml_service_enabled' => 'nullable|boolean',
        ]);

        $weightSum = (float) $validated['weight_rule_engine'] + (float) $validated['weight_ml_engine'];
        if (abs($weightSum - 1.0) > 0.01) {
            return back()->withInput()->withErrors([
                'weight_rule_engine' => 'يجب أن يكون مجموع وزن محرك القواعد ووزن نموذج الذكاء مساوياً لـ 1.00 (100%).',
            ]);
        }

        $descriptions = [
            'weight_rule_engine' => 'نسبة تأثير محرك القواعد في القرار النهائي',
            'weight_ml_engine' => 'نسبة تأثير نموذج الذكاء الاصطناعي في القرار النهائي',
            'threshold_block' => 'درجة الخطورة التي توجب الحظر الفوري للعملية',
            'threshold_review' => 'درجة الخطورة التي تستوجب تعليق العملية للمراجعة اليدوية',
            'threshold_critical_alert' => 'درجة الخطورة لاعتبار التنبيه حرجاً',
            'threshold_high_alert' => 'درجة الخطورة لاعتبار التنبيه عالي الأهمية',
            'threshold_medium_alert' => 'درجة الخطورة لاعتبار التنبيه متوسط الأهمية',
            'ml_service_enabled' => 'حالة تشغيل خدمة فحص الذكاء الاصطناعي الخارجية',
        ];

        $oldValues = Setting::whereIn('key', array_keys($this->defaults))
            ->pluck('value', 'key')
            ->toArray();

        foreach ($this->defaults as $key => $defaultVal) {
            $val = $key === 'ml_service_enabled'
                ? $request->boolean('ml_service_enabled')
                : $validated[$key];

            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $val,
                    'description' => $descriptions[$key] ?? null,
                ]
            );
        }

        $newValues = $validated;
        $newValues['ml_service_enabled'] = $request->boolean('ml_service_enabled');

        AuditLogService::log(
            action: 'SETTINGS_UPDATE',
            oldValues: $oldValues,
            newValues: $newValues
        );

        Cache::forget('fraud_engine_active_settings');

        return redirect()->route('settings.engine.edit')->with('status', 'تم حفظ وتطبيق معايير وأوزان المحرك بنجاح.');
    }
}