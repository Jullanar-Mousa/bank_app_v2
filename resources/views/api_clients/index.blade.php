@extends('layouts.app')

@section('title', 'مفاتيح التكامل البرمجي')
@section('page_title', 'إدارة مفاتيح التكامل وأنظمة الربط (API Keys Management)')

@section('content')
<div class="space-y-6">

    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    {{-- نافذة تنبيه خاصة تظهر لمرة واحدة فقط عند توليد مفتاح جديد --}}
    @if(session('generated_secret'))
        <div class="bg-amber-50 border-2 border-amber-300 p-5 rounded-xl shadow-sm space-y-3">
            <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
                <span>⚠️</span>
                <span>تنبيه أمني: انسخ المفتاح السري الآن! لن تتمكن من رؤيته مرة أخرى.</span>
            </div>
            <p class="text-xs text-amber-800">
                تم توليد بيانات الاتصال للنظام: <strong class="underline">{{ session('client_name') }}</strong>.
            </p>
            <div class="bg-slate-900 p-3 rounded-lg space-y-2 text-xs font-mono text-slate-100" dir="ltr">
                <div>
                    <span class="text-slate-400 select-none">Key ID: </span>
                    <span class="text-emerald-400 font-bold">{{ session('generated_key_id') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 select-none">Secret Key: </span>
                    <span class="text-amber-300 font-bold select-all">{{ session('generated_secret') }}</span>
                </div>
            </div>
            <p class="text-[11px] text-amber-700">
                * يُستخدم الـ Key ID والـ Secret Key في ترويسات الطلبات (Headers: <code class="font-mono bg-amber-100 px-1 py-0.5 rounded">X-API-KEY</code> و <code class="font-mono bg-amber-100 px-1 py-0.5 rounded">X-API-SECRET</code>) لإرسال المعاملات للفحص عبر الـ API.
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- عمود جدول المفاتيح (2/3) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-xs">قائمة المفاتيح والأنظمة المعتمدة</h3>
                    <span class="text-slate-400 text-xs font-mono">{{ $clients->total() }} أنظمة</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">اسم النظام / الخدمة</th>
                                <th class="p-3.5">معرف المفتاح (Key ID)</th>
                                <th class="p-3.5">الحالة</th>
                                <th class="p-3.5">آخر استخدام</th>
                                <th class="p-3.5">تاريخ الإنشاء</th>
                                <th class="p-3.5 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($clients as $client)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-800">{{ $client->name }}</div>
                                        <div class="text-[10px] text-slate-400">بواسطة: {{ $client->creator?->name ?? 'النظام' }}</div>
                                    </td>
                                    <td class="p-3.5 font-mono text-slate-700 select-all" dir="ltr">
                                        {{ $client->key_id }}
                                    </td>
                                    <td class="p-3.5">
                                        @if($client->revoked_at)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                                ملغي نهائياً (Revoked)
                                            </span>
                                        @elseif($client->is_active)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                نشط (Active)
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">
                                                معطل مؤقتاً
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 font-mono text-slate-500 text-[11px]">
                                        {{ $client->last_used_at ? $client->last_used_at->diffForHumans() : 'لم يُستخدم بعد' }}
                                    </td>
                                    <td class="p-3.5 font-mono text-slate-400 text-[11px]">
                                        {{ $client->created_at ? $client->created_at->format('Y-m-d') : '—' }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if(! $client->revoked_at)
                                                {{-- زر تبديل التفعيل --}}
                                                <form action="{{ route('api-clients.toggle', $client) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="px-2 py-1 text-[11px] rounded font-semibold {{ $client->is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                                                        {{ $client->is_active ? 'تعطيل' : 'تفعيل' }}
                                                    </button>
                                                </form>

                                                {{-- زر الإلغاء النهائي --}}
                                                <form action="{{ route('api-clients.revoke', $client) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من إبطال هذا المفتاح نهائياً؟ لن يتمكن هذا النظام من إرسال طلبات بعد الآن.')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="px-2 py-1 text-[11px] text-rose-600 hover:bg-rose-50 rounded font-semibold">
                                                        إبطال
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- زر الحذف --}}
                                            <form action="{{ route('api-clients.destroy', $client) }}" method="POST" onsubmit="return confirm('حذف هذا القيد نهائياً من قاعدة البيانات؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 text-[11px] text-slate-400 hover:text-rose-600 rounded">
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">
                                        لا توجد مفاتيح تكامل أو أنظمة مسجلة حالياً.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($clients->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $clients->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- عمود توليد مفتاح جديد (1/3) -->
        <div class="space-y-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-800 text-xs border-b pb-2">توليد مفتاح تكامل جديد (New API Key)</h3>

                <form action="{{ route('api-clients.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block text-slate-600 font-medium mb-1">اسم النظام أو الخدمة المستفيدة:</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="مثال: Core Banking API / Mobile App Gateway" class="w-full text-xs border-slate-300 rounded-lg shadow-sm" required>
                        <span class="text-[10px] text-slate-400 block mt-1">اسم للتعرف على التطبيق أو الخادم الخارجي.</span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-lg text-[11px] text-slate-500 space-y-1">
                        <div>🔒 <strong>التشفير:</strong> يُحفظ المفتاح السري مشفراً في قاعدة البيانات.</div>
                        <div>⚡ <strong>البروتوكول:</strong> يُستخدم للمصادقة عبر ترويسات الـ HTTP Headers.</div>
                    </div>

                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition shadow-sm">
                        توليد وتفعيل المفتاح
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection