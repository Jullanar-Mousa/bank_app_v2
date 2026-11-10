<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * عرض قائمة الأدوار وعدد المستخدمين والصلاحيات المربوطة بكل دور
     */
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * عرض تفاصيل الدور وقائمة الصلاحيات المتاحة لإدارتها
     */
    public function show(Role $role): View
    {
        $role->load(['permissions', 'users']);
        $allPermissions = Permission::orderBy('name')->get();

        return view('admin.roles.show', compact('role', 'allPermissions'));
    }

    /**
     * تحديث الصلاحيات المسندة للدور
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $oldPermissionIds = $role->permissions()->pluck('permissions.id')->toArray();
        $newPermissionIds = $validated['permissions'] ?? [];

        $role->permissions()->sync($newPermissionIds);

        // توثيق تعديل الصلاحيات في سجل التدقيق الأمني
        AuditLogService::log(
            action: 'ROLE_PERMISSIONS_UPDATED',
            entity: $role,
            oldValues: ['permissions_count' => count($oldPermissionIds), 'ids' => $oldPermissionIds],
            newValues: ['permissions_count' => count($newPermissionIds), 'ids' => $newPermissionIds]
        );

        return redirect()->route('admin.roles.show', $role)->with('status', 'تم تحديث صلاحيات الدور وتوثيق التعديل بنجاح.');
    }
}