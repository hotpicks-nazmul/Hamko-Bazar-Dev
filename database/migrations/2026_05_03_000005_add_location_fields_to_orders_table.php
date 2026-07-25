<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('billing_city_id')->nullable()->constrained('cities')->nullOnDelete()->after('billing_country');
            $table->foreignId('shipping_city_id')->nullable()->constrained('cities')->nullOnDelete()->after('shipping_country');
            $table->foreignId('billing_area_id')->nullable()->constrained('areas')->nullOnDelete()->after('billing_city_id');
            $table->foreignId('shipping_area_id')->nullable()->constrained('areas')->nullOnDelete()->after('shipping_city_id');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete()->after('shipping_area_id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['billing_city_id']);
            $table->dropForeign(['shipping_city_id']);
            $table->dropForeign(['billing_area_id']);
            $table->dropForeign(['shipping_area_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['billing_city_id', 'shipping_city_id', 'billing_area_id', 'shipping_area_id', 'warehouse_id']);
        });
    }
};
