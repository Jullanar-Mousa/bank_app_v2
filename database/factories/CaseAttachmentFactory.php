<?php

namespace Database\Factories;

use App\Models\CaseAttachment;
use App\Models\FraudCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseAttachment>
 */
class CaseAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->slug(2).'.png';

        return [
            'case_id' => FraudCase::factory(),
            'uploaded_by' => null,
            'file_name' => $name,
            'file_path' => 'case-attachments/seed/'.$name, // مسار وهمي، الملف غير موجود فعلياً
            'mime_type' => 'image/png',
            'file_size' => fake()->numberBetween(20_000, 500_000),
        ];
    }
}