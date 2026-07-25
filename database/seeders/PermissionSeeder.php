<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $moduleActions = [
            'dashboard'  => ['view', 'view-revenue', 'view-sales'],
            'analytics'  => ['view', 'export', 'view-revenue', 'view-sales'],
            'products'   => ['view', 'create', 'edit', 'export', 'view-cost'],
            'inventory'  => ['view'],
            'orders'     => ['view', 'view-customer', 'view-pricing'],
            'delivery'   => ['view'],
            'refund'     => ['view', 'view-customer'],
            'customers'  => ['view', 'export', 'view-financial'],
            'sellers'    => ['view'],
            'affiliate'  => ['view'],
            'media'      => ['view'],
            'reports'    => ['view'],
            'marketing'  => ['view'],
            'support'    => ['view'],
            'otp'        => ['view'],
            'content'    => ['view'],
            'appearance' => ['view'],
            'settings'   => ['view'],
            'locations'  => ['view'],
            'warehouse'  => ['view', 'create'],
            'staffs'     => ['view'],
            'system'     => ['view'],
            'pos'        => ['view'],
            'addon'      => ['view'],
            'multistore' => ['view'],
        ];

        $standalone = ['view-revenue', 'view-sales'];

        $permissionNames = [];
        foreach ($moduleActions as $module => $actions) {
            foreach ($actions as $action) {
                $permissionNames[] = $module . '.' . $action;
            }
        }
        $permissionNames = array_merge($permissionNames, $standalone);

        $created = 0;
        foreach ($permissionNames as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
            $created++;
        }

        $this->command->info("Created {$created} granular permissions across " . count($moduleActions) . " modules + " . count($standalone) . " standalone.");
    }
}
