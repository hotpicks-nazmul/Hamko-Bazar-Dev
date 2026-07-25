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
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->integer('min_quantity')->default(1);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('priority')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->json('conditions')->nullable();
            $table->timestamps();
        });

        // Create pivot table for price_rules and products
        Schema::create('price_rule_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_rule_id')->constrained('price_rules')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('discount', 10, 2)->nullable();
            $table->string('discount_type')->default('percent');
            $table->timestamps();
            
            $table->unique(['price_rule_id', 'product_id']);
        });

        // Create pivot table for price_rules and categories
        Schema::create('price_rule_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_rule_id')->constrained('price_rules')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['price_rule_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_rule_categories');
        Schema::dropIfExists('price_rule_products');
        Schema::dropIfExists('price_rules');
    }
};
