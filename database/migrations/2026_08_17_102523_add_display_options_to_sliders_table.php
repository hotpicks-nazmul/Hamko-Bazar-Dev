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
        Schema::table('sliders', function (Blueprint $table) {
            $table->tinyInteger('show_title')->default(1)->after('button_icon_color');
            $table->tinyInteger('show_subtitle')->default(1)->after('show_title');
            $table->tinyInteger('show_button')->default(1)->after('show_subtitle');
            $table->tinyInteger('show_gradient')->default(1)->after('show_button');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['show_title', 'show_subtitle', 'show_button', 'show_gradient']);
        });
    }
};
