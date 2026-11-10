<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AN-07 / EXT-03: سجل إجراءات الإيقاف والتجميد
        Schema::create('account_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('alert_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approval_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // freeze, unfreeze, block_transaction, require_otp
            $table->string('status')->default('pending'); // pending, executed, failed, rejected
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete(); // null = النظام
            $table->text('reason')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_actions');
    }
};
