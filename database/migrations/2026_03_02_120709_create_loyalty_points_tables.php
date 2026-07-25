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
        // Add loyalty_points column to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'loyalty_points')) {
                $table->integer('loyalty_points')->default(0)->after('customer_group_id');
            }
            if (!Schema::hasColumn('users', 'loyalty_points_spent')) {
                $table->integer('loyalty_points_spent')->default(0)->after('loyalty_points');
            }
            if (!Schema::hasColumn('users', 'total_spent')) {
                $table->decimal('total_spent', 12, 2)->default(0)->after('loyalty_points_spent');
            }
        });

        // Create loyalty_points_transactions table
        Schema::create('loyalty_points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points');
            $table->integer('points_balance');
            $table->string('type', 50);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create loyalty_rewards table
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points_required');
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->string('reward_type', 50);
            $table->string('code')->nullable()->unique();
            $table->integer('max_redemptions')->nullable();
            $table->integer('redemption_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
        });

        // Create loyalty_settings table
        Schema::create('loyalty_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_settings');
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_points_transactions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points', 'loyalty_points_spent', 'total_spent']);
        });
    }
};
