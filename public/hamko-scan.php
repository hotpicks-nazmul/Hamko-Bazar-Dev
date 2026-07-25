<?php
$token = $_GET['token'] ?? '';
if ($token !== 'hamko2026deploy') { http_response_code(403); exit; }

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('tinker', ['--execute' => '
$cats = App\Models\Category::whereNull("parent_id")->get();
echo "Parents:\n";
foreach($cats as $c) echo "{$c->id}|{$c->name}|{$c->slug}\n";

$cw = App\Models\Category::where("slug","cookware")->first();
if($cw) echo "\nCOOKWARE_FOUND|{$cw->id}\n";

$all = App\Models\Category::where("name","like","%COOK%")->orWhere("slug","like","%cook%")->get();
if($all->count()) { echo "\nCOOK_MATCHES:\n"; foreach($all as $c) echo "{$c->id}|{$c->name}|{$c->slug}|parent:{$c->parent_id}\n"; }
']);
echo $kernel->output();
