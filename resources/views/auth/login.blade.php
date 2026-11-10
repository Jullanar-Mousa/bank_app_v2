<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول - BankGuard AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style> body { font-family: 'Tajawal', sans-serif; } </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 text-slate-800">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 border border-slate-800">
        <div class="text-center mb-8">
            <span class="text-2xl font-black text-emerald-600 block">BankGuard AI</span>
            <p class="text-xs text-slate-500 mt-1">نظام رصد ومكافحة الاحتيال المصرفي</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-rose-50 border-r-4 border-rose-500 rounded text-rose-700 text-xs">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2.5 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">كلمة المرور</label>
                <input type="password" name="password" required
                    class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2.5 border">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="text-slate-600">تذكرني</span>
                </label>
            </div>

            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-lg shadow-md transition">
                تسجيل الدخول
            </button>
        </form>
    </div>

</body>
</html>