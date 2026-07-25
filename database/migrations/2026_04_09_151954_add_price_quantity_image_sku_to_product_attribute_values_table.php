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
        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('attribute_value_id');
            $table->integer('quantity')->default(0)->after('price');
            $table->string('image')->nullable()->after('quantity');
            $table->string('sku')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->dropColumn(['price', 'quantity', 'image', 'sku']);
        });
    }
};
