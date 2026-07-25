<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add seller_id to differentiate between in-house products and seller products.
     * In-house products have seller_id = NULL, seller products have seller_id set.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            $table->enum('product_source', ['in_house', 'seller'])->default('in_house')->after('seller_id');
            $table->decimal('purchase_price', 10, 2)->nullable()->after('cost_price');
            $table->string('barcode')->nullable()->after('sku');
            $table->string('brand')->nullable()->after('category_id');
            $table->integer('low_stock_threshold')->default(10)->after('quantity');
            $table->date('stock_update_date')->nullable()->after('low_stock_threshold');
            $table->boolean('is_approved')->default(true)->after('is_active');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
            
            // Indexes for faster queries
            $table->index(['seller_id', 'is_active']);
            $table->index(['product_source', 'is_active']);
            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn([
                'seller_id',
                'product_source',
                'purchase_price',
                'barcode',
                'brand',
                'low_stock_threshold',
                'stock_update_date',
                'is_approved',
                'approved_at'
            ]);
        });
    }
};
