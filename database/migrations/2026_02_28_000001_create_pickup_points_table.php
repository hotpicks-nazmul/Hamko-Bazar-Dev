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
        Schema::create('pickup_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->default('Bangladesh');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('opening_hours')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'city']);
        });

        // Add pickup_point_id to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('pickup_point_id')->nullable()->after('seller_id')->constrained('pickup_points')->onDelete('set null');
            $table->timestamp('picked_up_at')->nullable()->after('pickup_point_id');
            $table->string('picked_up_by')->nullable()->after('picked_up_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pickup_point_id']);
            $table->dropColumn(['pickup_point_id', 'picked_up_at', 'picked_up_by']);
        });

        Schema::dropIfExists('pickup_points');
    }
};
