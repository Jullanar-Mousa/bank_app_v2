@extends('layouts.app')

@section('title', 'القوائم المحظورة والموثوقة')
@section('page_title', 'إدارة القوائم المحظورة والموثوقة (Blacklists & Whitelists)')

@section('content')
<div class="space-y-6">
    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ⚠️ {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- عمود جدول العناصر والفلاتر (2/3) -->
        <div class="lg:col-span-2 space-y-4">
            <!-- شريط الفلاتر والبحث -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
                <form method="GET" action="{{ route('watchlists.index') }}" class="flex flex-wrap items-center gap-2 w-full">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالقيمة أو السبب..." class="text-xs border-slate-300 rounded-lg shadow-sm flex-1">

                    <select name="list_type" onchange="this.form.submit()" class="text-xs border-slate-300 rounded-lg shadow-sm">
                        <option value="">كافة القوائم</option>
                        <option value="blacklist" {{ request('list_type') === 'blacklist' ? 'selected' : '' }}>القائمة السوداء (Blacklist)</option>
                        <option value="whitelist" {{ request('list_type') === 'whitelist' ? 'selected' : '' }}>القائمة الموثوقة (Whitelist)</option>
                    </select>

                    <select name="entity_type" onchange="this.form.submit()" class="text-xs border-slate-300 rounded-lg shadow-sm">
                        <option value="">كافة الكيانات</option>
                        <option value="account" {{ request('entity_type') === 'account' ? 'selected' : '' }}>حساب (Account)</option>
                        <option value="ip" {{ request('entity_type') === 'ip' ? 'selected' : '' }}>عنوان IP</option>
                        <option value="beneficiary" {{ request('entity_type') === 'beneficiary' ? 'selected' : '' }}>مستفيد (Beneficiary)</option>
                        <option value="device" {{ request('entity_type') === 'device' ? 'selected' : '' }}>جهاز (Device)</option>
                    </select>

                    <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-bold">بحث</button>
                    @if(request()->hasAny(['search', 'list_type', 'entity_type']))
                        <a href="{{ route('watchlists.index') }}" class="text-xs text-slate-500 underline px-1">إعادة تعيين</a>
                    @endif
                </form>
            </div>

            <!-- جدول القوائم -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">نوع القائمة</th>
                                <th class="p-3.5">نوع الكيان</th>
                                <th class="p-3.5">القيمة (Value)</th>
                                <th class="p-3.5">سبب الإدراج</th>
                                <th class="p-3.5">تاريخ الإضافة</th>
                                <th class="p-3.5 text-center">إجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($items as $item)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3.5">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold 
                                            {{ $item->list_type === 'blacklist' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $item->list_type === 'blacklist' ? 'محظور (Blacklist)' : 'موثوق (Whitelist)' }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 font-bold text-slate-700">
                                        {{ match($item->entity_type) {
                                            'account' => 'رقم الحساب',
                                            'ip' => 'عنوان IP',
                                            'beneficiary' => 'رقم المستفيد',
                                            'device' => 'معرف الجهاز',
                                            default => $item->entity_type
                                        } }}
                                    </td>
                                    <td class="p-3.5 font-mono font-bold text-slate-900" dir="ltr">
                                        {{ $item->entity_value }}
                                    </td>
                                    <td class="p-3.5 text-slate-500">
                                        {{ $item->reason ?: '—' }}
                                    </td>
                                    <td class="p-3.5 font-mono text-slate-400 text-[11px]">
                                        {{ $item->added_at ? $item->added_at->format('Y-m-d H:i') : ($item->created_at?->format('Y-m-d H:i') ?? 'الآن') }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <form action="{{ route('watchlists.destroy', $item) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا العنصر؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded text-[11px] transition font-bold">
                                                إزالة
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">
                                        لا توجد مدخلات مسجلة في القوائم حالياً.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($items->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- عمود نموذج الإضافة السريع (1/3) -->
        <div class="space-y-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-800 text-sm border-b pb-2">إضافة قيد جديد للقائمة</h3>

                <form action="{{ route('watchlists.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">نوع القائمة المستهدفة:</label>
                        <select name="list_type" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                            <option value="blacklist" {{ old('list_type') === 'blacklist' ? 'selected' : '' }}>قائمة سوداء - حظر فوري (Blacklist)</option>
                            <option value="whitelist" {{ old('list_type') === 'whitelist' ? 'selected' : '' }}>قائمة بيضاء - ثقة وتمرير مباشر (Whitelist)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">نوع الكيان (Entity Type):</label>
                        <select name="entity_type" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                            <option value="ip" {{ old('entity_type') === 'ip' ? 'selected' : '' }}>عنوان IP (مثل: 192.168.1.105)</option>
                            <option value="account" {{ old('entity_type') === 'account' ? 'selected' : '' }}>رقم الحساب (Account ID)</option>
                            <option value="beneficiary" {{ old('entity_type') === 'beneficiary' ? 'selected' : '' }}>رقم المستفيد (Beneficiary ID)</option>
                            <option value="device" {{ old('entity_type') === 'device' ? 'selected' : '' }}>معرّف الجهاز (Device ID)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">القيمة (Entity Value):</label>
                        <input type="text" name="entity_value" value="{{ old('entity_value') }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono" placeholder="مثال: 192.168.1.105 أو 1" required>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">سبب الإدراج في القائمة:</label>
                        <textarea name="reason" rows="3" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" placeholder="اكتب سبب الحظر أو سبب اعتباره حساباً موثوقاً...">{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition shadow-sm">
                        إضافة العنصر للقائمة
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection