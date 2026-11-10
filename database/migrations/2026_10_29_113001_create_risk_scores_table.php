<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('account_events')->nullOnDelete();
            $table->foreignId('model_run_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('score'); // 0-100
            $table->string('decision'); // allow, otp_required, block
            $table->json('reasons')->nullable(); // أسباب التقيم SHAP Values
            $table->timestamp('evaluated_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_scores');
    }
};