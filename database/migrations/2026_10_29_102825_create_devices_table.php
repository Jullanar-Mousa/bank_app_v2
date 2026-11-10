<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_fingerprint')->unique();
            $table->string('device_type')->nullable(); // موبايل، ويب
            $table->timestamp('sim_changed_at')->nullable(); // آخر تغيير للشريحة
            $table->ipAddress('last_ip')->nullable();
            $table->string('last_location')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};