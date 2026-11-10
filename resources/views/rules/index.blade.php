@extends('layouts.app')

@section('title', 'إدارة قواعد الاحتيال')
@section('page_title', 'محرك القواعد الأمنية الديناميكي (Rule Engine)')

@section('content')
<div class="space-y-6">
    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-sm">قواعد الرصد والاشتباه</h3>
            <p class="text-xs text-slate-500 mt-1">
                تُحدد هذه الشروط العمليات التي يتم تصعيدها إلى نظام المخاطر مع تحديد درجة الأثر (Weight) والإجراء التلقائي.
            </p>
        </div>

        <a href="{{ route('rules.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition shadow-sm">
            <span>+</span> إضافة قاعدة جديدة
        </a>
    </div>

    <!-- جدول القواعد -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">الأولوية</th>
                        <th class="p-3.5">اسم القاعدة والوصف</th>
                        <th class="p-3.5">الوزن (Weight)</th>
                        <th class="p-3.5">الإجراء (Action)</th>
                        <th class="p-3.5">الشروط (Conditions)</th>
                        <th class="p-3.5 text-center">الحالة</th>
                        <th class="p-3.5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($rules as $rule)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono font-bold text-slate-800">
                                #{{ $rule->priority }}
                            </td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-800">{{ $rule->rule_name }}</div>
                                @if($rule->description)
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $rule->description }}</div>
                                @endif
                            </td>
                            <td class="p-3.5 font-mono">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    +{{ $rule->weight }} pts
                                </span>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold
                                    {{ $rule->action === 'block' ? 'bg-rose-100 text-rose-700' : 
                                      ($rule->action === 'otp' ? 'bg-amber-100 text-amber-700' : 
                                      ($rule->action === 'review' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ strtoupper($rule->action) }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <code class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-600 font-mono">
                                    {{ is_array($rule->conditions) ? json_encode($rule->conditions, JSON_UNESCAPED_UNICODE) : ($rule->conditions ?: '—') }}
                                </code>
                            </td>
                            <td class="p-3.5 text-center">
                                <form action="{{ route('rules.toggle', $rule) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition
                                        {{ $rule->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                        {{ $rule->is_active ? 'مفعلة' : 'معطلة' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('rules.edit', $rule) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-medium transition">
                                        تعديل
                                    </a>

                                    <form action="{{ route('rules.destroy', $rule) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه القاعدة نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded text-[11px] transition">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                لا توجد قواعد مسجلة حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rules->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $rules->links() }}
            </div>
        @endif
    </div>
</div>
@endsection