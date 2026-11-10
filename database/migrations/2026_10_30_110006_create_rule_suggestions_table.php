<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AN-13 / SUP-05: اقتراح قاعدة جديدة
        Schema::create('rule_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('conditions')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->foreignId('rule_id')->nullable()->constrained('rules')->nullOnDelete(); // القاعدة الناتجة بعد القبول
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_suggestions');
    }
};
