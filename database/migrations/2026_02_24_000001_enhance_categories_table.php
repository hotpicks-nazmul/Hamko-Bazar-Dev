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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('show_in_menu')->default(true)->after('is_featured');
            $table->boolean('show_in_homepage')->default(false)->after('show_in_menu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['meta_keywords', 'is_featured', 'show_in_menu', 'show_in_homepage']);
        });
    }
};
