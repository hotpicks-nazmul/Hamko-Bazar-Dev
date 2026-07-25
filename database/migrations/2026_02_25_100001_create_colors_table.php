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
        // Colors table for product color variations
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 10)->unique(); // Short code like 'RED', 'BLU', 'GRN'
            $table->string('hex_code', 7)->default('#000000'); // Hex color code like #FF0000
            $table->string('image')->nullable(); // Optional color swatch image
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Product Colors pivot table (link products to colors)
        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('color_id')->constrained()->onDelete('cascade');
            $table->string('image')->nullable(); // Product image for this specific color
            $table->integer('quantity')->default(0); // Stock quantity for this color variant
            $table->decimal('price_adjustment', 10, 2)->default(0); // Price adjustment for this color
            $table->string('sku')->nullable(); // SKU for this color variant
            $table->timestamps();

            $table->unique(['product_id', 'color_id'], 'product_color_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_colors');
        Schema::dropIfExists('colors');
    }
};
