<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The single "product video" column was the wrong model: a clip is media, and media lives in the
 * product's gallery list with the photographs. Every value is NULL (nothing was ever saved to it),
 * so dropping it loses nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'video')) {
            Schema::table('products', fn (Blueprint $table) => $table->dropColumn('video'));
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'video')) {
                $table->string('video')->nullable()->after('featured_image');
            }
        });
    }
};
