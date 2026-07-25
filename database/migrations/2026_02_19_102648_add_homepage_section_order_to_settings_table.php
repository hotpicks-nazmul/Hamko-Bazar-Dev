<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert default homepage section order setting
        DB::table('settings')->insertOrIgnore([
            'key' => 'homepage_section_order',
            'value' => json_encode([
                'categories',
                'featured',
                'banner',
                'new_arrivals',
                'why_choose_us',
                'sale',
                'testimonials',
                'blog'
            ]),
            'group' => 'homepage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'homepage_section_order')->delete();
    }
};
