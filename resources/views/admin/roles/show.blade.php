@extends('layouts.app')

@section('title', 'صلاحيات الدور: ' . $role->role_name)
@section('page_title', 'إدارة صلاحيات الدور: ' . $role->role_name)

@section('content')
<div class="space-y-6">

    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-xs font-semibold">
            ✅ {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-800">تعديل الصلاحيات الممنوحة للدور</h3>
            <p class="text-[11px] text-slate-400">حدد الصلاحيات المسموح بها لهذا الدور في النظام الرقابي</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium transition">
            &larr; العودة لقائمة الأدوار
        </a>
    </div>

    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-6">
            
            <div class="flex items-center justify-between border-b pb-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-500">اسم الدور:</span>
                    <span class="px-3 py-1 rounded bg-slate-100 font-mono font-bold text-slate-800 text-xs border border-slate-200">
                        {{ $role->role_name }}
                    </span>
                    <span class="text-xs text-slate-400">({{ $role->users->count() }} مستخدم مرتبط بهذا الدور)</span>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-bold transition shadow-sm">
                    حفظ التغييرات
                </button>
            </div>

            @php
                $assignedPermissionIds = $role->permissions->pluck('id')->toArray();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($allPermissions as $perm)
                    <label class="flex items-start gap-3 p-3.5 rounded-lg border border-slate-200 hover:bg-slate-50 transition cursor-pointer select-none">
                        <input type="checkbox" 
                               name="permissions[]" 
                               value="{{ $perm->id }}" 
                               {{ in_array($perm->id, $assignedPermissionIds) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <div class="space-y-0.5">
                            <div class="text-xs font-mono font-bold text-slate-800">{{ $perm->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $perm->description ?? 'صلاحية نظام' }}</div>
                        </div>
                    </label>
                @empty
                    <div class="col-span-3 text-center text-slate-400 p-8">
                        لا توجد صلاحيات مسجلة في جدول permissions.
                    </div>
                @endforelse
            </div>

            <div class="border-t pt-4 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                    حفظ واعتماد الصلاحيات
                </button>
            </div>
        </div>
    </form>

</div>
@endsection