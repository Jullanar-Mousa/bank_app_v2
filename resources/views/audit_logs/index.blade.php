@extends('layouts.app')

@section('title', 'سجل العمليات الأمني')
@section('page_title', 'سجل العمليات والتدقيق الأمني (Audit & Compliance Trail)')

@section('content')
<div class="space-y-6">
    <!-- الفلاتر والبحث -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="action" value="{{ request('action') }}" placeholder="بحث بنوع الحدث أو العملية (Action)..." class="w-full text-xs border-slate-300 rounded-lg shadow-sm">
            </div>

            <div class="min-w-[180px]">
                <select name="user_id" onchange="this.form.submit()" class="w-full text-xs border-slate-300 rounded-lg shadow-sm">
                    <option value="">كافة المستخدمين والمحللين</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg font-bold">بحث وتصفية</button>
            @if(request()->hasAny(['action', 'user_id', 'entity_type']))
                <a href="{{ route('audit-logs.index') }}" class="text-slate-500 underline">إعادة ضبط</a>
            @endif
        </form>
    </div>

    <!-- جدول السجلات -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">#</th>
                        <th class="p-3.5">المستخدم المسؤول</th>
                        <th class="p-3.5">الحدث (Action)</th>
                        <th class="p-3.5">الكيان المتأثر</th>
                        <th class="p-3.5">تفاصيل التعديل (القيم السابقة / الجديدة)</th>
                        <th class="p-3.5">عنوان IP</th>
                        <th class="p-3.5">التوقيت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono text-slate-400">#{{ $log->id }}</td>
                            <td class="p-3.5">
                                @if($log->user)
                                    <div class="font-bold text-slate-800">{{ $log->user->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $log->user->email }}</div>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600 font-mono">System / Auto</span>
                                @endif
                            </td>
                            <td class="p-3.5 font-mono">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono text-[11px] text-slate-700">
                                @if($log->entity_type)
                                    <div>{{ class_basename($log->entity_type) }}</div>
                                    <div class="text-slate-400">ID: {{ $log->entity_id }}</div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-3.5 max-w-md">
                                @if($log->old_values || $log->new_values)
                                    <details class="cursor-pointer group">
                                        <summary class="text-blue-600 font-semibold group-hover:underline text-[11px]">عرض فروقات التغيير (Diff)</summary>
                                        <div class="mt-2 p-2 bg-slate-900 text-emerald-400 rounded-lg text-[10px] font-mono overflow-x-auto space-y-1" dir="ltr">
                                            @if($log->old_values)
                                                <div class="text-rose-400"><span class="font-bold">OLD:</span> {{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE) }}</div>
                                            @endif
                                            @if($log->new_values)
                                                <div><span class="font-bold">NEW:</span> {{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}</div>
                                            @endif
                                        </div>
                                    </details>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-3.5 font-mono text-slate-500 text-[11px]" dir="ltr">
                                {{ $log->ip_address ?: '127.0.0.1' }}
                            </td>
                            <td class="p-3.5 font-mono text-slate-500 text-[11px]">
                                {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                لا توجد سجلات تدقيق أمني مسجلة بعد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection