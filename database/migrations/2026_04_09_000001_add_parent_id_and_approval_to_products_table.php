<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('brand_id')->constrained('products')->onDelete('cascade');
            $table->string('product_code')->nullable()->change();
            $table->string('barcode')->nullable()->change();
            $table->string('brand')->nullable()->change();
            $table->string('purchase_price')->nullable()->change();
            $table->boolean('is_approved')->default(true)->after('is_active');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->dropColumn('is_approved');
            $table->dropColumn('approved_at');
        });
    }
};