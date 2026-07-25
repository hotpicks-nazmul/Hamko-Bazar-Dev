<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alt text for the images that are not products: a banner or a category tile has no name to fall
 * back on, so its alt had to be the title (or nothing). Nullable, so every existing row and every
 * view that reads `alt ?: title` keeps working untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            if (! Schema::hasColumn('sliders', 'alt')) {
                $table->string('alt')->nullable()->after('image');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'alt')) {
                $table->string('alt')->nullable()->after('image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sliders', fn (Blueprint $t) => $t->dropColumn('alt'));
        Schema::table('categories', fn (Blueprint $t) => $t->dropColumn('alt'));
    }
};
