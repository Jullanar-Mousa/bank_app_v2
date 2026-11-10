@extends('layouts.app')

@section('title', 'لوحة المشرف')
@section('page_title', 'لوحة القيادة والرقابة التشغيلية')

@section('content')
<div class="space-y-6">
    <!-- شبكة الإحصائيات الرئيسية -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">تنبيهات غير معينة</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-amber-600">{{ $stats['unassigned_alerts'] }}</span>
                <span class="text-xs text-slate-500">تحتاج إسناد</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">طلبات اعتماد معلقة</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-indigo-600">{{ $stats['pending_approvals'] }}</span>
                <span class="text-xs text-indigo-500">تجميد حسابات</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">مقترحات قواعد بانتظار البت</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900">{{ $stats['pending_suggestions'] }}</span>
                <span class="text-xs text-slate-500">من المحللين</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase">قضايا قيد التحقيق النشط</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-rose-600">{{ $stats['active_cases'] }}</span>
                <span class="text-xs text-rose-500">مفتوحة حالياً</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- جدول عبء عمل فريق المحللين -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden lg:col-span-1">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm">توزيع أعباء العمل على المحللين</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($analysts as $analyst)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-sm text-slate-800">{{ $analyst->name }}</div>
                            <div class="text-xs text-slate-400">{{ $analyst->email }}</div>
                        </div>
                        <span class="px-2.5 py-1 text-xs rounded-full font-bold {{ $analyst->open_alerts_count > 5 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700' }}">
                            {{ $analyst->open_alerts_count }} تنبيه مفتوح
                        </span>
                    </div>
                @empty
                    <div class="p-4 text-center text-sm text-slate-500">لا يوجد محللون متاحون.</div>
                @endforelse
            </div>
        </div>

        <!-- التنبيهات بانتظار الإسناد -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden lg:col-span-2">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm">التنبيهات الجديدة المتطلبة للتوزيع والإسناد</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentPendingAlerts as $alert)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-sm text-slate-800">
                                حساب: {{ $alert->account_id }}
                                <span class="text-xs font-mono text-slate-400">({{ number_format($alert->transaction->amount ?? 0, 2) }} {{ $alert->transaction->currency ?? '' }})</span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $alert->reason }}</div>
                        </div>

                        <!-- نموذج الإسناد المباشر -->
                        <form action="{{ route('supervisor.alerts.assign', $alert) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="assigned_to" required class="text-xs border-slate-300 rounded-lg py-1.5 focus:ring-indigo-500">
                                <option value="">اختيار محلل...</option>
                                @foreach($analysts as $analyst)
                                    <option value="{{ $analyst->id }}">{{ $analyst->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold">
                                إسناد
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-slate-500">جميع التنبيهات مسندة وموزعة بالكامل.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection