@extends('layouts.app')

@section('title', 'قضية التحقيق #' . $case->id)
@section('page_title', 'ملف القضية: ' . $case->title)

@section('content')
<div class="space-y-6">
    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- عمود التنبيهات المرتبطة بالقضية -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-bold text-slate-800 text-sm">التنبيهات المربوطة بهذه القضية ({{ $case->alerts->count() }})</h3>
                    <span class="text-xs text-slate-400">الأدلة الميدانية المسجلة</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($case->alerts as $alert)
                        @php $tx = $alert->transaction; @endphp
                        <div class="py-3 flex items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800 text-xs">تنبيه #{{ $alert->id }}</span>
                                    <span class="font-mono text-[10px] text-slate-400">معاملة #{{ $tx?->id }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $alert->priority === 'critical' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ strtoupper($alert->priority) }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-1">
                                    الحساب: <span class="font-mono font-bold text-slate-700">#{{ $tx?->account_id }}</span> |
                                    المبلغ: <span class="font-mono font-bold text-rose-600">{{ number_format($tx?->amount ?? 0, 2) }} SYP</span> |
                                    المدينة: {{ $tx?->city ?? 'غير محدد' }}
                                </div>
                            </div>
                            <a href="{{ route('analyst.alerts.show', $alert) }}" class="px-2.5 py-1 text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition font-medium">
                                فحص التنبيه
                            </a>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">لا توجد تنبيهات مرتبطة بهذه القضية حتى الآن.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- عمود إدارة ملف القضية وتحديث حالتها -->
        <div class="space-y-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-800 text-sm border-b pb-2">بيانات وإدارة القضية</h3>

                <form action="{{ route('analyst.cases.update', $case) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">حالة القضية:</label>
                        <select name="status" class="w-full text-xs border-slate-300 rounded-lg shadow-sm">
                            <option value="open" {{ $case->status === 'open' ? 'selected' : '' }}>مفتوحة (Open)</option>
                            <option value="investigating" {{ $case->status === 'investigating' ? 'selected' : '' }}>قيد التحقيق (Investigating)</option>
                            <option value="escalated_to_legal" {{ $case->status === 'escalated_to_legal' ? 'selected' : '' }}>إحالة للشؤون القانونية</option>
                            <option value="closed_fraud" {{ $case->status === 'closed_fraud' ? 'selected' : '' }}>إغلاق - احتيال مؤكد</option>
                            <option value="closed_clean" {{ $case->status === 'closed_clean' ? 'selected' : '' }}>إغلاق - نشاط بريء وسليم</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">الأولوية:</label>
                        <select name="priority" class="w-full text-xs border-slate-300 rounded-lg shadow-sm">
                            <option value="critical" {{ $case->priority === 'critical' ? 'selected' : '' }}>حرجة (Critical)</option>
                            <option value="high" {{ $case->priority === 'high' ? 'selected' : '' }}>عالية (High)</option>
                            <option value="medium" {{ $case->priority === 'medium' ? 'selected' : '' }}>متوسطة (Medium)</option>
                            <option value="low" {{ $case->priority === 'low' ? 'selected' : '' }}>منخفضة (Low)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">تعيين المحلل المسؤول:</label>
                        <select name="assigned_to" class="w-full text-xs border-slate-300 rounded-lg shadow-sm">
                            <option value="">غير معين</option>
                            @foreach($analysts as $analyst)
                                <option value="{{ $analyst->id }}" {{ $case->assigned_to == $analyst->id ? 'selected' : '' }}>
                                    {{ $analyst->name }} ({{ $analyst->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition shadow-sm">
                        تحديث بيانات القضية
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection