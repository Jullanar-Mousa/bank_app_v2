@extends('layouts.app')

@section('title', $rule->exists ? 'تعديل القاعدة' : 'إضافة قاعدة جديدة')
@section('page_title', $rule->exists ? 'تعديل القاعدة الأمنية: ' . $rule->rule_name : 'إضافة قاعدة أمنية جديدة')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-6">
        <div class="border-b pb-3 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">بيانات القاعدة والمعايير</h3>
            <a href="{{ route('rules.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">عودة للقائمة</a>
        </div>

        <form action="{{ $rule->exists ? route('rules.update', $rule) : route('rules.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            @if($rule->exists)
                @method('PUT')
            @endif

            <div>
                <label class="block text-slate-600 font-medium mb-1">اسم القاعدة:</label>
                <input type="text" name="rule_name" value="{{ old('rule_name', $rule->rule_name) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" placeholder="مثال: تحويل بمبلغ ضخم يتجاوز الحد الطبيعي" required>
                @error('rule_name') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-slate-600 font-medium mb-1">الوصف والمبرر الأمني:</label>
                <textarea name="description" rows="2" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" placeholder="اكتب شرحاً مختصراً لسبب عمل هذه القاعدة">{{ old('description', $rule->description) }}</textarea>
                @error('description') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-600 font-medium mb-1">الوزن / النقاط (1-100):</label>
                    <input type="number" name="weight" min="1" max="100" value="{{ old('weight', $rule->weight ?? 30) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                    @error('weight') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-600 font-medium mb-1">الأولوية (الترتيب):</label>
                    <input type="number" name="priority" min="1" max="1000" value="{{ old('priority', $rule->priority ?? 10) }}" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                    @error('priority') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-600 font-medium mb-1">الإجراء الافتراضي:</label>
                    <select name="action" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                        <option value="flag" {{ old('action', $rule->action) === 'flag' ? 'selected' : '' }}>تأشير (Flag)</option>
                        <option value="review" {{ old('action', $rule->action) === 'review' ? 'selected' : '' }}>مراجعة يدوية (Review)</option>
                        <option value="otp" {{ old('action', $rule->action) === 'otp' ? 'selected' : '' }}>طلب OTP</option>
                        <option value="block" {{ old('action', $rule->action) === 'block' ? 'selected' : '' }}>حظر فوري (Block)</option>
                    </select>
                    @error('action') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-slate-600 font-medium mb-1">شروط القاعدة بتنسيق JSON (Conditions):</label>
                <textarea name="conditions_json" rows="3" dir="ltr" class="font-mono text-xs w-full border-slate-300 rounded-lg shadow-sm" placeholder='{"field": "amount", "operator": ">", "value": 5000000}'>{{ old('conditions_json', $rule->conditions ? json_encode($rule->conditions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                <span class="text-[10px] text-slate-400">يمكن تركه فارغاً أو كتابة كائن JSON يحتوي على الحقل والمشغل والقيمة.</span>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $rule->exists ? $rule->is_active : true) ? 'checked' : '' }} class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                <label for="is_active" class="font-bold text-slate-700">تفعيل هذه القاعدة في المحرك فور الحفظ</label>
            </div>

            <div class="pt-4 border-t flex items-center justify-end gap-3">
                <a href="{{ route('rules.index') }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition font-medium">إلغاء</a>
                <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold transition shadow-sm">
                    {{ $rule->exists ? 'حفظ التعديلات' : 'إضافة القاعدة' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection