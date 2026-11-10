@extends('layouts.app')

@section('title', 'لوحة التحكم والإحصائيات')
@section('page_title', 'مركز الرصد والمراقبة الأمني (Fraud Monitoring Hub)')

@section('content')
<div class="space-y-6">

    <!-- 1. صف البطاقات الإحصائية الرئيسية -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- إجمالي المعاملات -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-400 block">إجمالي المعاملات المسجلة</span>
                <span class="text-2xl font-bold font-mono text-slate-800 mt-1 block">{{ number_format($totalTransactions) }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold mt-1 block">
                    {{ $approvedTransactions }} معتمدة وسليمة
                </span>
            </div>
            <div class="w-11 h-11 bg-slate-100 rounded-xl flex items-center justify-center text-slate-700 text-lg font-bold">
                💳
            </div>
        </div>

        <!-- المبالغ المحمية من الاحتيال -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-400 block">الأموال المحمية (المحظورة)</span>
                <span class="text-xl font-bold font-mono text-rose-600 mt-1 block">{{ number_format($preventedFraudAmount, 2) }}</span>
                <span class="text-[11px] text-slate-400 font-semibold mt-1 block">ليرة سورية (SYP)</span>
            </div>
            <div class="w-11 h-11 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-lg font-bold border border-rose-100">
                🛡️
            </div>
        </div>

        <!-- التنبيهات المفتوحة والحرجة -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-400 block">تنبيهات أمنية جديدة</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold font-mono text-amber-600">{{ $newAlerts }}</span>
                    <span class="text-xs text-slate-400 font-mono">/ {{ $totalAlerts }} كلي</span>
                </div>
                <span class="text-[11px] text-rose-600 font-semibold mt-1 block">
                    {{ $criticalAlerts }} تنبيه حرج (Critical)
                </span>
            </div>
            <div class="w-11 h-11 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-lg font-bold border border-amber-100">
                🔔
            </div>
        </div>

        <!-- قضايا التحقيق المفتوحة -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-400 block">قضايا الاحتيال المجمعة</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold font-mono text-slate-800">{{ $openCases }}</span>
                    <span class="text-xs text-slate-400 font-mono">قيد التحقيق</span>
                </div>
                <span class="text-[11px] text-slate-400 font-medium mt-1 block">
                    من أصل {{ $totalCases }} قضية مسجلة
                </span>
            </div>
            <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-lg font-bold border border-blue-100">
                📁
            </div>
        </div>
    </div>

    <!-- 2. صف مؤشرات الأمان وحالة محرك الرصد -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between text-xs">
            <div>
                <span class="text-slate-400 block">قواعد الاحتيال الفعالة:</span>
                <span class="font-bold text-slate-800 font-mono text-sm">{{ $activeRulesCount }} قاعدة نشطة</span>
            </div>
            <a href="{{ route('rules.index') }}" class="text-blue-600 hover:underline font-semibold">إدارة القواعد ←</a>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between text-xs">
            <div>
                <span class="text-slate-400 block">القيود في القائمة السوداء:</span>
                <span class="font-bold text-rose-600 font-mono text-sm">{{ $blacklistCount }} قيد محظور</span>
            </div>
            <a href="{{ route('watchlists.index') }}" class="text-blue-600 hover:underline font-semibold">إدارة القوائم ←</a>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between text-xs">
            <div>
                <span class="text-slate-400 block">المعاملات المعلقة للمراجعة:</span>
                <span class="font-bold text-amber-600 font-mono text-sm">{{ $underReviewTransactions }} معاملة</span>
            </div>
            <a href="{{ route('analyst.alerts.index') }}" class="text-blue-600 hover:underline font-semibold">غرفة الفحص ←</a>
        </div>
    </div>

    <!-- 3. جداول الرصد المباشر -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- آخر التنبيهات المكتشفة -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden space-y-3">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">أحدث التنبيهات الأمنية</h3>
                    <span class="text-xs text-slate-400">تنبيهات تم رصدها بواسطة المحرك الهجين</span>
                </div>
                <a href="{{ route('analyst.alerts.index') }}" class="text-xs text-blue-600 font-bold hover:underline">عرض الكل</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentAlerts as $alert)
                    @php $tx = $alert->riskScore?->transaction; @endphp
                    <div class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-800">تنبيه #{{ $alert->id }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                    {{ $alert->priority === 'critical' ? 'bg-rose-100 text-rose-700' : 
                                      ($alert->priority === 'high' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                    {{ strtoupper($alert->priority) }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-1">
                                الحساب: <span class="font-mono font-semibold">#{{ $tx?->account_id }}</span> |
                                المبلغ: <span class="font-mono text-rose-600 font-bold">{{ number_format($tx?->amount ?? 0) }} SYP</span>
                            </div>
                        </div>
                        <a href="{{ route('analyst.alerts.show', $alert) }}" class="px-2.5 py-1 bg-slate-900 text-white rounded text-[11px] font-medium hover:bg-slate-800 transition">
                            فحص
                        </a>
                    </div>
                @empty
                    <p class="p-6 text-center text-slate-400 text-xs">لا توجد تنبيهات أمنية حديثة.</p>
                @endforelse
            </div>
        </div>

        <!-- تقييمات المخاطر المباشرة (Risk Scores Stream) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden space-y-3">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">سجل تقييم المخاطر المباشر</h3>
                    <span class="text-xs text-slate-400">تكامل درجات محرك القواعد مع نموذج ML</span>
                </div>
                <span class="text-xs text-slate-400 font-mono">Live Scores</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentScores as $rs)
                    <div class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-800">معاملة #{{ $rs->transaction_id }}</span>
                                <span class="text-slate-400 font-mono text-[11px]">({{ $rs->evaluated_at?->diffForHumans() ?? 'الآن' }})</span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-1">
                                القواعد: <span class="font-mono font-bold">{{ $rs->rule_score }}</span> |
                                نموذج ML: <span class="font-mono font-bold">{{ round($rs->model_probability * 100) }}%</span>
                            </div>
                        </div>
                        <div class="text-left font-mono">
                            <span class="text-sm font-bold block {{ $rs->score >= 80 ? 'text-rose-600' : ($rs->score >= 50 ? 'text-amber-600' : 'text-emerald-600') }}">
                                {{ $rs->score }}%
                            </span>
                            <span class="text-[10px] text-slate-400 uppercase font-semibold">{{ $rs->decision }}</span>
                        </div>
                    </div>
                @empty
                    <p class="p-6 text-center text-slate-400 text-xs">لم يتم تقييم معاملات بعد.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection