@extends('layouts.app')

@section('title', 'إدارة المستخدمين')
@section('page_title', 'المستخدمون والحسابات')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-slate-800">قائمة المستخدمين</h2>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm">
            إضافة مستخدم جديد +
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-right text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-bold">الاسم</th>
                    <th class="p-4 font-bold">البريد الإلكتروني</th>
                    <th class="p-4 font-bold">الدور</th>
                    <th class="p-4 font-bold">الحالة</th>
                    <th class="p-4 font-bold">تاريخ الإنشاء</th>
                    <th class="p-4 font-bold text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="p-4 font-semibold text-slate-800">{{ $user->name }}</td>
                        <td class="p-4">{{ $user->email }}</td>
                        <td class="p-4">
                          <div class="flex flex-wrap gap-1 justify-center">
                            @forelse($user->roles as $role)
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $role->role_name }}
                                </span>
                            @empty
                                <span class="text-slate-400 text-[11px]">بدون دور</span>
                            @endforelse
                        </div>
                        </td>
                        <td class="p-4">
                            <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $user->is_active ? 'نشط' : 'معطل' }}
                            </span>
                        </td>
                        <td class="p-4 text-xs text-slate-500">{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-blue-600 hover:underline">تعديل</a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف المستخدم؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">لا يوجد مستخدمون مسجلون.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection