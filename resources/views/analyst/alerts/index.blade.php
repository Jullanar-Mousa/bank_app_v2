@extends('layouts.app')

@section('title', 'غرفة المحلل - سجل التنبيهات الأمنية')
@section('page_title', 'التنبيهات الأمنية للاشتباه بالاحتيال')

@section('content')
<div class="space-y-6">
    <!-- شريط معلومات وفلاتر -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-sm">مراقبة التنبيهات المباشرة</h3>
            <p class="text-xs text-slate-500 mt-1">
                تنبيهات فورية تم توليدها بواسطة محرك القواعد ونموذج الذكاء الاصطناعي بناءً على درجات الخطورة.
            </p>
        </div>

        <form method="GET" action="{{ route('analyst.alerts.index') }}" class="flex items-center gap-2">
            <select name="priority" onchange="this.form.submit()" class="text-xs border-slate-300 rounded-lg shadow-sm">
                <option value="">كافة الأولويات</option>
                <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>حرجة (Critical)</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>عالية (High)</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>متوسطة (Medium)</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>منخفضة (Low)</option>
            </select>

            <select name="status" onchange="this.form.submit()" class="text-xs border-slate-300 rounded-lg shadow-sm">
                <option value="">كافة الحالات</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>جديد (New)</option>
                <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>قيد المراجعة</option>
                <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>مُحال لقضية</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>مغلق</option>
            </select>
        </form>
    </div>

    <!-- جدول التنبيهات -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5"># التنبيه</th>
                        <th class="p-3.5">الأولوية</th>
                        <th class="p-3.5">رقم الحساب / المعاملة</th>
                        <th class="p-3.5">المبلغ</th>
                        <th class="p-3.5">درجة الخطورة</th>
                        <th class="p-3.5">القرار التلقائي</th>
                        <th class="p-3.5">الحالة</th>
                        <th class="p-3.5">وقت الرصد</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($alerts as $alert)
                        @php
                            $transaction = $alert->transaction;
                            $riskScore = $alert->riskScore;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono text-slate-800">#{{ $alert->id }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold 
                                    {{ $alert->priority === 'critical' ? 'bg-rose-100 text-rose-700' : 
                                      ($alert->priority === 'high' ? 'bg-amber-100 text-amber-700' : 
                                      ($alert->priority === 'medium' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ strtoupper($alert->priority) }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-800">حساب #{{ $transaction?->account_id ?? 'N/A' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">معاملة #{{ $transaction?->id ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-slate-800">
                                {{ number_format($transaction?->amount ?? 0, 2) }} SYP
                            </td>
                            <td class="p-3.5 font-mono">
                                <span class="font-bold {{ ($riskScore?->score ?? 0) >= 80 ? 'text-rose-600' : (($riskScore?->score ?? 0) >= 50 ? 'text-amber-600' : 'text-emerald-600') }}">
                                    {{ $riskScore?->score ?? 0 }}%
                                </span>
                            </td>
                            <td class="p-3.5">
                                <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                    {{ strtoupper($riskScore?->decision ?? 'UNKNOWN') }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium {{ $alert->status === 'new' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $alert->status }}
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-400 font-mono text-[11px]">
                                {{ $alert->created_at?->diffForHumans() ?? 'الآن' }}
                            </td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('analyst.alerts.show', $alert) }}" class="inline-flex items-center px-2.5 py-1 rounded bg-slate-900 hover:bg-slate-800 text-white text-[11px] transition">
                                    معاينة واتخاذ قرار
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400">
                                لا توجد تنبيهات مسجلة حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alerts->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection