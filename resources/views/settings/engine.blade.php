@extends('layouts.app')

@section('title', 'إعدادات العتبات والمحرك')
@section('page_title', 'معايير أوزان المحرك وعتبات القرارات (Thresholds & Weights)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ⚠️ {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('settings.engine.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. بطاقة أوزان المحرك الهجين -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="border-b pb-3">
                <h3 class="font-bold text-slate-800 text-sm">1. أوزان محرك اتخاذ القرار الهجين (Hybrid Engine Weights)</h3>
                <p class="text-xs text-slate-500 mt-1">
                    تحدد هذه النسب مدى اعتماد النظام على القواعد الثابتة مقابل نموذج تعلم الآلة. (يجب أن يكون المجموع = 1.00).
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">وزن محرك القواعد (Rule Engine Weight):</label>
                    <div class="flex items-center gap-2">
                        <input type="number" step="0.05" min="0" max="1" name="weight_rule_engine" value="{{ old('weight_rule_engine', $settings['weight_rule_engine']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 font-bold text-xs"> (مثلاً 0.60)</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">نسبة تأثير القواعد الإحصائية والشرطية.</span>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">وزن نموذج الذكاء الاصطناعي (ML Engine Weight):</label>
                    <div class="flex items-center gap-2">
                        <input type="number" step="0.05" min="0" max="1" name="weight_ml_engine" value="{{ old('weight_ml_engine', $settings['weight_ml_engine']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 font-bold text-xs"> (مثلاً 0.40)</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">نسبة تأثير التنبؤ الاحتمالي لنموذج الذكاء الاصطناعي.</span>
                </div>
            </div>

            <div class="pt-3 border-t flex items-center gap-2">
                <input type="checkbox" name="ml_service_enabled" id="ml_service_enabled" value="1" {{ old('ml_service_enabled', $settings['ml_service_enabled']) ? 'checked' : '' }} class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                <label for="ml_service_enabled" class="text-xs font-bold text-slate-700">تفعيل استدعاء خدمة الـ Machine Learning (عند التعطيل يعمل النظام على القواعد فقط)</label>
            </div>
        </div>

        <!-- 2. بطاقة عتبات اتخاذ القرارات التلقائية -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="border-b pb-3">
                <h3 class="font-bold text-slate-800 text-sm">2. عتبات القرارات التلقائية (Automated Action Thresholds)</h3>
                <p class="text-xs text-slate-500 mt-1">
                    درجة الخطورة المئوية (من 100) التي يقوم عندها النظام باتخاذ إجراء مباشر على المعاملة.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div class="bg-rose-50 p-4 rounded-lg border border-rose-100">
                    <label class="block text-rose-800 font-bold mb-1">عتبة الحظر الفوري (Block Threshold):</label>
                    <div class="flex items-center gap-2">
                        <input type="number" min="1" max="100" name="threshold_block" value="{{ old('threshold_block', $settings['threshold_block']) }}" class="w-full text-xs border-rose-300 rounded-lg shadow-sm font-mono font-bold text-rose-700" required>
                        <span class="text-rose-700 font-bold text-xs">%</span>
                    </div>
                    <span class="text-[11px] text-rose-600 mt-1 block">إذا وصلت درجة الخطورة لهذه النسبة أو تجاوزتها، يتم رفض المعاملة فوراً وحظرها.</span>
                </div>

                <div class="bg-amber-50 p-4 rounded-lg border border-amber-100">
                    <label class="block text-amber-800 font-bold mb-1">عتبة المراجعة والتعليق (Review / Hold Threshold):</label>
                    <div class="flex items-center gap-2">
                        <input type="number" min="1" max="100" name="threshold_review" value="{{ old('threshold_review', $settings['threshold_review']) }}" class="w-full text-xs border-amber-300 rounded-lg shadow-sm font-mono font-bold text-amber-700" required>
                        <span class="text-amber-700 font-bold text-xs">%</span>
                    </div>
                    <span class="text-[11px] text-amber-600 mt-1 block">إذا كانت النتيجة بين هذه العتبة وعتبة الحظر، تُعلق المعاملة ويُفتح تنبيه لمحلل الاحتيال.</span>
                </div>
            </div>
        </div>

        <!-- 3. بطاقة تصنيف أولويات التنبيهات -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="border-b pb-3">
                <h3 class="font-bold text-slate-800 text-sm">3. عتبات تصنيف أولوية التنبيه (Alert Priority Thresholds)</h3>
                <p class="text-xs text-slate-500 mt-1">
                    تصنيف درجة أهمية التنبيه للمحللين الجنائيين في لوحة التحكم بناءً على درجة الخطورة المحسوبة.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">حرج (Critical):</label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs">≥</span>
                        <input type="number" min="1" max="100" name="threshold_critical_alert" value="{{ old('threshold_critical_alert', $settings['threshold_critical_alert']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 text-xs">%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">عالي (High):</label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs">≥</span>
                        <input type="number" min="1" max="100" name="threshold_high_alert" value="{{ old('threshold_high_alert', $settings['threshold_high_alert']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 text-xs">%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">متوسط (Medium):</label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs">≥</span>
                        <input type="number" min="1" max="100" name="threshold_medium_alert" value="{{ old('threshold_medium_alert', $settings['threshold_medium_alert']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 text-xs">%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold text-xs transition shadow-sm">
                تطبيق وحفظ معايير المحرك
            </button>
        </div>
    </form>
</div>
@endsection@extends('layouts.app')

@section('title', 'إعدادات العتبات والمحرك')
@section('page_title', 'معايير أوزان المحرك وعتبات القرارات (Thresholds & Weights)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ⚠️ {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('settings.engine.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. بطاقة أوزان المحرك الهجين -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="border-b pb-3">
                <h3 class="font-bold text-slate-800 text-sm">1. أوزان محرك اتخاذ القرار الهجين (Hybrid Engine Weights)</h3>
                <p class="text-xs text-slate-500 mt-1">
                    تحدد هذه النسب مدى اعتماد النظام على القواعد الثابتة مقابل نموذج تعلم الآلة. (يجب أن يكون المجموع = 1.00).
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">وزن محرك القواعد (Rule Engine Weight):</label>
                    <div class="flex items-center gap-2">
                        <input type="number" step="0.05" min="0" max="1" name="weight_rule_engine" value="{{ old('weight_rule_engine', $settings['weight_rule_engine']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 font-bold text-xs"> (مثلاً 0.60)</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">نسبة تأثير القواعد الإحصائية والشرطية.</span>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">وزن نموذج الذكاء الاصطناعي (ML Engine Weight):</label>
                    <div class="flex items-center gap-2">
                        <input type="number" step="0.05" min="0" max="1" name="weight_ml_engine" value="{{ old('weight_ml_engine', $settings['weight_ml_engine']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 font-bold text-xs"> (مثلاً 0.40)</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">نسبة تأثير التنبؤ الاحتمالي لنموذج الذكاء الاصطناعي.</span>
                </div>
            </div>

            <div class="pt-3 border-t flex items-center gap-2">
                <input type="checkbox" name="ml_service_enabled" id="ml_service_enabled" value="1" {{ old('ml_service_enabled', $settings['ml_service_enabled']) ? 'checked' : '' }} class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                <label for="ml_service_enabled" class="text-xs font-bold text-slate-700">تفعيل استدعاء خدمة الـ Machine Learning (عند التعطيل يعمل النظام على القواعد فقط)</label>
            </div>
        </div>

        <!-- 2. بطاقة عتبات اتخاذ القرارات التلقائية -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="border-b pb-3">
                <h3 class="font-bold text-slate-800 text-sm">2. عتبات القرارات التلقائية (Automated Action Thresholds)</h3>
                <p class="text-xs text-slate-500 mt-1">
                    درجة الخطورة المئوية (من 100) التي يقوم عندها النظام باتخاذ إجراء مباشر على المعاملة.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div class="bg-rose-50 p-4 rounded-lg border border-rose-100">
                    <label class="block text-rose-800 font-bold mb-1">عتبة الحظر الفوري (Block Threshold):</label>
                    <div class="flex items-center gap-2">
                        <input type="number" min="1" max="100" name="threshold_block" value="{{ old('threshold_block', $settings['threshold_block']) }}" class="w-full text-xs border-rose-300 rounded-lg shadow-sm font-mono font-bold text-rose-700" required>
                        <span class="text-rose-700 font-bold text-xs">%</span>
                    </div>
                    <span class="text-[11px] text-rose-600 mt-1 block">إذا وصلت درجة الخطورة لهذه النسبة أو تجاوزتها، يتم رفض المعاملة فوراً وحظرها.</span>
                </div>

                <div class="bg-amber-50 p-4 rounded-lg border border-amber-100">
                    <label class="block text-amber-800 font-bold mb-1">عتبة المراجعة والتعليق (Review / Hold Threshold):</label>
                    <div class="flex items-center gap-2">
                        <input type="number" min="1" max="100" name="threshold_review" value="{{ old('threshold_review', $settings['threshold_review']) }}" class="w-full text-xs border-amber-300 rounded-lg shadow-sm font-mono font-bold text-amber-700" required>
                        <span class="text-amber-700 font-bold text-xs">%</span>
                    </div>
                    <span class="text-[11px] text-amber-600 mt-1 block">إذا كانت النتيجة بين هذه العتبة وعتبة الحظر، تُعلق المعاملة ويُفتح تنبيه لمحلل الاحتيال.</span>
                </div>
            </div>
        </div>

        <!-- 3. بطاقة تصنيف أولويات التنبيهات -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <div class="border-b pb-3">
                <h3 class="font-bold text-slate-800 text-sm">3. عتبات تصنيف أولوية التنبيه (Alert Priority Thresholds)</h3>
                <p class="text-xs text-slate-500 mt-1">
                    تصنيف درجة أهمية التنبيه للمحللين الجنائيين في لوحة التحكم بناءً على درجة الخطورة المحسوبة.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block text-slate-700 font-bold mb-1">حرج (Critical):</label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs">≥</span>
                        <input type="number" min="1" max="100" name="threshold_critical_alert" value="{{ old('threshold_critical_alert', $settings['threshold_critical_alert']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 text-xs">%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">عالي (High):</label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs">≥</span>
                        <input type="number" min="1" max="100" name="threshold_high_alert" value="{{ old('threshold_high_alert', $settings['threshold_high_alert']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 text-xs">%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">متوسط (Medium):</label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400 text-xs">≥</span>
                        <input type="number" min="1" max="100" name="threshold_medium_alert" value="{{ old('threshold_medium_alert', $settings['threshold_medium_alert']) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" required>
                        <span class="text-slate-400 text-xs">%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold text-xs transition shadow-sm">
                تطبيق وحفظ معايير المحرك
            </button>
        </div>
    </form>
</div>
@endsection