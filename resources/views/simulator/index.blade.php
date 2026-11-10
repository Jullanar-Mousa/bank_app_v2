
@extends('layouts.app')

@section('title', 'محاكي سيناريوهات الاحتيال')
@section('page_title', 'محاكي واختبار سلوك محرك الاحتيال')

@section('content')
<div class="space-y-6">

    {{-- شريط عرض نتيجة القرار بعد تنفيذ السيناريو --}}
    @if(session('status'))
        <div style="background-color: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 16px; border-radius: 8px; font-weight: bold; margin-bottom: 20px;">
            ✅ {{ session('status') }}
        </div>
    @endif

    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <h3 class="font-bold text-slate-800 text-sm">بيئة الاختبار والمحاكاة الحية (Fraud Sandbox)</h3>
            <p class="text-xs text-slate-500 mt-1">
                تتيح هذه الشاشة حقن سيناريوهات معاملات مشبوهة لاختبار استجابة محرك القواعد ونموذج الذكاء الاصطناعي في بيئة معزولة.
            </p>
        </div>
        <span class="px-3 py-1 bg-slate-900 text-emerald-400 font-mono text-xs rounded-lg">Sandbox Mode</span>
    </div>

    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <h3 class="font-bold text-slate-800 text-sm">بيئة الاختبار والمحاكاة الحية (Fraud Sandbox)</h3>
            <p class="text-xs text-slate-500 mt-1">
                تتيح هذه الشاشة حقن سيناريوهات معاملات مشبوهة لاختبار استجابة محرك القواعد ونموذج الذكاء الاصطناعي في بيئة معزولة.
            </p>
        </div>
        <span class="px-3 py-1 bg-slate-900 text-emerald-400 font-mono text-xs rounded-lg">Sandbox Mode</span>
    </div>

    <!-- شبكة بطاقات السيناريوهات -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($scenarios as $scenario)
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between hover:border-slate-300 transition">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-mono font-semibold text-slate-400">#{{ $scenario->id }}</span>
                        <span class="px-2 py-0.5 text-xs rounded font-bold {{ $scenario->risk_level === 'critical' ? 'bg-rose-100 text-rose-700' : ($scenario->risk_level === 'high' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ strtoupper($scenario->risk_level ?? 'TEST') }}
                        </span>
                    </div>

                    <h4 class="font-bold text-slate-800 text-sm mb-1">{{ $scenario->name }}</h4>
                    <p class="text-xs text-slate-500 mb-4">{{ $scenario->description }}</p>

                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 mb-4">
                        <span class="text-xs font-semibold text-slate-400 block mb-1">حمولة البيانات (Payload):</span>
                        <pre dir="ltr" class="text-[11px] font-mono text-slate-700 overflow-x-auto text-left">{{ json_encode($scenario->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>

                <form action="{{ route('simulator.scenarios.run', $scenario) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                        تشغيل السيناريو ومحاكاة المعاملة
                    </button>
                </form>
            </div>
        @empty
            <div class="col-span-3 bg-white p-8 rounded-xl border border-slate-200 text-center text-sm text-slate-500">
                لا توجد سيناريوهات اختبار محددة حالياً في قاعدة البيانات.
            </div>
        @endforelse
    </div>
</div>
@endsection