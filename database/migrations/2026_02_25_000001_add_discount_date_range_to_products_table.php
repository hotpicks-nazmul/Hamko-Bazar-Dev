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
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('discount_starts_at')->nullable()->after('sale_price');
            $table->timestamp('discount_ends_at')->nullable()->after('discount_starts_at');
            
            // Add index for querying active discounts
            $table->index(['discount_starts_at', 'discount_ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['discount_starts_at', 'discount_ends_at']);
            $table->dropColumn(['discount_starts_at', 'discount_ends_at']);
        });
    }
};
