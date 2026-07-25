<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('combination_key', 255); // e.g., "color_5_attr_12_attr_15"
            $table->string('image');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['product_id', 'combination_key']);
            $table->index(['product_id', 'combination_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_images');
    }
};