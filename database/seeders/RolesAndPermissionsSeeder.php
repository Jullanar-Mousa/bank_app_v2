<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.manage' => 'إدارة المستخدمين وتفعيلهم وتعطيلهم',
            'roles.manage' => 'إدارة الأدوار والصلاحيات',
            'rules.manage' => 'إدارة قواعد الكشف',
            'settings.manage' => 'إدارة حدود القرار وإعدادات OTP',
            'api_clients.manage' => 'إدارة مفاتيح تكامل النظام المصرفي',
            'models.view' => 'عرض إصدارات نماذج الذكاء الاصطناعي',
            'audit.view' => 'عرض سجل التدقيق',
            'alerts.view' => 'عرض قائمة التنبيهات',
            'alerts.decide' => 'اتخاذ قرار على تنبيه',
            'cases.manage' => 'إدارة الحالات وملاحظاتها ومرفقاتها',
            'accounts.freeze.request' => 'طلب تجميد حساب',
            'rule_suggestions.create' => 'اقتراح قاعدة جديدة',
            'alerts.assign' => 'توزيع التنبيهات على المحللين',
            'approvals.decide' => 'الموافقة أو الرفض (مبدأ العينين)',
            'rule_suggestions.review' => 'مراجعة اقتراحات القواعد',
            'reports.view' => 'عرض تقارير الأداء والامتثال',
            'ai.propose' => 'تقديم مقترحات (بدون تنفيذ)',
            'portal.use' => 'استخدام بوابة العميل',
        ];

        foreach ($permissions as $name => $description) {
            Permission::updateOrCreate(['name' => $name], ['description' => $description]);
        }

        // فصل المهام: المدير لا يرى التنبيهات ولا يقرر عليها
        $matrix = [
            'admin' => [
                'users.manage', 'roles.manage', 'rules.manage', 'settings.manage',
                'api_clients.manage', 'models.view', 'audit.view',
            ],
            'fraud_analyst' => [
                'alerts.view', 'alerts.decide', 'cases.manage',
                'accounts.freeze.request', 'rule_suggestions.create',
            ],
            'supervisor' => [
                'alerts.view', 'alerts.assign', 'cases.manage', 'approvals.decide',
                'rule_suggestions.review', 'reports.view', 'audit.view', 'models.view',
            ],
            'ai_assistant' => ['alerts.view', 'ai.propose'],
            'customer' => ['portal.use'],
        ];

        foreach ($matrix as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(['role_name' => $roleName]);
            $role->permissions()->sync(
                Permission::whereIn('name', $permissionNames)->pluck('id')
            );
        }
    }
}