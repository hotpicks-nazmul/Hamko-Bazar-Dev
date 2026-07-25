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
        // Add ip_address column if it doesn't exist
        if (!Schema::hasColumn('activity_logs', 'ip_address')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->string('ip_address', 45)->nullable()->after('properties');
            });
        }

        // Add user_agent column if it doesn't exist
        if (!Schema::hasColumn('activity_logs', 'user_agent')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->string('user_agent', 500)->nullable()->after('ip_address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a safe migration - we don't want to remove columns if they exist
    }
};
