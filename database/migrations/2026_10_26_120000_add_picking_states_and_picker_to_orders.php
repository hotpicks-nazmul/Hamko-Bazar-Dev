<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The warehouse picking workflow was written against a schema that was never migrated:
 *
 *  - `orders.status` is enum('pending','processing','confirmed','shipped','delivered','cancelled','refunded'),
 *    while WarehouseController (and Order::isPicking()/isPacked(), the status label/colour maps and the
 *    order-row partials) use 'ready_to_pick', 'picking' and 'packed'. Every write of those values is
 *    SQLSTATE 01000 "1265 Data truncated for column 'status'".
 *  - `orders.picked_by` does not exist, though startPicking() writes it and Order::pickedBy() reads it:
 *    SQLSTATE 42S22 "Unknown column 'picked_by'".
 *
 * Both are additive: three enum values are appended (in-place, no row rewrite) and one nullable column is
 * added, so nothing that already exists changes meaning.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM(
            'pending','processing','confirmed','shipped','delivered','cancelled','refunded',
            'ready_to_pick','picking','packed'
        ) NOT NULL DEFAULT 'pending'");

        if (! Schema::hasColumn('orders', 'picked_by')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('picked_by')->nullable()->after('picking_started_at');
                $table->foreign('picked_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'picked_by')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['picked_by']);
                $table->dropColumn('picked_by');
            });
        }

        // Rows still holding one of the removed values must move first, or MySQL truncates them on the
        // way down; 'picking'/'ready_to_pick' return to 'processing' and 'packed' to 'confirmed'.
        DB::table('orders')->whereIn('status', ['ready_to_pick', 'picking'])->update(['status' => 'processing']);
        DB::table('orders')->where('status', 'packed')->update(['status' => 'confirmed']);

        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM(
            'pending','processing','confirmed','shipped','delivered','cancelled','refunded'
        ) NOT NULL DEFAULT 'pending'");
    }
};
