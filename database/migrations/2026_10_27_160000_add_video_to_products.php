<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One optional clip per product, shown in the product gallery. Nullable: every existing product keeps
 * rendering exactly as it does now. The host has no ffmpeg, so nothing is transcoded — the upload cap
 * and the "poster = the featured photo" rule are what keep this from becoming a 60 MB page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'video')) {
                $table->string('video')->nullable()->after('featured_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('video'));
    }
};
