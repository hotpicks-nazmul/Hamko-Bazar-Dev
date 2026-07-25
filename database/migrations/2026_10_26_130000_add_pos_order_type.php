<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The POS module writes `orders.order_type = 'pos'` and both POS report screens query
 * `where('order_type', 'pos')`, while the column was enum('inhouse','seller','pickup_point'):
 *
 *   - checkout answered SQLSTATE 01000 "1265 Data truncated for column 'order_type'" — no POS sale could
 *     ever be recorded;
 *   - Cash Register and POS Reports filtered on a value the column could not hold, so they were empty by
 *     construction for every date range.
 *
 * Appending the value is in-place and changes nothing that already exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY `order_type`
            ENUM('inhouse','seller','pickup_point','pos') NOT NULL DEFAULT 'inhouse'");
    }

    public function down(): void
    {
        // Rows still holding the removed value must move first or MySQL truncates them on the way down.
        DB::table('orders')->where('order_type', 'pos')->update(['order_type' => 'inhouse']);

        DB::statement("ALTER TABLE `orders` MODIFY `order_type`
            ENUM('inhouse','seller','pickup_point') NOT NULL DEFAULT 'inhouse'");
    }
};
