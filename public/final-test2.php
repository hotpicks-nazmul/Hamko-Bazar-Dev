<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('otp_verifications')->where('status', 'pending')->delete();

// Try with the same format the app uses
$service = $app->make(App\Services\OtpService::class);
$result = $service->sendOtp('8801303391262', 'test');
echo json_encode($result) . "\n";
echo "DONE\n";
