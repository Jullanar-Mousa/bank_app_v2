<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('ml_models')->cascadeOnDelete();
            $table->string('version_number');
            $table->json('metrics')->nullable(); // Precision, Recall, AUC
            $table->timestamp('trained_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_runs');
    }
};