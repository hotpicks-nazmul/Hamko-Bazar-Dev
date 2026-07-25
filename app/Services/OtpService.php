<?php

namespace App\Services;

use App\Models\OtpConfiguration;
use App\Models\OtpVerification;
use App\Models\OtpSmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Generate a random OTP
     */
    public function generateOtp($length = null)
    {
        $length = $length ?? (int) OtpConfiguration::get('otp_length', 6);
        $isAlphanumeric = OtpConfiguration::get('otp_alphanumeric', 0);
        
        if ($isAlphanumeric) {
            return Str::upper(Str::random($length));
        }
        
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Create OTP record and send SMS
     */
    public function sendOtp($phone, $purpose = 'verification', $customMessage = null)
    {
        // Validate phone
        $phone = $this->formatPhone($phone);
        
        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format'
            ];
        }
        
        // Check cooldown
        $cooldown = 60;
        $latestOtp = OtpVerification::where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($latestOtp && now()->diffInSeconds($latestOtp->created_at, true) < $cooldown) {
            $remainingTime = ceil($cooldown - now()->diffInSeconds($latestOtp->created_at, true));
            return [
                'success' => false,
                'message' => "Please wait {$remainingTime} seconds before requesting another OTP"
            ];
        }
        
        // Check daily limit
        $dailyLimit = (int) OtpConfiguration::get('max_otp_per_day', 10);
        $todayCount = OtpVerification::where('phone', $phone)
            ->whereDate('created_at', today())
            ->count();
            
        if ($todayCount >= $dailyLimit) {
            return [
                'success' => false,
                'message' => "Daily OTP limit reached. Please try again tomorrow."
            ];
        }
        
        // Generate OTP
        $otp = $this->generateOtp();
        $expiry = (int) OtpConfiguration::get('otp_expiry', 5);
        
        // Create OTP record
        $verification = OtpVerification::create([
            'phone' => $phone,
            'otp' => $otp,
            'purpose' => $purpose,
            'status' => 'pending',
            'attempts' => 0,
            'expires_at' => now()->addMinutes($expiry),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        // Prepare message
        $message = $customMessage ?? $this->getMessageTemplate($purpose, [
            'otp' => $otp,
            'expiry' => $expiry,
        ]);
        
        // Send SMS
        $smsResult = $this->sendSms($phone, $message, $otp, $purpose);
        
        // Log the SMS
        OtpSmsLog::create([
            'phone' => $phone,
            'message' => $message,
            'status' => $smsResult['success'] ? 'sent' : 'failed',
            'gateway' => OtpConfiguration::get('sms_gateway', 'custom'),
            'gateway_response' => json_encode($smsResult),
            'otp_code' => $otp,
            'purpose' => $purpose,
        ]);
        
        return $smsResult;
    }

    /**
     * Verify OTP
     */
    public function verifyOtp($phone, $otp, $purpose = 'verification')
    {
        $phone = $this->formatPhone($phone);
        
        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format'
            ];
        }
        
        $verification = OtpVerification::where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Also check legacy format (88 + last 10 digits) for existing OTPs
        if (!$verification && strlen($phone) >= 12) {
            $legacyPhone = '88' . substr($phone, -10);
            $verification = OtpVerification::where('phone', $legacyPhone)
                ->where('purpose', $purpose)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();
        }
            
        if (!$verification) {
            return [
                'success' => false,
                'message' => 'No OTP found. Please request a new OTP.'
            ];
        }
        
        // Check if expired
        if ($verification->isExpired()) {
            $verification->markAsExpired();
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new OTP.'
            ];
        }
        
        // Check max attempts
        $maxAttempts = (int) OtpConfiguration::get('otp_max_attempts', 3);
        if ($verification->attempts >= $maxAttempts) {
            $verification->markAsFailed();
            return [
                'success' => false,
                'message' => 'Maximum verification attempts exceeded. Please request a new OTP.'
            ];
        }
        
        // Verify OTP
        $isAlphanumeric = OtpConfiguration::get('otp_alphanumeric', 0);
        $caseSensitive = OtpConfiguration::get('otp_case_sensitive', 0);
        
        $storedOtp = $verification->otp;
        $providedOtp = $otp;
        
        if (!$caseSensitive && !$isAlphanumeric) {
            $storedOtp = strtolower($storedOtp);
            $providedOtp = strtolower($providedOtp);
        }
        
        if ($storedOtp !== $providedOtp) {
            $verification->incrementAttempts();
            $remainingAttempts = $maxAttempts - $verification->attempts;
            
            return [
                'success' => false,
                'message' => "Invalid OTP. {$remainingAttempts} attempts remaining."
            ];
        }
        
        // Success
        $verification->markAsVerified();
        
        return [
            'success' => true,
            'message' => 'OTP verified successfully'
        ];
    }

    /**
     * Resend OTP
     */
    public function resendOtp($phone, $purpose = 'verification')
    {
        return $this->sendOtp($phone, $purpose);
    }

    /**
     * Map purpose to template key
     */
    private function getTemplateKey($purpose)
    {
        $mapping = [
            'verification' => 'otp_verification_template',
            'login' => 'login_notification_template',
            'registration' => 'registration_template',
            'password_reset' => 'password_reset_template',
            'payment' => 'payment_template',
            'order' => 'order_confirmation_template',
        ];

        return $mapping[$purpose] ?? $purpose . '_template';
    }

    /**
     * Get message template
     */
    public function getMessageTemplate($purpose, $data = [])
    {
        $templateKey = $this->getTemplateKey($purpose);
        $template = OtpConfiguration::get($templateKey, '');
        
        if (empty($template)) {
            // Default templates
            $defaults = [
                'verification' => 'Your verification code is: {otp}. Valid for {expiry} minutes.',
                'login' => 'Your login OTP is: {otp}. Valid for {expiry} minutes.',
                'registration' => 'Welcome! Your verification code is: {otp}. Valid for {expiry} minutes.',
                'password_reset' => 'Your password reset OTP is: {otp}. Do not share this code.',
                'payment' => 'Your payment verification code is: {otp}. Amount: {amount}.',
                'order' => 'Your order confirmation OTP is: {otp}. Order ID: {order_id}.',
            ];
            $template = $defaults[$purpose] ?? 'Your verification code is: {otp}.';
        }
        
        // Replace placeholders
        $siteName = config('app.name', 'Hamko Ecommerce');
        $template = str_replace('{otp}', $data['otp'] ?? '', $template);
        $template = str_replace('{expiry}', $data['expiry'] ?? '', $template);
        $template = str_replace('{site_name}', $siteName, $template);
        $template = str_replace('{user_name}', $data['user_name'] ?? '', $template);
        $template = str_replace('{amount}', $data['amount'] ?? '', $template);
        $template = str_replace('{order_id}', $data['order_id'] ?? '', $template);
        $template = str_replace('{email}', $data['email'] ?? '', $template);
        
        return $template;
    }

    /**
     * Send SMS via configured gateway
     */
    public function sendSms($phone, $message, $otp = null, $purpose = null)
    {
        $gateway = OtpConfiguration::get('sms_gateway', 'custom');
        
        try {
            switch ($gateway) {
                case 'twilio':
                    return $this->sendViaTwilio($phone, $message);
                case 'msg91':
                    return $this->sendViaMsg91($phone, $message);
                case 'ssl':
                    return $this->sendViaSslWireless($phone, $message);
                case 'bikiran':
                    return $this->sendViaBikiran($phone, $message);
                case 'custom':
                default:
                    return $this->sendViaCustomApi($phone, $message);
            }
        } catch (\Exception $e) {
            Log::error('SMS Send Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via Custom API
     */
    private function sendViaCustomApi($phone, $message)
    {
        $apiUrl = OtpConfiguration::get('custom_api_url');
        $apiKey = OtpConfiguration::get('custom_api_key');
        $apiSecret = OtpConfiguration::get('custom_api_secret');
        $senderId = OtpConfiguration::get('custom_sender_id');
        $method = OtpConfiguration::get('custom_api_method', 'POST');
        
        if (empty($apiUrl)) {
            // Simulate success for demo
            Log::info('SMS (Demo Mode): ' . $phone . ' - ' . $message);
            return [
                'success' => true,
                'message' => 'SMS sent successfully (demo mode)'
            ];
        }
        
        // Prepare request
        $requestBody = OtpConfiguration::get('custom_request_body', '{}');
        $requestBody = str_replace('{{api_key}}', $apiKey, $requestBody);
        $requestBody = str_replace('{{api_secret}}', $apiSecret, $requestBody);
        $requestBody = str_replace('{{sender_id}}', $senderId, $requestBody);
        $requestBody = str_replace('{{phone}}', $phone, $requestBody);
        $requestBody = str_replace('{{message}}', $message, $requestBody);
        
        try {
            if ($method === 'POST') {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($apiUrl, json_decode($requestBody, true));
            } else {
                $params = json_decode($requestBody, true);
                $response = Http::get($apiUrl, $params);
            }
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'SMS gateway returned error'
            ];
        } catch (\Exception $e) {
            Log::error('Custom SMS API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio($phone, $message)
    {
        $sid = OtpConfiguration::get('twilio_sid');
        $token = OtpConfiguration::get('twilio_token');
        $from = OtpConfiguration::get('twilio_from');
        
        if (empty($sid) || empty($token)) {
            return $this->sendViaCustomApi($phone, $message);
        }
        
        // Twilio implementation would go here
        // For now, fall back to custom API
        return $this->sendViaCustomApi($phone, $message);
    }

    /**
     * Send SMS via MSG91
     */
    private function sendViaMsg91($phone, $message)
    {
        $authkey = OtpConfiguration::get('msg91_authkey');
        $senderId = OtpConfiguration::get('msg91_sender_id');
        $route = OtpConfiguration::get('msg91_route', 1);
        
        if (empty($authkey)) {
            return $this->sendViaCustomApi($phone, $message);
        }
        
        // MSG91 implementation would go here
        return $this->sendViaCustomApi($phone, $message);
    }

    /**
     * Send SMS via SSL Wireless
     */
    private function sendViaSslWireless($phone, $message)
    {
        $user = OtpConfiguration::get('ssl_sms_user');
        $pass = OtpConfiguration::get('ssl_sms_pass');
        $sid = OtpConfiguration::get('ssl_sid');
        
        if (empty($user) || empty($pass)) {
            return $this->sendViaCustomApi($phone, $message);
        }
        
        // SSL Wireless implementation would go here
        return $this->sendViaCustomApi($phone, $message);
    }

    /**
     * Strip country code prefix from phone number for API parameters.
     * The Bikiran API has a separate country-code field, so numbers should
     * be in local format (e.g., 1303391262) not international (e.g., 8801303391262).
     */
    private function stripCountryCode($phone, $countryCode)
    {
        // Remove + prefix if present
        $phone = ltrim($phone, '+');
        
        // Strip the country code prefix (e.g., '880' or '88')
        if (str_starts_with($phone, $countryCode)) {
            $phone = substr($phone, strlen($countryCode));
        } elseif (str_starts_with($phone, '88')) {
            $phone = substr($phone, 2);
        }
        
        // Ensure it starts with the leading 0 for local format
        if (!str_starts_with($phone, '0') && strlen($phone) == 10) {
            $phone = '0' . $phone;
        }
        
        return $phone;
    }

    /**
     * Send SMS via Bikiran (smsapi.app)
     *
     * Uses form-encoded POST with api-id, api-key, country-code, domain, numbers, text
     */
    private function sendViaBikiran($phone, $message)
    {
        $apiId = OtpConfiguration::get('bikiran_api_id');
        $apiKey = OtpConfiguration::get('bikiran_api_key');
        $sender = OtpConfiguration::get('bikiran_sender', '');
        $countryCode = OtpConfiguration::get('bikiran_country_code', '880');
        $domain = OtpConfiguration::get('bikiran_domain', '');

        if (empty($apiId) || empty($apiKey)) {
            Log::error('Bikiran SMS: API credentials not configured');
            return [
                'success' => false,
                'message' => 'SMS gateway credentials not configured'
            ];
        }

        $parameters = [
            'sender' => $sender,
            'api-id' => $apiId,
            'api-key' => $apiKey,
            'country-code' => $countryCode,
            'domain' => $domain,
            'numbers' => $this->stripCountryCode($phone, $countryCode),
            'text' => $message,
        ];

        $url = "https://www.smsapi.app/api/web/send-sms/post/";

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $out = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            if ($error) {
                Log::error('Bikiran SMS cURL Error: ' . $error);
                return [
                    'success' => false,
                    'message' => 'SMS gateway error: ' . $error
                ];
            }

            Log::info('Bikiran SMS Response: HTTP ' . $httpCode . ' - ' . $out);

            // Parse response to check for actual errors
            $responseData = json_decode($out, true);
            if (isset($responseData['error']) && $responseData['error'] != 0) {
                $errorMsg = $responseData['message'] ?? 'Unknown API error (code: ' . $responseData['error'] . ')';
                Log::error('Bikiran SMS API Error: ' . $errorMsg);
                return [
                    'success' => false,
                    'message' => 'SMS API error: ' . $errorMsg
                ];
            }

            // Also check if response contains error text patterns
            if (stripos($out, 'error') !== false && stripos($out, 'not allowed') !== false) {
                Log::error('Bikiran SMS Error Response: ' . $out);
                return [
                    'success' => false,
                    'message' => 'SMS delivery failed: ' . substr($out, 0, 200)
                ];
            }

            return [
                'success' => true,
                'message' => 'SMS sent successfully via Bikiran'
            ];
        } catch (\Exception $e) {
            Log::error('Bikiran SMS Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if OTP is required for a specific action
     */
    public function isOtpRequired($purpose)
    {
        $configKey = 'otp_for_' . $purpose;
        return (bool) OtpConfiguration::get($configKey, 0);
    }

    /**
     * Format phone number
     */
    public function formatPhone($phone)
    {
        // Remove common formatting characters
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Handle Bangladesh numbers
        $original = $phone;
        if (str_starts_with($phone, '+880')) {
            $phone = substr($phone, 4);
        } elseif (str_starts_with($phone, '+88')) {
            $phone = substr($phone, 3);
        } elseif (str_starts_with($phone, '88')) {
            $phone = substr($phone, 2);
        }
        // Now handle local format: 01XXXXXXXXX
        if (str_starts_with($phone, '01')) {
            $phone = '88' . $phone;
        }
        
        // Validate
        if (!preg_match('/^8801[3-9]\d{8}$/', $phone)) {
            // Try alternative validation
            if (preg_match('/^01[3-9]\d{8}$/', '0' . substr($phone, -10))) {
                $phone = '88' . substr($phone, -10);
            } else {
                return null;
            }
        }
        
        return $phone;
    }

    /**
     * Check SMS balance for configured gateway
     */
    public function checkBalance()
    {
        $gateway = OtpConfiguration::get('sms_gateway', 'custom');

        try {
            switch ($gateway) {
                case 'twilio':
                    return $this->checkTwilioBalance();
                case 'msg91':
                    return $this->checkMsg91Balance();
                case 'ssl':
                    return $this->checkSslWirelessBalance();
                case 'nexmo':
                    return $this->checkNexmoBalance();
                case 'banglalion':
                    return $this->checkBanglalionBalance();
                case 'mim':
                    return $this->checkMimBalance();
                case 'custom':
                default:
                    return $this->checkCustomApiBalance();
            }
        } catch (\Exception $e) {
            Log::error('Balance Check Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'balance' => 'Unable to check balance'
            ];
        }
    }

    /**
     * Check balance via Custom API (if available)
     */
    private function checkCustomApiBalance()
    {
        $apiUrl = OtpConfiguration::get('custom_api_url');
        $apiKey = OtpConfiguration::get('custom_api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            return [
                'success' => true,
                'balance' => 'Configure Custom API URL to check balance',
                'message' => 'No API URL configured'
            ];
        }

        return [
            'success' => true,
            'balance' => 'N/A',
            'message' => 'Custom API - Check provider dashboard for balance'
        ];
    }

    /**
     * Check Twilio balance
     */
    private function checkTwilioBalance()
    {
        $sid = OtpConfiguration::get('twilio_sid');
        $token = OtpConfiguration::get('twilio_token');

        if (empty($sid) || empty($token)) {
            return [
                'success' => false,
                'balance' => 'Not configured',
                'message' => 'Twilio credentials not configured'
            ];
        }

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->get("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Balance.json");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'balance' => $data['balance'] . ' ' . $data['currency'],
                    'message' => 'Balance retrieved successfully'
                ];
            }

            return [
                'success' => false,
                'balance' => 'Unable to retrieve',
                'message' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'balance' => 'Error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check MSG91 balance
     */
    private function checkMsg91Balance()
    {
        $authkey = OtpConfiguration::get('msg91_authkey');

        if (empty($authkey)) {
            return [
                'success' => false,
                'balance' => 'Not configured',
                'message' => 'MSG91 auth key not configured'
            ];
        }

        return [
            'success' => true,
            'balance' => 'N/A',
            'message' => 'Check MSG91 dashboard for balance'
        ];
    }

    /**
     * Check SSL Wireless balance
     */
    private function checkSslWirelessBalance()
    {
        $user = OtpConfiguration::get('ssl_sms_user');
        $pass = OtpConfiguration::get('ssl_sms_pass');

        if (empty($user) || empty($pass)) {
            return [
                'success' => false,
                'balance' => 'Not configured',
                'message' => 'SSL Wireless credentials not configured'
            ];
        }

        return [
            'success' => true,
            'balance' => 'N/A',
            'message' => 'Check SSL Wireless portal for balance'
        ];
    }

    /**
     * Check Nexmo (Vonage) balance
     */
    private function checkNexmoBalance()
    {
        $apiKey = OtpConfiguration::get('nexmo_api_key');
        $apiSecret = OtpConfiguration::get('nexmo_api_secret');

        if (empty($apiKey) || empty($apiSecret)) {
            return [
                'success' => false,
                'balance' => 'Not configured',
                'message' => 'Nexmo credentials not configured'
            ];
        }

        try {
            $response = Http::get("https://dashboard.nexmo.com/api/get-balance?api_key={$apiKey}&api_secret={$apiSecret}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'balance' => $data['balance'] . ' EUR',
                    'message' => 'Balance retrieved successfully'
                ];
            }

            return [
                'success' => false,
                'balance' => 'Unable to retrieve',
                'message' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'balance' => 'Error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check Banglalion balance
     */
    private function checkBanglalionBalance()
    {
        $apiKey = OtpConfiguration::get('banglalion_api_key');

        if (empty($apiKey)) {
            return [
                'success' => false,
                'balance' => 'Not configured',
                'message' => 'Banglalion API key not configured'
            ];
        }

        return [
            'success' => true,
            'balance' => 'N/A',
            'message' => 'Check Banglalion portal for balance'
        ];
    }

    /**
     * Check MIM SMS balance
     */
    private function checkMimBalance()
    {
        $apiKey = OtpConfiguration::get('mim_api_key');

        if (empty($apiKey)) {
            return [
                'success' => false,
                'balance' => 'Not configured',
                'message' => 'MIM SMS API key not configured'
            ];
        }

        return [
            'success' => true,
            'balance' => 'N/A',
            'message' => 'Check MIM SMS portal for balance'
        ];
    }

    /**
     * Get statistics
     */
    public function getStats()
    {
        return [
            'total_sent_today' => OtpSmsLog::countToday(),
            'successful_verifications' => OtpVerification::whereDate('verified_at', today())->count(),
            'failed_attempts' => OtpVerification::whereDate('created_at', today())
                ->whereIn('status', ['expired', 'failed'])->count(),
        ];
    }
}
