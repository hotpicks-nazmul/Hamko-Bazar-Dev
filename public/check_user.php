<?php
try {
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $user = App\Models\User::where('email', 'sknazmul@gmail.com')->first();
    if ($user) {
        echo "Role: " . var_export($user->role, true) . "\n";
        echo "user_type: " . var_export($user->user_type, true) . "\n";
        echo "warehouse_id: " . var_export($user->warehouse_id, true) . "\n";
        echo "Spatie roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
    } else {
        echo "User not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
