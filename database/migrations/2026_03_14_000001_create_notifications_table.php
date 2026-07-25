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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // order, review, stock, refund, system, etc.
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable(); // bi bi-icon class
            $table->string('link')->nullable(); // URL to redirect
            $table->foreignId('notifiable_id')->nullable(); // User ID (for frontend users)
            $table->string('notifiable_type')->nullable(); // User model (for frontend users)
            $table->boolean('is_read')->default(false);
            $table->boolean('is_for_admin')->default(true); // True for admin notifications
            $table->json('data')->nullable(); // Additional data like order_id, product_id, etc.
            $table->timestamps();
            
            $table->index(['is_for_admin', 'is_read', 'created_at']);
            $table->index(['notifiable_id', 'notifiable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
