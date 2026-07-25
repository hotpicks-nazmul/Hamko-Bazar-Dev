<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // API Keys table
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key', 64)->unique();
            $table->string('secret', 128)->nullable();
            $table->string('type')->default('general'); // general, payment, shipping, sms, email, etc.
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('rate_limit')->default(100)->comment('Requests per minute');
            $table->json('permissions')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
        });

        // Webhooks table
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('event'); // order.created, order.updated, payment.completed, etc.
            $table->string('method', 10)->default('POST');
            $table->text('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('timeout')->default(30);
            $table->integer('retry_count')->default(3);
            $table->json('headers')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->timestamps();
            
            $table->index('event');
            $table->index('is_active');
        });

        // API Key usage logs table
        Schema::create('api_key_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->constrained('api_keys')->onDelete('cascade');
            $table->string('method', 10);
            $table->string('endpoint');
            $table->integer('status_code')->nullable();
            $table->integer('response_time')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index('api_key_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_key_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('api_keys');
    }
};
