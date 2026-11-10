<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_scores', function (Blueprint $table) {
            $table->unsignedSmallInteger('rule_score')->nullable();       // درجة القواعد
            $table->decimal('model_probability', 5, 4)->nullable();       // احتمال الذكاء الاصطناعي 0-1
            $table->boolean('used_fallback')->default(false);             // SYS-10: القواعد فقط عند تعطل AI
            $table->unsignedInteger('latency_ms')->nullable();            // قياس هدف 300ms
            $table->json('features')->nullable();                         // الخصائص اللحظية المستخدمة
        });
    }

    public function down(): void
    {
        Schema::table('risk_scores', function (Blueprint $table) {
            $table->dropColumn(['rule_score', 'model_probability', 'used_fallback', 'latency_ms', 'features']);
        });
    }
};
