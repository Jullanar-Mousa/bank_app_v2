@extends('layouts.app')

@section('title', 'بوابة العميل')
@section('page_title', 'الحساب البنكي والخدمات الإلكترونية')

@section('content')
<div class="space-y-6">
    <!-- تنبيه معلق لرمز OTP إن وجد -->
    @if($pendingOtp)
        <div class="p-5 bg-amber-50 border-r-4 border-amber-500 rounded-xl shadow-sm flex items-center justify-between">
            <div>
                <h4 class="font-bold text-amber-900 text-sm">عملية معلقة تتطلب تأكيد رمز الأمان (OTP)</h4>
                <p class="text-xs text-amber-700 mt-0.5">لديك معاملة مالية متوقفة مؤقتاً بانتظار إدخال رمز التحقق لتأكيد هويتك.</p>
            </div>
            <a href="{{ route('customer.otp.index') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition">
                إدخال رمز OTP الآن
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- نموذج إجراء تحويل مالي جديد -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 lg:col-span-1">
            <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 text-sm">إجراء تحويل مالي جديد</h3>
            <form action="{{ route('customer.transactions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">رقم حساب المستفيد</label>
                    <input type="text" name="beneficiary_account" placeholder="مثال: ACC-99201" required class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">المبلغ</label>
                    <input type="number" step="0.01" min="1" name="amount" placeholder="0.00" required class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">العملة</label>
                    <select name="currency" class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:ring-teal-500">
                        <option value="SYP">ليرة سورية (SYP)</option>
                        <option value="USD">دولار أمريكي (USD)</option>
                        <option value="EUR">يورو (EUR)</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm rounded-lg shadow-sm transition">
                    إرسال المعاملة
                </button>
            </form>
        </div>

        <!-- أحدث المعاملات المنفذة -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden lg:col-span-2">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-sm">أحدث الحركات المالية</h3>
                <a href="{{ route('customer.transactions.index') }}" class="text-xs font-semibold text-teal-600 hover:underline">عرض السجل الكامل</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentTransactions as $txn)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                        <div>
                            <div class="font-semibold text-sm text-slate-800">
                                إلى حساب: {{ $txn->beneficiary_account }}
                                <span class="font-mono text-xs text-slate-400">({{ $txn->reference_id }})</span>
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $txn->created_at->format('Y-m-d H:i') }} | القناة: {{ strtoupper($txn->channel) }}</div>
                        </div>

                        <div class="text-left">
                            <span class="font-bold text-slate-900 block font-mono">{{ number_format($txn->amount, 2) }} {{ $txn->currency }}</span>
                            <span class="text-xs px-2 py-0.5 rounded font-semibold {{ $txn->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($txn->status === 'blocked' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $txn->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-slate-500">لا توجد حركات مالية مسجلة بعد.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection