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
        $heroSettings = [
            // Hero Background
            ['key' => 'hero_background_image', 'value' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1920', 'group' => 'hero', 'type' => 'image'],
            
            // Hero Badge
            ['key' => 'hero_badge_icon', 'value' => 'bi bi-patch-check-fill', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_badge_text', 'value' => 'Trusted by 10,000+ Customers', 'group' => 'hero', 'type' => 'text'],
            
            // Hero Main Content
            ['key' => 'hero_title_line1', 'value' => 'Fresh', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_title_highlight1', 'value' => 'Halal Food', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_title_line2', 'value' => 'Delivered Fresh', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_title_line3', 'value' => 'To Your Door', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_description', 'value' => 'Premium quality halal meat, poultry, seafood & groceries. 100% certified halal, farm-fresh, delivered within hours across Bangladesh.', 'group' => 'hero', 'type' => 'textarea'],
            
            // CTA Buttons
            ['key' => 'hero_cta1_text', 'value' => 'Shop Now', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_cta1_link', 'value' => 'products.index', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_cta1_icon', 'value' => 'bi bi-cart3', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_cta2_text', 'value' => 'Hot Deals', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_cta2_link', 'value' => 'products.index', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_cta2_params', 'value' => '{"sort":"discount"}', 'group' => 'hero', 'type' => 'json'],
            ['key' => 'hero_cta2_icon', 'value' => 'bi bi-fire', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_cta2_badge', 'value' => 'UP TO 30% OFF', 'group' => 'hero', 'type' => 'text'],
            
            // Hero Main Image
            ['key' => 'hero_main_image', 'value' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=600', 'group' => 'hero', 'type' => 'image'],
            ['key' => 'hero_main_image_alt', 'value' => 'Fresh Halal Meat', 'group' => 'hero', 'type' => 'text'],
            
            // Floating Card - Today's Special
            ['key' => 'hero_special_label', 'value' => "Today's Special", 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_special_title', 'value' => 'Premium Beef - 20% OFF', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_special_link', 'value' => 'products.index', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_special_params', 'value' => '{"category":"fresh-meat"}', 'group' => 'hero', 'type' => 'json'],
            ['key' => 'hero_special_button', 'value' => 'Order Now', 'group' => 'hero', 'type' => 'text'],
            
            // Floating Card - Delivery Time
            ['key' => 'hero_delivery_icon', 'value' => 'bi bi-clock', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_delivery_label', 'value' => 'Delivery Time', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_delivery_value', 'value' => '30-60 Min', 'group' => 'hero', 'type' => 'text'],
            
            // Floating Card - Happy Customers
            ['key' => 'hero_customers_label', 'value' => 'Happy', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_customers_value', 'value' => 'Customers', 'group' => 'hero', 'type' => 'text'],
            
            // Features Bar
            ['key' => 'hero_feature1_icon', 'value' => 'bi bi-truck', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature1_title', 'value' => 'Free Delivery', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature1_subtitle', 'value' => 'Orders over Tk500', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature2_icon', 'value' => 'bi bi-shield-check', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature2_title', 'value' => '100% Halal', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature2_subtitle', 'value' => 'Certified Quality', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature3_icon', 'value' => 'bi bi-cash-coin', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature3_title', 'value' => 'Best Prices', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature3_subtitle', 'value' => 'Guaranteed', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature4_icon', 'value' => 'bi bi-headset', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature4_title', 'value' => '24/7 Support', 'group' => 'hero', 'type' => 'text'],
            ['key' => 'hero_feature4_subtitle', 'value' => 'Always Here', 'group' => 'hero', 'type' => 'text'],
        ];

        foreach ($heroSettings as $setting) {
            DB::table('settings')->insert(array_merge($setting, ['created_at' => now(), 'updated_at' => now()]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('group', 'hero')->delete();
    }
};