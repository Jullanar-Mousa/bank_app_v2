@extends('layouts.app')

@section('title', 'مقترحات الذكاء الاصطناعي')
@section('page_title', 'مقترحات وتوصيات الذكاء الاصطناعي (AI Proposals & Agent Tools)')

@section('content')
<div class="space-y-6">

    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-xs">سجل توصيات الوكلاء الأذكياء (Autonomous Agents)</h3>
                <span class="text-[11px] text-slate-400">توصيات تتطلب مصادقة المحلل البشري (Human-in-the-Loop) قبل التطبيق</span>
            </div>
            <span class="text-xs font-mono text-slate-400">{{ $proposals->total() }} مقترح</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">#</th>
                        <th class="p-3.5">الأداة / الإجراء المقترح</th>
                        <th class="p-3.5">المرجع (التنبيه / القضية)</th>
                        <th class="p-3.5">تفاصيل المعطيات (Payload Summary)</th>
                        <th class="p-3.5">الحالة</th>
                        <th class="p-3.5">المراجع</th>
                        <th class="p-3.5 text-center">القرار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($proposals as $prop)
                        @php $payload = $prop->payload ?? []; @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono text-slate-400">#{{ $prop->id }}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded text-[11px] font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $prop->tool_name }}
                                </span>
                            </td>
                            <td class="p-3.5 text-[11px]">
                                @if($prop->alert_id)
                                    <div>تنبيه: <a href="{{ route('analyst.alerts.show', $prop->alert_id) }}" class="text-blue-600 font-mono font-bold hover:underline">#{{ $prop->alert_id }}</a></div>
                                @endif
                                @if($prop->case_id)
                                    <div>قضية: <a href="{{ route('analyst.cases.show', $prop->case_id) }}" class="text-indigo-600 font-mono font-bold hover:underline">#{{ $prop->case_id }}</a></div>
                                @endif
                                @if(!$prop->alert_id && !$prop->case_id)
                                    <span class="text-slate-400">توصية عامة</span>
                                @endif
                            </td>
                            <td class="p-3.5 max-w-sm">
                                <div class="bg-slate-50 p-2 rounded text-[11px] font-mono text-slate-600 space-y-0.5 border border-slate-100">
                                    @if(is_array($payload) && count($payload) > 0)
                                        @foreach(array_slice($payload, 0, 3) as $k => $v)
                                            <div><strong class="text-slate-500">{{ $k }}:</strong> {{ is_array($v) ? json_encode($v) : $v }}</div>
                                        @endforeach
                                    @else
                                        <span class="text-slate-400">فارغ</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3.5">
                                @if($prop->status === 'pending')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">بانتظار المراجعة</span>
                                @elseif($prop->status === 'approved')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">معتمد</span>
                                @elseif($prop->status === 'rejected')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">مرفوض</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $prop->status }}</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-700 text-[11px]">
                                @if($prop->reviewer)
                                    <div>{{ $prop->reviewer->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $prop->reviewed_at ? $prop->reviewed_at->format('Y-m-d H:i') : '' }}</div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                @if($prop->status === 'pending')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="{{ route('analyst.ai-proposals.approve', $prop) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold transition shadow-sm">
                                                اعتماد
                                            </button>
                                        </form>

                                        <form action="{{ route('analyst.ai-proposals.reject', $prop) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-[11px] font-bold transition shadow-sm">
                                                رفض
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 font-mono">مكتمل</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                لا توجد مقترحات ذكاء اصطناعي حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($proposals->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $proposals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
