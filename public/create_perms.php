<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Spatie\Permission\Models\Permission;

$newPerms = [
    'orders.inhouse-add-product',
    'orders.inhouse-edit-item',
    'orders.inhouse-remove-item',
    'orders.inhouse-change-warehouse',
];

$added = 0;
foreach ($newPerms as $name) {
    if (!Permission::where('name', $name)->where('guard_name', 'web')->exists()) {
        Permission::create(['name' => $name, 'guard_name' => 'web']);
        echo "Created: {$name}\n";
        $added++;
    } else {
        echo "Already exists: {$name}\n";
    }
}

echo "\nDone. {$added} permission(s) created.\n";
