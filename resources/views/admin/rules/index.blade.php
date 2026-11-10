@extends('layouts.app')

@section('title', 'قواعد الاحتيال')
@section('page_title', 'قواعد فحص وتقييم الاحتيال')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-slate-800">قواعد الكشف المفعلة في المحرك</h2>
        <a href="{{ route('admin.rules.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm">
            إضافة قاعدة جديدة +
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-right text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-bold">الأولوية</th>
                    <th class="p-4 font-bold">اسم القاعدة</th>
                    <th class="p-4 font-bold">الوزن (Weight)</th>
                    <th class="p-4 font-bold">الإجراء</th>
                    <th class="p-4 font-bold">الحالة</th>
                    <th class="p-4 font-bold text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rules as $rule)
                    <tr class="hover:bg-slate-50">
                        <td class="p-4 font-bold text-slate-900">{{ $rule->priority }}</td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-800">{{ $rule->name }}</div>
                            <div class="text-xs text-slate-400">{{ Str::limit($rule->description, 50) }}</div>
                        </td>
                        <td class="p-4 font-mono font-semibold text-slate-700">{{ $rule->weight }}</td>
                        <td class="p-4">
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $rule->action === 'block' ? 'bg-rose-50 text-rose-700' : ($rule->action === 'otp' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }}">
                                {{ strtoupper($rule->action) }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="text-xs px-2 py-0.5 rounded {{ $rule->active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $rule->active ? 'مفعلة' : 'معطلة' }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.rules.edit', $rule) }}" class="text-xs text-blue-600 hover:underline">تعديل</a>
                                <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST" onsubmit="return confirm('تأكيد حذف القاعدة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">لا توجد قواعد مسجلة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection