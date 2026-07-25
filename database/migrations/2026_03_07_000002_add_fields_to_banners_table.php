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
        Schema::table('banners', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('banners', 'position')) {
                $table->string('position')->default('home_top')->after('link');
            }
            if (!Schema::hasColumn('banners', 'description')) {
                $table->text('description')->nullable()->after('position');
            }
            if (!Schema::hasColumn('banners', 'button_color')) {
                $table->string('button_color')->nullable()->default('#000000')->after('button_text');
            }
            if (!Schema::hasColumn('banners', 'text_color')) {
                $table->string('text_color')->nullable()->default('#ffffff')->after('button_color');
            }
            if (!Schema::hasColumn('banners', 'background_color')) {
                $table->string('background_color')->nullable()->default('#ffffff')->after('text_color');
            }
            if (!Schema::hasColumn('banners', 'sort_order')) {
                // Rename 'order' to 'sort_order' if 'order' exists, otherwise add 'sort_order'
                if (Schema::hasColumn('banners', 'order')) {
                    $table->renameColumn('order', 'sort_order');
                } else {
                    $table->integer('sort_order')->default(0)->after('is_active');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn([
                'position',
                'description',
                'button_color',
                'text_color',
                'background_color',
            ]);
            if (Schema::hasColumn('banners', 'sort_order')) {
                $table->renameColumn('sort_order', 'order');
            }
        });
    }
};
