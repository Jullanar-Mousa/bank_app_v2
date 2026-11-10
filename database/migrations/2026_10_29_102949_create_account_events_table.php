<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type'); // login, change_phone, add_beneficiary
            $table->ipAddress('ip_address')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('event_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_events');
    }
};