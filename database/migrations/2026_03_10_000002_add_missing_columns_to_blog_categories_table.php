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
        Schema::table('blog_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_categories', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('blog_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('image');
            }
            if (!Schema::hasColumn('blog_categories', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('sort_order');
            }
            if (!Schema::hasColumn('blog_categories', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('status');
            }
            if (!Schema::hasColumn('blog_categories', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('blog_categories', 'meta_keywords')) {
                $table->string('meta_keywords')->nullable()->after('meta_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropColumn([
                'image',
                'sort_order',
                'status',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ]);
        });
    }
};
