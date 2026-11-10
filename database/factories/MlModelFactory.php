<?php

namespace Database\Factories;

use App\Models\MlModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MlModel>
 */
class MlModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'model_name' => fake()->unique()->slug(2),
            'algorithm' => fake()->randomElement(['XGBoost', 'Random Forest', 'Isolation Forest']),
            'status' => fake()->randomElement(['Champion', 'Shadow', 'Retired']),
        ];
    }
}