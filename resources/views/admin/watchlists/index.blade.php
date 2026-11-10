@extends('layouts.app')

@section('title', 'القوائم الموثوقة والمحظورة')
@section('page_title', 'إدارة القوائم (Watchlists)')

@section('content')
<div class="space-y-6">
    <!-- نموذج الإضافة السريعة -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="font-bold text-slate-800 mb-4 text-sm">إدراج قيمة في القائمة</h3>
        <form action="{{ route('admin.watchlists.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            @csrf
            <div>
                <select name="list_type" class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:ring-emerald-500">
                    <option value="blacklist">قائمة سوداء (Blacklist)</option>
                    <option value="whitelist">قائمة بيضاء (Whitelist)</option>
                </select>
            </div>
            <div>
                <select name="entry_type" class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:ring-emerald-500">
                    <option value="ip">عنوان IP</option>
                    <option value="account">رقم حساب</option>
                    <option value="device">معرف جهاز (Device ID)</option>
                    <option value="beneficiary">مستفيد (Beneficiary)</option>
                </select>
            </div>
            <div>
                <input type="text" name="entry_value" placeholder="القيمة المراد حظرها أو توثيقها" required class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:ring-emerald-500">
            </div>
            <div>
                <input type="text" name="reason" placeholder="سبب الإدراج" class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:ring-emerald-500">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    إضافة للقائمة
                </button>
            </div>
        </form>
    </div>

    <!-- جدول عرض العناصر -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-right text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-bold">نوع القائمة</th>
                    <th class="p-4 font-bold">النوع</th>
                    <th class="p-4 font-bold">القيمة</th>
                    <th class="p-4 font-bold">السبب</th>
                    <th class="p-4 font-bold">التاريخ</th>
                    <th class="p-4 font-bold text-center">إجراء</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="p-4">
                            <span class="text-xs px-2.5 py-1 rounded-full font-bold {{ $item->list_type === 'blacklist' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ $item->list_type === 'blacklist' ? 'سوداء' : 'بيضاء' }}
                            </span>
                        </td>
                        <td class="p-4 font-medium">{{ strtoupper($item->entry_type) }}</td>
                        <td class="p-4 font-mono text-slate-900">{{ $item->entry_value }}</td>
                        <td class="p-4 text-xs text-slate-500">{{ $item->reason ?? '—' }}</td>
                        <td class="p-4 text-xs text-slate-400">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-4 text-center">
                            <form action="{{ route('admin.watchlists.destroy', $item) }}" method="POST" onsubmit="return confirm('تأكيد الحذف من القائمة؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-600 hover:underline">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">لا توجد سجلات في القوائم حالياً.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection