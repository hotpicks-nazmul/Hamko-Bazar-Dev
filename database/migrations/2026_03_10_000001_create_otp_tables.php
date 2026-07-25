<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });
        
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->string('otp', 10);
            $table->string('purpose', 50)->default('verification'); // verification, login, registration, password_reset, payment, order
            $table->string('status', 20)->default('pending'); // pending, verified, expired, failed
            $table->integer('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['phone', 'purpose']);
            $table->index('expires_at');
        });
        
        Schema::create('otp_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->string('message', 500);
            $table->string('status', 20)->default('pending'); // pending, sent, delivered, failed
            $table->string('gateway', 50)->nullable();
            $table->string('gateway_response')->nullable();
            $table->string('otp_code', 10)->nullable();
            $table->string('purpose', 50)->nullable();
            $table->timestamps();
            
            $table->index('phone');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_sms_logs');
        Schema::dropIfExists('otp_verifications');
        Schema::dropIfExists('otp_configurations');
    }
};
