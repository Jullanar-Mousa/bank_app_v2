@extends('layouts.app')

@section('title', 'غرفة المحلل - قضايا الاحتيال المجمعة')
@section('page_title', 'قضايا التحقيق الأمني المجمعة (Fraud Cases)')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-sm">ملفات التحقيق المجمعة</h3>
            <p class="text-xs text-slate-500 mt-1">
                تضم هذه الشاشة قضايا الاحتيال المفتوحة والتي تربط عدة تنبيهات أو حسابات تحت ملف تحقيق موحد.
            </p>
        </div>

        <form method="GET" action="{{ route('analyst.cases.index') }}" class="flex items-center gap-2">
            <select name="priority" onchange="this.form.submit()" class="text-xs border-slate-300 rounded-lg shadow-sm">
                <option value="">كافة الأولويات</option>
                <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>حرجة</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>عالية</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>متوسطة</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>منخفضة</option>
            </select>

            <select name="status" onchange="this.form.submit()" class="text-xs border-slate-300 rounded-lg shadow-sm">
                <option value="">كافة الحالات</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>مفتوحة (Open)</option>
                <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>قيد التحقيق</option>
                <option value="escalated_to_legal" {{ request('status') === 'escalated_to_legal' ? 'selected' : '' }}>أحيلت للشؤون القانونية</option>
                <option value="closed_fraud" {{ request('status') === 'closed_fraud' ? 'selected' : '' }}>مغلقة (احتيال مؤكد)</option>
                <option value="closed_clean" {{ request('status') === 'closed_clean' ? 'selected' : '' }}>مغلقة (سليمة)</option>
            </select>
        </form>
    </div>

    <!-- جدول القضايا -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5"># القضية</th>
                        <th class="p-3.5">عنوان القضية</th>
                        <th class="p-3.5">الأولوية</th>
                        <th class="p-3.5">عدد التنبيهات المربوطة</th>
                        <th class="p-3.5">المحلل المسؤول</th>
                        <th class="p-3.5">الحالة</th>
                        <th class="p-3.5">تاريخ الفتح</th>
                        <th class="p-3.5 text-center">الإجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($cases as $case)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono text-slate-800 font-bold">#{{ $case->id }}</td>
                            <td class="p-3.5">
                                <span class="font-bold text-slate-800">{{ $case->title }}</span>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold 
                                    {{ $case->priority === 'critical' ? 'bg-rose-100 text-rose-700' : 
                                      ($case->priority === 'high' ? 'bg-amber-100 text-amber-700' : 
                                      ($case->priority === 'medium' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ strtoupper($case->priority) }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">
                                    {{ $case->alerts_count }} تنبيه
                                </span>
                            </td>
                            <td class="p-3.5">
                                {{ $case->assignee?->name ?? 'غير محدد' }}
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $case->status }}
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-400 font-mono text-[11px]">
                                {{ $case->created_at?->diffForHumans() ?? 'الآن' }}
                            </td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('analyst.cases.show', $case) }}" class="inline-flex items-center px-3 py-1 rounded bg-slate-900 hover:bg-slate-800 text-white text-[11px] transition">
                                    فتح ملف القضية
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                لا توجد قضايا احتيال مفتوحة حالياً. يمكنك تصعيد أي تنبيه ليصبح قضية مستقلة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cases->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $cases->links() }}
            </div>
        @endif
    </div>
</div>
@endsection