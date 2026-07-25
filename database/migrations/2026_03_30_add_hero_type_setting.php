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
        // Check if hero_type setting already exists
        $exists = DB::table('settings')
            ->where('key', 'hero_type')
            ->where('group', 'hero')
            ->exists();

        if (!$exists) {
            DB::table('settings')->insert([
                'key' => 'hero_type',
                'value' => 'standard',
                'group' => 'hero',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'hero_type')
            ->where('group', 'hero')
            ->delete();
    }
};