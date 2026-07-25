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
        // Insert blog settings into the settings table
        $settings = [
            ['key' => 'blog_title', 'value' => 'Blog', 'group' => 'blog', 'type' => 'text'],
            ['key' => 'blog_subtitle', 'value' => 'Discover our latest articles and insights', 'group' => 'blog', 'type' => 'text'],
            ['key' => 'blog_posts_per_page', 'value' => '9', 'group' => 'blog', 'type' => 'text'],
            ['key' => 'blog_show_author', 'value' => '1', 'group' => 'blog', 'type' => 'checkbox'],
            ['key' => 'blog_show_date', 'value' => '1', 'group' => 'blog', 'type' => 'checkbox'],
            ['key' => 'blog_show_category', 'value' => '1', 'group' => 'blog', 'type' => 'checkbox'],
            ['key' => 'blog_show_tags', 'value' => '1', 'group' => 'blog', 'type' => 'checkbox'],
            ['key' => 'blog_show_share_buttons', 'value' => '1', 'group' => 'blog', 'type' => 'checkbox'],
            ['key' => 'blog_show_related_posts', 'value' => '1', 'group' => 'blog', 'type' => 'checkbox'],
            ['key' => 'blog_related_posts_count', 'value' => '4', 'group' => 'blog', 'type' => 'text'],
            ['key' => 'blog_sidebar_show_search', 'value' => '1', 'group' => 'blog', 'type' => 'checkbox'],
            ['key' => 'blog_sidebar_show_categories', 'value' => '1', 'group' => 'blog', 'type' => 'checkbox'],
        ];

        foreach ($settings as $setting) {
            \DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('settings')->where('group', 'blog')->delete();
    }
};
