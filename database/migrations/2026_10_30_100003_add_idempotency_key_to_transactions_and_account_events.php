<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // EXT-06: منع تكرار معالجة نفس الحدث
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->unique();
        });

        Schema::table('account_events', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });

        Schema::table('account_events', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
