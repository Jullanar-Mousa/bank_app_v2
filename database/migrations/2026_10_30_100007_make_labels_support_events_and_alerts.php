<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            // الوسم قد يكون على حدث غير مالي (دخول، تغيير جوال) وليس عملية فقط
            $table->unsignedBigInteger('transaction_id')->nullable()->change();
            $table->foreignId('event_id')->nullable()->constrained('account_events')->nullOnDelete();
            $table->foreignId('alert_id')->nullable()->constrained('alerts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('labels')->whereNull('transaction_id')->delete();

        Schema::table('labels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_id');
            $table->dropConstrainedForeignId('alert_id');
            $table->unsignedBigInteger('transaction_id')->nullable(false)->change();
        });
    }
};
