@extends('layouts.app')

@section('title', 'إدارة الأدوار والصلاحيات')
@section('page_title', 'إدارة الأدوار والصلاحيات (Roles & Access Control)')

@section('content')
<div class="space-y-6">

    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-xs">قائمة أدوار النظام (System Roles)</h3>
                <span class="text-[11px] text-slate-400">التحكم في وصول المستخدمين ووظائف النظام الرقابي (RBAC)</span>
            </div>
            <span class="text-xs font-mono text-slate-400">{{ $roles->count() }} أدوار</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">#</th>
                        <th class="p-3.5">اسم الدور (Role Name)</th>
                        <th class="p-3.5">المستخدمين المسندين</th>
                        <th class="p-3.5">عدد الصلاحيات</th>
                        <th class="p-3.5">تاريخ الإنشاء</th>
                        <th class="p-3.5 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($roles as $role)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono text-slate-400">#{{ $role->id }}</td>
                            <td class="p-3.5 font-mono font-bold text-slate-800">
                                <span class="px-2.5 py-1 rounded text-xs bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $role->role_name }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $role->users_count }} مستخدم
                                </span>
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $role->permissions_count }} صلاحية
                                </span>
                            </td>
                            <td class="p-3.5 font-mono text-[11px] text-slate-400">
                                {{ $role->created_at ? $role->created_at->format('Y-m-d') : '—' }}
                            </td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('admin.roles.show', $role) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold transition shadow-sm">
                                    <span>إدارة الصلاحيات</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                لا توجد أدوار مسجلة في النظام.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection