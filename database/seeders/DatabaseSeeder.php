<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // بيانات مرجعية (آمنة لإعادة التشغيل)
        $this->call([
            RolesAndPermissionsSeeder::class,
            SettingsSeeder::class,
            UsersSeeder::class,
            RulesAndWatchlistSeeder::class,
            IntegrationSeeder::class,
            SimulatorSeeder::class,
        ]);

        // بيانات تجريبية (فقط بيئة التطوير)
        if (app()->environment('local', 'testing')) {
            $this->call([
                DemoDataSeeder::class,
                AhmadScenarioSeeder::class,
                WorkflowDataSeeder::class,

            ]);
        }
    }
}