<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            // ADM-04: شروط القاعدة ليقدر المحرك يطبقها
            $table->json('conditions')->nullable();
            $table->string('action')->default('add_score'); // add_score, force_otp, force_block
        });
    }

    public function down(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            $table->dropColumn(['conditions', 'action']);
        });
    }
};
