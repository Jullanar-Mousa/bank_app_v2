@extends('layouts.app')

@section('title', 'اقتراح قواعد رقابية جديدة')
@section('page_title', 'اقتراح قواعد رقابية جديدة (Rule Suggestions)')

@section('content')
<div class="space-y-6">

    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-xs space-y-1">
            <div class="font-bold">يرجى التحقق من المدخلات:</div>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- جدول القواعد المقترحة (2/3) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-xs">سجل القواعد الرقابية المقترحة</h3>
                        <span class="text-[11px] text-slate-400">مقترحات مستنتجة من أنماط التحقيق الميداني لتحسين محرك الكشف</span>
                    </div>
                    <span class="text-xs font-mono text-slate-400">{{ $suggestions->total() }} مقترح</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">#</th>
                                <th class="p-3.5">عنوان القاعدة</th>
                                <th class="p-3.5">الإجراء المقترح والشروط</th>
                                <th class="p-3.5">المحلل المقترح</th>
                                <th class="p-3.5">الحالة</th>
                                <th class="p-3.5">المراجع والملاحظات</th>
                                <th class="p-3.5">التاريخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($suggestions as $item)
                                @php 
                                    $cond = is_array($item->conditions) ? $item->conditions : [];
                                    $actionName = $cond['action'] ?? 'review';
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3.5 font-mono text-slate-400">#{{ $item->id }}</td>
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-800">{{ $item->title }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5 line-clamp-2" title="{{ $item->description }}">
                                            {{ $item->description }}
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        @if($actionName === 'block')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">حظر فوري</span>
                                        @elseif($actionName === 'otp')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">طلب OTP</span>
                                        @elseif($actionName === 'review')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">مراجعة يدوية</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">سماح</span>
                                        @endif
                                        @if(!empty($cond['rules_summary']))
                                            <div class="text-[10px] text-slate-400 font-mono mt-1 max-w-[200px] truncate" title="{{ $cond['rules_summary'] }}">
                                                {{ $cond['rules_summary'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-slate-700">
                                        {{ $item->suggester?->name ?? 'المحلل' }}
                                    </td>
                                    <td class="p-3.5">
                                        @if($item->status === 'pending')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">بانتظار المراجعة</span>
                                        @elseif($item->status === 'approved')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">معتمدة ومطبقة</span>
                                        @elseif($item->status === 'rejected')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">مرفوضة</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $item->status }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-[11px]">
                                        @if($item->reviewer)
                                            <div>{{ $item->reviewer->name }}</div>
                                            @if($item->review_note)
                                                <div class="text-[10px] text-slate-400 italic">"{{ $item->review_note }}"</div>
                                            @endif
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 font-mono text-[11px] text-slate-400">
                                        {{ $item->created_at ? $item->created_at->format('Y-m-d') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">
                                        لا توجد مقترحات قواعد مسجلة حتى الآن.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($suggestions->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $suggestions->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- عمود رفع مقترح جديد (1/3) -->
        <div class="space-y-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <div class="border-b pb-2">
                    <h3 class="font-bold text-slate-800 text-xs">اقتراح قاعدة فحص جديدة (New Rule Suggestion)</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">ستتم مراجعتها من مشرف النظام لإضافتها لمحرك القواعد</p>
                </div>

                <form action="{{ route('analyst.rule-suggestions.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">اسم / عنوان القاعدة المقترحة:</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="مثلاً: محاولات متكررة في ساعات الفجر" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">الإجراء التأديبي المقترح عند تطابق القاعدة:</label>
                        <select name="action_recommendation" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                            <option value="review" {{ old('action_recommendation') == 'review' ? 'selected' : '' }}>تعليق للمراجعة اليدوية (Review)</option>
                            <option value="block" {{ old('action_recommendation') == 'block' ? 'selected' : '' }}>حظر فوري للعملية (Block)</option>
                            <option value="otp" {{ old('action_recommendation') == 'otp' ? 'selected' : '' }}>طلب رمز تحقق إضافي (Require OTP)</option>
                            <option value="allow" {{ old('action_recommendation') == 'allow' ? 'selected' : '' }}>سماح مع تسجيل تنبيه فقط (Allow & Alert)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">وصف نمط الاحتيال المكتشف (Pattern Description):</label>
                        <textarea name="description" rows="3" placeholder="اشرح النمط الذي لاحظته أثناء التحقيق مع الحسابات المشبوهة..." class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">شروط القاعدة ومعاييرها (Conditions):</label>
                        <textarea name="conditions_text" rows="3" placeholder="مثلاً: المبلغ > 50000 AND الساعة بين 02:00 و 05:00 AND جهاز جديد" class="w-full text-xs border-slate-300 rounded-lg shadow-sm font-mono">{{ old('conditions_text') }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition shadow-sm">
                        إرسال مقترح القاعدة
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection