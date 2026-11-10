@extends('layouts.app')

@section('title', 'تأكيد العملية عبر OTP')
@section('page_title', 'التحقق الثنائي الأمني')

@section('content')
<div class="max-w-md mx-auto mt-6">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 text-center">
        <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-xl">
            !
        </div>
        <h3 class="font-bold text-slate-800 text-base mb-1">التحقق من صحة المعاملة</h3>
        <p class="text-xs text-slate-500 mb-6">
            تم رصد مؤشرات اشتباه تستلزم تأكيد هويتك لحماية رصيدك. يرجى إدخال رمز الأمان المؤلف من 6 أرقام.
        </p>

        @if($challenge)
            <form action="{{ route('customer.otp.verify', $challenge) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <input type="text" name="otp_code" maxlength="6" autofocus placeholder="------" required class="text-center tracking-widest text-2xl font-mono py-2 w-48 border-slate-300 rounded-lg shadow-sm focus:ring-teal-500">
                </div>

                <div class="text-xs text-slate-400">
                    ينتهي الرمز عند: {{ $challenge->expires_at->format('H:i:s') }}
                </div>

                <button type="submit" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-sm rounded-lg shadow-sm transition">
                    تأكيد المعاملة الآن
                </button>
            </form>
        @else
            <div class="p-4 bg-slate-50 rounded-lg text-sm text-slate-600">
                لا توجد طلبات تحقق نشطة حالياً.
            </div>
        @endif
    </div>
</div>
@endsection