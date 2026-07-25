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
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('password');
            $table->string('provider_id')->nullable()->after('provider');
        });

        // Insert social login settings
        DB::table('settings')->insert([
            [
                'group' => 'social_login',
                'key' => 'google_client_id',
                'value' => '',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'social_login',
                'key' => 'google_client_secret',
                'value' => '',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'social_login',
                'key' => 'google_enabled',
                'value' => '0',
                'type' => 'checkbox',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'social_login',
                'key' => 'facebook_client_id',
                'value' => '',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'social_login',
                'key' => 'facebook_client_secret',
                'value' => '',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'social_login',
                'key' => 'facebook_enabled',
                'value' => '0',
                'type' => 'checkbox',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_id']);
        });

        DB::table('settings')->where('group', 'social_login')->delete();
    }
};
