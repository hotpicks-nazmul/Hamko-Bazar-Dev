<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two columns the delivery module already declared but the table never had.
 *
 * delivered_at  — getAverageDeliveryTime()/getDeliveryTimeAnalysis() had to measure from
 *                 picked_up_at (written by the pick-up flow only) and fall back to updated_at,
 *                 so "average delivery time" was really "time since the last row touch". Every
 *                 order that reached delivered now carries its own timestamp.
 *
 * delivery_boy_id — DeliveryBoy::orders() is declared as hasMany(Order::class,
 *                 'delivery_boy_id') and the column did not exist, so that relation threw a
 *                 SQLSTATE 42S22 the moment anything used it. The column is nullable and no
 *                 assignment UI exists yet; it makes the declared relation true instead of a
 *                 landmine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('picked_up_at');
            }
            if (!Schema::hasColumn('orders', 'delivery_boy_id')) {
                $table->unsignedBigInteger('delivery_boy_id')->nullable()->after('delivered_at');
                $table->index('delivery_boy_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_boy_id')) {
                $table->dropIndex(['delivery_boy_id']);
                $table->dropColumn('delivery_boy_id');
            }
            if (Schema::hasColumn('orders', 'delivered_at')) {
                $table->dropColumn('delivered_at');
            }
        });
    }
};
