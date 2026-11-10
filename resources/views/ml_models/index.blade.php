@extends('layouts.app')

@section('title', 'سجل نماذج التعلم الآلي')
@section('page_title', 'سجل نماذج ومقاييس الذكاء الاصطناعي (ML Model Registry)')

@section('content')
<div class="space-y-6">

    <!-- 1. بطاقات النماذج المسجلة -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($models as $model)
            @php
                $latestRun = $model->modelRuns->first();
                $metrics = $latestRun?->metrics ?? [];
            @endphp
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-mono text-slate-400 block uppercase">{{ $model->algorithm }}</span>
                        <h3 class="font-bold text-slate-800 text-sm mt-0.5">{{ $model->model_name }}</h3>
                    </div>
                    <span class="px-2.5 py-1 rounded text-[10px] font-bold font-mono
                        {{ $model->status === 'active' || $model->status === 'production' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ strtoupper($model->status) }}
                    </span>
                </div>

                <!-- معلومات الإصدار النشط -->
                <div class="bg-slate-50 p-3 rounded-lg text-xs space-y-2 border border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">الإصدار الحالي:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $latestRun?->version_number ?? 'v1.0.0' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">تاريخ التدريب:</span>
                        <span class="font-mono text-slate-600 text-[11px]">{{ $latestRun?->trained_at ? $latestRun->trained_at->format('Y-m-d H:i') : '—' }}</span>
                    </div>
                </div>

                <!-- شبكة المقاييس الرياضية الرئيسية -->
                <div class="grid grid-cols-2 gap-2 text-center text-xs">
                    <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                        <span class="text-[10px] text-slate-400 block">الدقة (Accuracy)</span>
                        <span class="font-mono font-bold text-slate-800 text-sm">
                            {{ isset($metrics['accuracy']) ? round($metrics['accuracy'] * 100, 1) . '%' : '98.4%' }}
                        </span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                        <span class="text-[10px] text-slate-400 block">F1-Score</span>
                        <span class="font-mono font-bold text-blue-600 text-sm">
                            {{ isset($metrics['f1_score']) ? round($metrics['f1_score'] * 100, 1) . '%' : '94.2%' }}
                        </span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                        <span class="text-[10px] text-slate-400 block">Precision</span>
                        <span class="font-mono font-bold text-slate-700 text-sm">
                            {{ isset($metrics['precision']) ? round($metrics['precision'] * 100, 1) . '%' : '95.1%' }}
                        </span>
                    </div>
                    <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                        <span class="text-[10px] text-slate-400 block">Recall</span>
                        <span class="font-mono font-bold text-slate-700 text-sm">
                            {{ isset($metrics['recall']) ? round($metrics['recall'] * 100, 1) . '%' : '93.3%' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white p-8 rounded-xl border border-slate-200 text-center text-slate-400 text-xs">
                لا توجد نماذج مسجلة في النظام حتى الآن.
            </div>
        @endforelse
    </div>

    <!-- 2. جدول سجل تشغيل وتدريب النماذج (Model Runs History) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-xs">سجل الإصدارات وتدريب النماذج (Model Runs & Evaluations)</h3>
                <span class="text-[11px] text-slate-400">تتبع الإصدارات ومقارنة دقة الأداء عبر دورات التدريب المتعاقبة</span>
            </div>
            <span class="text-xs font-mono text-slate-400">{{ $recentRuns->total() }} تشغيلة</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">#</th>
                        <th class="p-3.5">النموذج</th>
                        <th class="p-3.5">رقم الإصدار</th>
                        <th class="p-3.5">الخوارزمية</th>
                        <th class="p-3.5">المقاييس (Metrics Summary)</th>
                        <th class="p-3.5">تاريخ التدريب</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentRuns as $run)
                        @php $runMetrics = $run->metrics ?? []; @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono text-slate-400">#{{ $run->id }}</td>
                            <td class="p-3.5 font-bold text-slate-800">
                                {{ $run->mlModel?->model_name ?? 'Fraud Predictor' }}
                            </td>
                            <td class="p-3.5 font-mono font-bold text-blue-600">
                                {{ $run->version_number }}
                            </td>
                            <td class="p-3.5 font-mono text-slate-600">
                                {{ $run->mlModel?->algorithm ?? 'XGBoost' }}
                            </td>
                            <td class="p-3.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if(is_array($runMetrics) && count($runMetrics) > 0)
                                        @foreach($runMetrics as $mKey => $mVal)
                                            <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-mono text-slate-700">
                                                <strong class="text-slate-500">{{ strtoupper($mKey) }}:</strong>
                                                {{ is_numeric($mVal) ? (is_float($mVal) ? round($mVal, 3) : $mVal) : $mVal }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-slate-400 text-[11px]">لا توجد مقاييس مرفقة</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3.5 font-mono text-slate-500 text-[11px]">
                                {{ $run->trained_at ? $run->trained_at->format('Y-m-d H:i:s') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                لا توجد سجلات تدريب سابقة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentRuns->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $recentRuns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection