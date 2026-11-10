@extends('layouts.app')

@section('title', 'طلبات تجميد وتقييد الحسابات')
@section('page_title', 'طلبات تجميد وتقييد الحسابات (Account Actions & Freezes)')

@section('content')
<div class="space-y-6">

    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-xs space-y-1">
            <div class="font-bold">حدث خطأ أثناء إرسال الطلب:</div>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- عمود جدول الطلبات السابقة (2/3) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-xs">سجل طلبات التجميد والتقييد</h3>
                        <span class="text-[11px] text-slate-400">متابعة مسار اعتمادات المشرفين (Four-Eyes Principle)</span>
                    </div>
                    <span class="text-xs font-mono text-slate-400">{{ $actions->total() }} طلبات</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">#</th>
                                <th class="p-3.5">الحساب / العميل</th>
                                <th class="p-3.5">نوع الإجراء</th>
                                <th class="p-3.5">السبب / المبرر</th>
                                <th class="p-3.5">طالب الإجراء</th>
                                <th class="p-3.5">الحالة</th>
                                <th class="p-3.5">التاريخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($actions as $action)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3.5 font-mono text-slate-400">#{{ $action->id }}</td>
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-800 font-mono" dir="ltr">
                                            {{ $action->account?->account_number ?? ('ACC-' . $action->account_id) }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $action->account?->customer?->name ?? 'عميل مصرفي' }}
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        @if($action->action === 'freeze')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">تجميد كلي (Freeze)</span>
                                        @elseif($action->action === 'restrict')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">تقييد عمليات (Restrict)</span>
                                        @elseif($action->action === 'monitor')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">مراقبة مشددة (Monitor)</span>
                                        @elseif($action->action === 'unfreeze')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">إلغاء التجميد (Unfreeze)</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $action->action }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 max-w-xs truncate text-[11px] text-slate-600" title="{{ $action->reason }}">
                                        {{ $action->reason }}
                                    </td>
                                    <td class="p-3.5 text-slate-700">
                                        {{ $action->requester?->name ?? 'المحلل' }}
                                    </td>
                                    <td class="p-3.5">
                                        @if($action->status === 'pending_approval' || $action->status === 'pending')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">بانتظار المشرف</span>
                                        @elseif($action->status === 'approved' || $action->status === 'executed')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">معتمد / منفذ</span>
                                        @elseif($action->status === 'rejected')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">مرفوض</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $action->status }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 font-mono text-[11px] text-slate-400">
                                        {{ $action->created_at ? $action->created_at->format('Y-m-d H:i') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">
                                        لا توجد طلبات تجميد أو تقييد مسجلة حتى الآن.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($actions->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $actions->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- عمود رفع طلب جديد (1/3) -->
        <div class="space-y-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <div class="border-b pb-2">
                    <h3 class="font-bold text-slate-800 text-xs">رفع طلب إجراء احترازي (New Request)</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">يتطلب موافقة المشرف قبل التنفيذ على الحساب</p>
                </div>

                <form action="{{ route('analyst.account-actions.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">الحساب المصرفي المستهدف:</label>
                        <select name="account_id" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                            <option value="">-- اختر الحساب المصرفي --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_number ?? ('ACC-' . $acc->id) }} - {{ $acc->customer?->name ?? 'عميل' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">نوع الإجراء الاحترازي المطلوب:</label>
                        <select name="action" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                            <option value="freeze" {{ old('action') == 'freeze' ? 'selected' : '' }}>تجميد كلي للحساب (Freeze Account)</option>
                            <option value="restrict" {{ old('action') == 'restrict' ? 'selected' : '' }}>تقييد الحوالات الصادرة فقط (Restrict Outgoing)</option>
                            <option value="monitor" {{ old('action') == 'monitor' ? 'selected' : '' }}>وضع الحساب تحت المراقبة المكثفة (Watchlist Monitoring)</option>
                            <option value="unfreeze" {{ old('action') == 'unfreeze' ? 'selected' : '' }}>إلغاء تجميد سابق (Unfreeze)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">أسباب ومبررات الإجراء (Justification):</label>
                        <textarea name="reason" rows="4" placeholder="اكتب مبررات الشبهة ونتائج الفحص والتحليل المصرفي..." class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>{{ old('reason') }}</textarea>
                    </div>

                    <div class="p-3 bg-amber-50 rounded-lg text-[11px] text-amber-800 space-y-1 border border-amber-200">
                        <div>⚖️ <strong>الرقابة الثنائية:</strong> لن يتم تجميد الحساب فوراً؛ سيتم توجيه الطلب لمدير العمليات للمصادقة.</div>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg transition shadow-sm">
                        إرسال الطلب للاعتماد
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection