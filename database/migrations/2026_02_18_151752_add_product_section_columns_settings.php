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
            // Product section column settings
            ['key' => 'homepage_featured_columns', 'value' => '6', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'homepage_new_arrivals_columns', 'value' => '6', 'group' => 'homepage', 'type' => 'text'],
            ['key' => 'homepage_sale_columns', 'value' => '6', 'group' => 'homepage', 'type' => 'text'],
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
        DB::table('settings')->whereIn('key', [
            'homepage_featured_columns',
            'homepage_new_arrivals_columns',
            'homepage_sale_columns',
        ])->delete();
    }
};
