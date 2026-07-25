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
        // Add wallet columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('wallet_balance', 10, 2)->default(0);
            $table->decimal('wallet_points', 10, 2)->default(0);
        });

        // Create wallet transactions table
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->enum('source', ['admin', 'order', 'refund', 'payment', 'other'])->default('other');
            $table->string('description')->nullable();
            $table->string('reference_id')->nullable();
            $table->decimal('balance_after', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wallet_balance', 'wallet_points']);
        });
    }
};
