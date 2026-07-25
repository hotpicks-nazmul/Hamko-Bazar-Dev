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
        $settings = [
            // Why Choose Us Section Settings
            ['key' => 'why_choose_us_title', 'value' => 'Why Choose Us?', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'why_choose_us_subtitle', 'value' => 'We are committed to providing the best halal products', 'group' => 'homepage', 'type' => 'text'],
            
            // Feature 1
            ['key' => 'why_choose_us_icon_1', 'value' => 'bi-patch-check-fill', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'why_choose_us_title_1', 'value' => '100% Halal Certified', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'why_choose_us_desc_1', 'value' => 'All our products are certified halal by recognized Islamic authorities', 'group' => 'homepage', 'type' => 'text'],
            
            // Feature 2
            ['key' => 'why_choose_us_icon_2', 'value' => 'bi-thermometer-snow', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'why_choose_us_title_2', 'value' => 'Fresh & Cold Storage', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'why_choose_us_desc_2', 'value' => 'Maintained at optimal temperature from farm to your door', 'group' => 'homepage', 'type' => 'text'],
            
            // Feature 3
            ['key' => 'why_choose_us_icon_3', 'value' => 'bi-truck', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'why_choose_us_title_3', 'value' => 'Fast Delivery', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'why_choose_us_desc_3', 'value' => 'Same day delivery in Dhaka, 1-2 days nationwide', 'group' => 'homepage', 'type' => 'text'],
            
            // Feature 4
            ['key' => 'why_choose_us_icon_4', 'value' => 'bi-hand-thumbs-up-fill', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'why_choose_us_title_4', 'value' => 'Quality Guarantee', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'why_choose_us_desc_4', 'value' => 'Not satisfied? We offer easy returns and refunds', 'group' => 'homepage', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
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
        DB::table('settings')->where('group', 'homepage')
            ->where('key', 'like', 'why_choose_us_%')
            ->delete();
    }
};
