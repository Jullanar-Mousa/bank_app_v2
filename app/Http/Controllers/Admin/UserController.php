<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * عرض قائمة مستخدمي النظام مع التصفية والبحث
     */
    public function index(Request $request): View
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('role_name', $request->input('role'));
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();
        $roles = Role::orderBy('role_name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * نموذج إضافة مستخدم جديد للنظام
     */
    public function create(): View
    {
        $roles = Role::orderBy('role_name')->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * حفظ المستخدم الجديد وإسناد أدواره
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'is_active' => ['boolean'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->roles()->sync($validated['roles']);

        AuditLogService::log(
            action: 'USER_CREATED',
            entity: $user,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $validated['roles'],
            ]
        );

        return redirect()->route('admin.users.index')->with('status', 'تم إنشاء المستخدم وإسناد أدواره بنجاح.');
    }

    /**
     * نموذج تعديل بيانات وأدوار المستخدم
     */
    public function edit(User $user): View
    {
        $roles = Role::orderBy('role_name')->get();
        $user->load('roles');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * تحديث بيانات وأدوار المستخدم
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'is_active' => ['boolean'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);
        $user->roles()->sync($validated['roles']);

        AuditLogService::log(
            action: 'USER_UPDATED',
            entity: $user,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'roles' => $validated['roles'],
            ]
        );

        return redirect()->route('admin.users.index')->with('status', 'تم تحديث بيانات المستخدم وأدواره بنجاح.');
    }

    /**
     * حذف مستخدم وإلغاء أدواره
     */
    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        $userName = $user->name;
        $userEmail = $user->email;

        $user->roles()->detach();
        $user->delete();

        AuditLogService::log(
            action: 'USER_DELETED',
            entity: $user,
            oldValues: ['name' => $userName, 'email' => $userEmail]
        );

        return redirect()->route('admin.users.index')->with('status', 'تم حذف المستخدم بنجاح.');
    }
}