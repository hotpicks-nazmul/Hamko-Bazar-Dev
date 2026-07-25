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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duration_days')->default(365); // Duration in days
            $table->decimal('discount_percentage', 5, 2)->default(0); // Discount on orders
            $table->decimal('minimum_spent', 10, 2)->default(0); // Minimum spend required
            $table->text('benefits')->nullable(); // JSON or text benefits
            $table->string('icon')->nullable(); // Icon class or image path
            $table->string('color')->default('#6c757d'); // Plan color for UI
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('max_members')->nullable()->comment('Maximum members allowed, null for unlimited');
            $table->integer('members_count')->default(0)->comment('Current number of members');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
            
            $table->index('slug');
            $table->index(['is_active', 'sort_order']);
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
