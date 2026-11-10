@extends('layouts.app')

@section('title', 'تفاصيل التنبيه #' . $alert->id)
@section('page_title', 'فحص وتحليل التنبيه الأمني #' . $alert->id)

@section('content')
<div class="space-y-6">
    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- عمود تفاصيل التنبيه والمعاملة -->
        <div class="lg:col-span-2 space-y-6">
            <!-- بطاقة المعاملة -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-800 text-sm border-b pb-2">بيانات المعاملة المشتبه بها</h3>
                @php $t = $alert->transaction; @endphp
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">رقم المعاملة:</span>
                        <span class="font-mono font-bold text-slate-800">#{{ $t?->id }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">المبلغ:</span>
                        <span class="font-mono font-bold text-rose-600 text-sm">{{ number_format($t?->amount ?? 0, 2) }} SYP</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">القناة:</span>
                        <span class="font-semibold text-slate-700">{{ strtoupper($t?->channel ?? 'N/A') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">حساب المرسل:</span>
                        <span class="font-mono font-semibold text-slate-700">#{{ $t?->account_id }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">حساب المستفيد:</span>
                        <span class="font-mono font-semibold text-slate-700">#{{ $t?->beneficiary_id ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">عنوان IP:</span>
                        <span class="font-mono font-semibold text-slate-700">{{ $t?->ip_address ?? 'N/A' }} ({{ $t?->city ?? 'غير محدد' }})</span>
                    </div>
                </div>
            </div>

            <!-- بطاقة تحليل المحرك ودرجة الخطورة -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-800 text-sm border-b pb-2">تحليل محرك المخاطر والذكاء الاصطناعي</h3>
                @php $score = $alert->riskScore; @endphp
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">درجة الخطورة الكلية:</span>
                        <span class="font-mono font-bold text-lg text-rose-600">{{ $score?->score ?? 0 }}%</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">نقاط محرك القواعد:</span>
                        <span class="font-mono font-bold text-slate-700">{{ $score?->rule_score ?? 0 }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">احتمالية نموذج الذكاء:</span>
                        <span class="font-mono font-bold text-slate-700">{{ ($score?->model_probability ?? 0) * 100 }}%</span>
                    </div>
                </div>

                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-xs">
                    <span class="text-slate-500 font-semibold block mb-1">أسباب الاشتباه والقواعد المطابقة:</span>
                    <pre dir="ltr" class="font-mono text-[11px] text-slate-700 overflow-x-auto">{{ json_encode($score?->reasons, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    
        <!-- عمود اتخاذ الإجراء من قبل المحلل -->
        <div class="space-y-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-800 text-sm border-b pb-2">إجراء المحلل الجنائي</h3>

                <form action="{{ route('analyst.alerts.update', $alert) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">قرار المحلل النهائي:</label>
                        <select name="decision" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                            <option value="confirmed_fraud" {{ $alert->decision === 'confirmed_fraud' ? 'selected' : '' }}>تأكيد الاحتيال (Confirmed Fraud)</option>
                            <option value="false_positive" {{ $alert->decision === 'false_positive' ? 'selected' : '' }}>إنذار خاطئ / سليم (False Positive)</option>
                            <option value="suspicious" {{ $alert->decision === 'suspicious' ? 'selected' : '' }}>معاملة مشبوهة تحتاج مزيد من التحقيق</option>
                            <option value="dismissed" {{ $alert->decision === 'dismissed' ? 'selected' : '' }}>تجاهل وحفظ</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">حالة التنبيه:</label>
                        <select name="status" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                            <option value="in_review" {{ $alert->status === 'in_review' ? 'selected' : '' }}>قيد المراجعة (In Review)</option>
                            <option value="closed" {{ $alert->status === 'closed' ? 'selected' : '' }}>إغلاق التنبيه (Closed)</option>
                            <option value="escalated" {{ $alert->status === 'escalated' ? 'selected' : '' }}>إحالة لقضية أمنية (Escalated)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">مبررات القرار والملاحظات:</label>
                        <textarea name="decision_reason" rows="3" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" placeholder="اكتب ملاحظات التدقيق والتحقيق هنا...">{{ $alert->decision_reason }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition shadow-sm">
                        تثبيت وحفظ قرار المراجعة
                    </button>
                </form>
            </div>
            <!-- بطاقة تصعيد التنبيه إلى قضية موحدة -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-800 text-sm border-b pb-2">تصعيد التنبيه إلى ملف قضية (Escalate)</h3>

                @if($alert->case_id)
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                        هذا التنبيه مُحال بالفعل إلى القضية:
                        <a href="{{ route('analyst.cases.show', $alert->case_id) }}" class="font-bold underline">
                            #{{ $alert->case_id }} ({{ $alert->fraudCase?->title ?? 'ملف القضية' }})
                        </a>
                    </div>
                @else
                    <form action="{{ route('analyst.alerts.escalate', $alert) }}" method="POST" class="space-y-3 text-xs">
                        @csrf
                        <input type="hidden" name="action_type" value="new_case">
                        <div>
                            <label class="block text-slate-600 font-medium mb-1">عنوان القضية الجديدة:</label>
                            <input type="text" name="title" value="اشتباه نشاط احتيالي - حساب #{{ $alert->transaction?->account_id }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                        </div>

                        <div>
                            <label class="block text-slate-600 font-medium mb-1">أولوية القضية:</label>
                            <select name="priority" class="w-full text-xs border-slate-300 rounded-lg shadow-sm">
                                <option value="critical">حرجة</option>
                                <option value="high" selected>عالية</option>
                                <option value="medium">متوسطة</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg transition shadow-sm">
                            إنشاء قضية أمنية وربط التنبيه بها
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection