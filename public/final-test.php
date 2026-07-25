<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear all pending OTPs
DB::table('otp_verifications')->where('status', 'pending')->delete();

// Test
$service = $app->make(App\Services\OtpService::class);
$result = $service->sendOtp('8801712345678', 'test');
echo json_encode($result) . "\n";
echo "DONE\n";
