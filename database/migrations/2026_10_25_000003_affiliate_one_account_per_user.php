<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 10 Affiliate, two data-side fixes:
 *
 * 1. `affiliates.user_id` had no unique index, and `approveRequest()` did not check either, so
 *    approving a second request for the same user created a second affiliate account with its own
 *    code and its own balance. One row per user is the invariant the money model already assumes.
 * 2. The module only ever had an `affiliate.view` permission row, and every route sat behind the bare
 *    module key — so a staff member holding `affiliate.view` could approve payouts and delete
 *    affiliates. The routes now ask for `affiliate.edit` / `affiliate.delete`; those keys have to
 *    exist in the permission table or `hasPermission()` denies them to admins too.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('affiliates')) {
            $exists = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'affiliates')
                ->where('INDEX_NAME', 'affiliates_user_id_unique')
                ->exists();

            if (! $exists) {
                // Refuse to add the index on top of existing duplicates: report what has to be
                // reconciled instead of failing with a bare SQLSTATE.
                $dupes = DB::table('affiliates')
                    ->select('user_id', DB::raw('COUNT(*) as n'))
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();

                if ($dupes->isNotEmpty()) {
                    throw new RuntimeException(
                        'affiliates has ' . $dupes->count() . ' user(s) with more than one row; ' .
                        'merge them before this migration can add the unique index: ' .
                        $dupes->pluck('user_id')->implode(', ')
                    );
                }

                DB::statement('ALTER TABLE `affiliates` ADD UNIQUE KEY `affiliates_user_id_unique` (`user_id`)');
            }
        }

        if (Schema::hasTable('permissions')) {
            foreach (['affiliate.view', 'affiliate.edit', 'affiliate.delete'] as $name) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        if (app()->bound('cache')) {
            app('cache')->forget('spatie.permission.cache');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('affiliates')) {
            try {
                DB::statement('ALTER TABLE `affiliates` DROP INDEX `affiliates_user_id_unique`');
            } catch (\Throwable $e) {
                // Index already gone.
            }
        }
    }
};
