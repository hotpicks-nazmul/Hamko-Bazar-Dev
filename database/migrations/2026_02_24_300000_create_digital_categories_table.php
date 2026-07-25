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
        Schema::create('digital_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('digital_categories')->onDelete('set null');
        });

        // Add digital_category_id to products table
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('digital_category_id')->nullable()->after('category_id');
            $table->foreign('digital_category_id')->references('id')->on('digital_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['digital_category_id']);
            $table->dropColumn('digital_category_id');
        });
        
        Schema::dropIfExists('digital_categories');
    }
};
