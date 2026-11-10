@extends('layouts.app')

@section('title', 'لوحة المحلل')
@section('page_title', 'غرفة رصد وتحليل الاحتيال')

@section('content')
<div class="space-y-6">
    <!-- المؤشرات الرقمية السريعة -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">تنبيهاتي قيد المتابعة</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-blue-600">{{ $stats['my_open_alerts'] }}</span>
                <span class="text-xs text-slate-500">مُسندة لي</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">تنبيهات بانتظار الإسناد</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-amber-600">{{ $stats['unassigned_alerts'] }}</span>
                <span class="text-xs text-amber-500">غير معينة</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">ملفات القضايا المفتوحة</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900">{{ $stats['my_active_cases'] }}</span>
                <span class="text-xs text-slate-500">تحقيقات نشطة</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">تنبيهات حرجة (Critical)</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-rose-600">{{ $stats['critical_alerts'] }}</span>
                <span class="text-xs text-rose-500">أولوية قصوى</span>
            </div>
        </div>
    </div>

    <!-- جدول التنبيهات العاجلة -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">أحدث التنبيهات العاجلة للمراجعة</h3>
            <a href="{{ route('analyst.alerts.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">عرض كافة التنبيهات</a>
        </div>
        
        <div class="divide-y divide-slate-100">
            @if(count($urgentAlerts) > 0)
                @foreach($urgentAlerts as $alert)
                    @php
                        $priority = strtolower($alert->priority ?? 'medium');
                        $badgeClass = match($priority) {
                            'critical' => 'bg-rose-100 text-rose-700 border-rose-200',
                            'high'     => 'bg-amber-100 text-amber-700 border-amber-200',
                            'low'      => 'bg-slate-100 text-slate-700 border-slate-200',
                            default    => 'bg-blue-100 text-blue-700 border-blue-200',
                        };
                        $tx = $alert->transaction;
                        $score = $alert->riskScore->score ?? $alert->riskScore->risk_score ?? 0;

                        $translations = [
                            'blacklist_hit'                  => 'قائمة سوداء',
                            'new_device'                      => 'جهاز غير معروف',
                            'sim_changed_recently'            => 'تغيير شريحة SIM حديث',
                            'High Value'                      => 'مبلغ ضخم',
                            'new_beneficiary_large_transfer'  => 'تحويل كبير لمستفيد جديد',
                            'amount_far_above_average'        => 'مبلغ يتجاوز المعدل الطبيعي',
                            'rapid_beneficiary_then_transfer' => 'تحويل سريع بعد إضافة مستفيد',
                            'unusual_location'                => 'موقع جغرافي غير معتاد',
                            'multiple_failed_logins'          => 'محاولات دخول فاشلة',
                        ];

                        $raw = $alert->decision_reason ?: ($alert->riskScore?->reasons ?? []);
                        if (is_string($raw)) {
                            $decoded = json_decode($raw, true);
                            $raw = is_array($decoded) ? $decoded : [$raw];
                        }

                        $badges = [];
                        if (is_array($raw)) {
                            $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($raw));
                            foreach ($iterator as $key => $val) {
                                if (is_string($val) && isset($translations[$val])) {
                                    $badges[] = $translations[$val];
                                } elseif (is_string($val) && !str_starts_with($val, '{') && strlen($val) < 40) {
                                    $badges[] = $translations[$val] ?? $val;
                                }
                            }
                        }
                        $badges = array_unique($badges);
                    @endphp
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                        <div class="flex items-center gap-4">
                            <span class="px-2.5 py-1 text-xs rounded-full font-bold border {{ $badgeClass }}">
                                {{ strtoupper($alert->priority ?? 'ALERT') }}
                            </span>
                            <div>
                                <div class="font-semibold text-sm text-slate-800">
                                    تنبيه على الحساب: 
                                    <span class="font-bold text-slate-900">#{{ $tx->account_id ?? '1' }}</span>
                                    <span class="font-mono text-xs text-slate-500 mr-2 font-normal">({{ $tx->idempotency_key ?? ('ID-' . $alert->id) }})</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                    @forelse(array_slice($badges, 0, 4) as $badge)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $badge }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">نشاط مالي غير اعتيادي يتطلب التحقق</span>
                                    @endforelse
                                    @if(count($badges) > 4)
                                        <span class="text-[11px] text-slate-400 self-center">+{{ count($badges) - 4 }} أسباب أخرى</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-left font-mono text-sm">
                                <span class="text-xs text-slate-400 block">درجة الخطورة</span>
                                <span class="font-bold {{ $score >= 80 ? 'text-rose-600' : ($score >= 50 ? 'text-amber-600' : 'text-blue-600') }}">
                                    {{ round($score) }}%
                                </span>
                            </div>
                            <a href="{{ route('analyst.alerts.show', $alert) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                فحص واتخاذ قرار
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="p-6 text-center text-sm text-slate-500">لا توجد تنبيهات عاجلة بحاجة لمراجعة حالياً.</div>
            @endif
        </div>
    </div>
</div>
@endsection