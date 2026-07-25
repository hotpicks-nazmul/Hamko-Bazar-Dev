<?php
$token = $_GET['token'] ?? '';
if ($token !== 'hamko2026deploy') { http_response_code(403); exit; }

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "=== Post-Import Cache Clear ===\n\n";

$kernel->call('route:clear');
echo $kernel->output();

$kernel->call('view:clear');
echo $kernel->output();

$kernel->call('config:clear');
echo $kernel->output();

$kernel->call('cache:clear');
echo $kernel->output();

$kernel->call('optimize');
echo $kernel->output();

// Create storage link if missing
$kernel->call('storage:link');
echo $kernel->output();

echo "=== Done! Visit the site now ===";
