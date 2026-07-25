<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the role enum to include 'staff'
        // Check if column exists and if 'staff' is already in enum
        $columnType = DB::select("SHOW COLUMNS FROM users WHERE Field = 'role'");
        if (!empty($columnType)) {
            $type = $columnType[0]->Type ?? '';
            // Check if 'staff' is already in the enum
            if (strpos($type, 'staff') === false) {
                // Get current enum values
                preg_match('/enum\((.*)\)/', $type, $matches);
                if ($matches) {
                    $currentValues = explode(',', str_replace("'", '', $matches[1]));
                    $currentValues[] = "'staff'";
                    $newType = 'enum(' . implode(',', $currentValues) . ')';
                    DB::statement("ALTER TABLE users MODIFY role {$newType} DEFAULT 'customer'");
                }
            }
        }

        // Add staff-specific fields if they don't exist
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'permissions')) {
                $table->text('permissions')->nullable()->after('designation');
            }
            if (!Schema::hasColumn('users', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null')->after('permissions');
            }
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('warehouse_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['designation', 'permissions', 'warehouse_id', 'is_super_admin']);
        });

        // Restore original enum
        DB::statement("ALTER TABLE users MODIFY role enum('admin','customer','vendor') DEFAULT 'customer'");
    }
};
