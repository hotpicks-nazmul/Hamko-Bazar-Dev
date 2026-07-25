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
        Schema::dropIfExists('banners');
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->string('link')->nullable();
            $table->string('position')->default('home_top'); // home_top, home_middle, home_bottom, sidebar, category_page
            $table->text('description')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_color')->nullable()->default('#000000');
            $table->string('text_color')->nullable()->default('#ffffff');
            $table->string('background_color')->nullable()->default('#ffffff');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
