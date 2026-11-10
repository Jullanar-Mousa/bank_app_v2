<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use App\Models\MlModel;
use App\Models\ModelRun;
use App\Models\User;
use Illuminate\Database\Seeder;

class IntegrationSeeder extends Seeder
{
    public function run(): void
    {
        $model = MlModel::updateOrCreate(
            ['model_name' => 'fraud-detector'],
            ['algorithm' => 'XGBoost', 'status' => 'Champion']
        );

        ModelRun::updateOrCreate(
            ['model_id' => $model->id, 'version_number' => '0.1.0'],
            [
                'metrics' => ['precision' => 0.91, 'recall' => 0.84, 'auc' => 0.96, 'note' => 'قيم تجريبية'],
                'trained_at' => now()->subDays(7),
            ]
        );

        // النظام المصرفي المحاكى. السر للتطوير فقط.
        ApiClient::updateOrCreate(
            ['key_id' => 'core-bank-sim'],
            [
                'name' => 'النظام المصرفي (محاكى)',
                'secret' => 'dev-secret-change-me',
                'is_active' => true,
                'created_by' => User::where('email', 'admin@bank.test')->value('id'),
            ]
        );
    }
}