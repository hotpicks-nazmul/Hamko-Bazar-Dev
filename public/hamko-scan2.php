<?php
$token = $_GET['token'] ?? '';
if ($token !== 'hamko2026deploy') { http_response_code(403); exit; }

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('tinker', ['--execute' => '
echo "ALL CATEGORIES:\n";
foreach(App\Models\Category::orderBy("id")->get() as $c) {
    $pname = $c->parent ? $c->parent->name : "ROOT";
    echo "{$c->id}|{$c->name}|slug:{$c->slug}|parent:{$pname}|status:{$c->status}|products:" . $c->products()->count() . "\n";
}
echo "\n---\nTotal categories: " . App\Models\Category::count() . "\n";
echo "Active products: " . App\Models\Product::where("is_active",1)->count() . "\n";
']);
echo $kernel->output();
