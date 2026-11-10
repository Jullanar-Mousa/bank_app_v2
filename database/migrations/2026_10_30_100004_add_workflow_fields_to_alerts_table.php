<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->string('priority')->default('medium'); // low, medium, high (SYS-05)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // SUP-01
            // AN-06: قرار المحلل
            $table->string('decision')->nullable(); // confirmed_fraud, false_positive, escalated, info_requested
            $table->text('decision_reason')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();

            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropIndex(['status', 'priority']);
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropConstrainedForeignId('decided_by');
            $table->dropColumn(['priority', 'decision', 'decision_reason', 'decided_at']);
        });
    }
};
