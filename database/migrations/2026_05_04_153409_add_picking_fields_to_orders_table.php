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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('packed_at')->nullable()->after('notes');
            $table->foreignId('packed_by')->nullable()->constrained('users')->nullOnDelete()->after('packed_at');
            $table->timestamp('picking_started_at')->nullable()->after('packed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['packed_by']);
            $table->dropColumn(['packed_at', 'packed_by', 'picking_started_at']);
        });
    }
};