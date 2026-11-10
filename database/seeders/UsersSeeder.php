<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /** كلمة سر موحّدة للتطوير فقط (بيانات اصطناعية) */
    public const DEV_PASSWORD = 'password';

    public function run(): void
    {
        $users = [
            ['مدير النظام', 'admin@bank.test', 'admin'],
            ['ليلى المحللة', 'analyst@bank.test', 'fraud_analyst'],
            ['سامر المشرف', 'supervisor@bank.test', 'supervisor'],
            ['المساعد الذكي', 'assistant@bank.test', 'ai_assistant'],
            ['سارة العميلة', 'customer@bank.test', 'customer'],
        ];

        foreach ($users as [$name, $email, $roleName]) {
            self::makeUser($name, $email, $roleName);
        }
    }

    public static function makeUser(string $name, string $email, string $roleName): User
    {
        $user = User::firstOrNew(['email' => $email]);
        // forceFill لأن email_verified_at خارج #[Fillable]
        $user->forceFill([
            'name' => $name,
            'password' => self::DEV_PASSWORD, // cast hashed يشفّرها
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        $role = Role::where('role_name', $roleName)->firstOrFail();
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }
}