<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BankGuard') }} - @yield('title', 'لوحة التحكم')</title>

    <!-- الخطوط وتنسيقات Tailwind و Livewire -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { font-family: 'Tajawal', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <div class="flex flex-1 min-h-screen">
        <!-- الشريط الجانبي Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col shadow-xl">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <span class="text-xl font-black tracking-wider text-emerald-400">BankGuard AI</span>
                <span class="text-xs bg-slate-800 text-slate-400 px-2 py-0.5 rounded">v2.0</span>
            </div>

            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                @if(auth()->user()->hasRole('admin'))
                    <div class="text-xs font-semibold text-slate-500 uppercase px-3 mb-2">إدارة النظام</div>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-300' }}">
                        <span>لوحة الإحصائيات</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white' : 'text-slate-300' }}">
                        <span>المستخدمون</span>
                    </a>
                    <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('admin.roles.*') ? 'bg-emerald-600 text-white' : 'text-slate-300' }}">
                        <span>الأدوار والصلاحيات</span>
                    </a>
                    <a href="{{ route('rules.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('admin.rules.*') ? 'bg-emerald-600 text-white' : 'text-slate-300' }}">
                        <span>قواعد الاحتيال</span>
                    </a>
                    <a href="{{ route('watchlists.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('admin.watchlists.*') ? 'bg-emerald-600 text-white' : 'text-slate-300' }}">
                        <span>القوائم المحظورة/الموثوقة</span>
                    </a>
                    
                    <a href="{{ route('api-clients.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('admin.api-clients.*') ? 'bg-emerald-600 text-white' : 'text-slate-300' }}">
                        <span>مفاتيح التكامل (API)</span>
                    </a>
                    <a href="{{ route('settings.engine.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('admin.settings.*') ? 'bg-emerald-600 text-white' : 'text-slate-300' }}">
                        <span>إعدادات العتبات والمحرك</span>
                    </a>
                    <a href="{{ route('ml-models.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('admin.models.*') ? 'bg-emerald-600 text-white' : 'text-slate-300' }}">
                        <span>سجل نماذج ML</span>
                    </a>
                    <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-emerald-600 text-white' : 'text-slate-300' }}">
                        <span>سجل العمليات (Audit)</span>
                    </a>
                @endif

                @if(auth()->user()->hasRole('fraud_analyst'))
                    <div class="text-xs font-semibold text-slate-500 uppercase px-3 mb-2">غرفة التحليل</div>
                    <!-- لوحة التنبيهات / لوحة المحلل -->
                    <a href="{{ route('analyst.dashboard') }}" 
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs('analyst.dashboard') ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <span>لوحة التنبيهات</span>
                    </a>

                    <!-- التنبيهات المكتشفة (جدول الفحص والفلاتر) -->
                    <a href="{{ route('analyst.alerts.index') }}" 
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs('analyst.alerts.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <span>التنبيهات المكتشفة</span>
                    </a>
                    <a href="{{ route('analyst.cases.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('analyst.cases.*') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                        <span>ملفات القضايا</span>
                    </a>
                    <a href="{{ route('analyst.ai-proposals.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('analyst.ai-proposals.*') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                        <span>مقترحات AI</span>
                    </a>
                    <a href="{{ route('analyst.account-actions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('analyst.account-actions.*') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                        <span>طلبات تجميد الحسابات</span>
                    </a>
                    <a href="{{ route('analyst.rule-suggestions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('analyst.rule-suggestions.*') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                        <span>اقتراح قواعد جديدة</span>
                    </a>
                @endif

                @if(auth()->user()->hasRole('supervisor'))
                    <div class="text-xs font-semibold text-slate-500 uppercase px-3 mb-2">إدارة الرقابة</div>
                    <a href="{{ route('supervisor.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('supervisor.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300' }}">
                        <span>لوحة الإشراف</span>
                    </a>
                    <a href="{{ route('supervisor.approvals.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('supervisor.approvals.*') ? 'bg-indigo-600 text-white' : 'text-slate-300' }}">
                        <span>طلبات الاعتماد</span>
                    </a>
                    <a href="{{ route('supervisor.rule-suggestions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('supervisor.rule-suggestions.*') ? 'bg-indigo-600 text-white' : 'text-slate-300' }}">
                        <span>مراجعة القواعد المقترحة</span>
                    </a>
                    <a href="{{ route('supervisor.reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('supervisor.reports.*') ? 'bg-indigo-600 text-white' : 'text-slate-300' }}">
                        <span>التقارير التحليلية</span>
                    </a>
                @endif

                @if(auth()->user()->hasRole('customer'))
                    <div class="text-xs font-semibold text-slate-500 uppercase px-3 mb-2">بوابة العميل</div>
                    <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('customer.dashboard') ? 'bg-teal-600 text-white' : 'text-slate-300' }}">
                        <span>الرئيسية</span>
                    </a>
                    <a href="{{ route('customer.transactions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('customer.transactions.*') ? 'bg-teal-600 text-white' : 'text-slate-300' }}">
                        <span>معاملاتي المالية</span>
                    </a>
                    <a href="{{ route('customer.otp.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('customer.otp.*') ? 'bg-teal-600 text-white' : 'text-slate-300' }}">
                        <span>تأكيد العمليات (OTP)</span>
                    </a>
                    <a href="{{ route('customer.reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 {{ request()->routeIs('customer.reports.*') ? 'bg-teal-600 text-white' : 'text-slate-300' }}">
                        <span>بلاغات الاحتيال</span>
                    </a>
                @endif
            </nav>

            <div class="p-4 border-t border-slate-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-semibold transition">
                        <span>تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- المحتوى الرئيسي Main Content Area -->
        <div class="flex-1 flex flex-col overflow-x-hidden">
            <!-- الشريط العلوي Header -->
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm">
                <div>
                    <h1 class="text-xl font-bold text-slate-800">@yield('page_title', 'النظام الذكي لكشف الاحتيال')</h1>
                </div>

                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-slate-600">{{ auth()->user()->name }}</span>
                    <span class="text-xs bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full border border-slate-200">
                        {{ auth()->user()->roles->pluck('name')->first() ?? 'مستخدم' }}
                    </span>
                </div>
            </header>

            <!-- التنبيهات والرسائل -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="mb-5 p-4 bg-emerald-50 border-r-4 border-emerald-500 text-emerald-800 rounded-lg text-sm flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-4 bg-rose-50 border-r-4 border-rose-500 text-rose-800 rounded-lg text-sm flex items-center justify-between">
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-5 p-4 bg-amber-50 border-r-4 border-amber-500 text-amber-800 rounded-lg text-sm flex items-center justify-between">
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>