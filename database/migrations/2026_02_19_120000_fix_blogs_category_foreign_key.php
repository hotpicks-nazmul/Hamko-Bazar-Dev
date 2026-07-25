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
        // Drop the existing foreign key constraint
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
        
        // Change the foreign key to reference categories table instead of blog_categories
        Schema::table('blogs', function (Blueprint $table) {
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
        
        Schema::table('blogs', function (Blueprint $table) {
            $table->foreign('category_id')
                  ->references('id')
                  ->on('blog_categories')
                  ->onDelete('set null');
        });
    }
};
