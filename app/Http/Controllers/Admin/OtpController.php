<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtpConfiguration;
use App\Rules\Phone;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * OTP Controller for managing OTP settings.
 */
class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Display OTP configuration page
     */
    public function configuration()
    {
        $config = array_merge(
            OtpConfiguration::defaults(),
            OtpConfiguration::getAllConfig()
        );
        
        $stats = $this->otpService->getStats();
        
        return view('admin.otp.configuration', compact('config', 'stats'));
    }

    /**
     * Update OTP configuration
     */
    public function updateConfiguration(Request $request)
    {
        $validated = $request->validate([
            'otp_length' => 'required|in:4,5,6',
            'otp_expiry' => 'required|integer|min:1|max:60',
            'otp_max_attempts' => 'required|integer|min:1|max:10',
            'resend_cooldown' => 'required|integer|min:30|max:300',
            'max_otp_per_day' => 'required|integer|min:1|max:100',
            'temp_block_duration' => 'required|integer|min:5|max:1440',
        ]);

        // Save configuration
        foreach ($validated as $key => $value) {
            OtpConfiguration::set($key, $value, 'configuration');
        }

        // Save checkbox fields (0 if not present)
        $checkboxFields = [
            'otp_for_login',
            'otp_for_registration',
            'otp_for_password_reset',
            'otp_for_payment',
            'otp_for_profile_change',
            'otp_for_order_confirmation',
            'otp_case_sensitive',
            'otp_alphanumeric',
        ];

        foreach ($checkboxFields as $field) {
            OtpConfiguration::set($field, $request->has($field) ? 1 : 0, 'configuration');
        }

        return redirect()->route('admin.otp.configuration')
            ->with('success', 'OTP configuration updated successfully.');
    }

    /**
     * Display SMS templates page
     */
    public function smsTemplates()
    {
        $templates = array_merge(
            OtpConfiguration::templateDefaults(),
            OtpConfiguration::getAllConfig()
        );

        $stats = $this->otpService->getStats();

        return view('admin.otp.sms-templates', compact('templates', 'stats'));
    }

    /**
     * Update SMS templates
     */
    public function updateSmsTemplates(Request $request)
    {
        $validated = $request->validate([
            'otp_verification_template' => 'required|string|max:160',
            'registration_template' => 'nullable|string|max:160',
            'password_reset_template' => 'nullable|string|max:160',
            'payment_template' => 'nullable|string|max:160',
            'order_confirmation_template' => 'nullable|string|max:160',
            'login_notification_template' => 'nullable|string|max:160',
            'order_placed_template' => 'nullable|string|max:160',
            'delivery_status_template' => 'nullable|string|max:160',
            'payment_status_template' => 'nullable|string|max:160',
            'delivery_assignment_template' => 'nullable|string|max:160',
        ]);

        // Save templates
        foreach ($validated as $key => $value) {
            if (!empty($value)) {
                OtpConfiguration::set($key, $value, 'templates');
            }
        }

        return redirect()->route('admin.otp.sms-templates')
            ->with('success', 'SMS templates updated successfully.');
    }

    /**
     * Display OTP credentials page
     */
    public function credentials()
    {
        $credentials = array_merge(
            OtpConfiguration::credentialsDefaults(),
            OtpConfiguration::getAllConfig()
        );

        $stats = $this->otpService->getStats();

        return view('admin.otp.credentials', compact('credentials', 'stats'));
    }

    /**
     * Update OTP credentials
     */
    public function updateCredentials(Request $request)
    {
        $validated = $request->validate([
            'sms_gateway' => 'required|string|in:custom,twilio,nexmo,msg91,banglalion,ssl,mim,bikiran',
        ]);

        // Save gateway selection
        OtpConfiguration::set('sms_gateway', $validated['sms_gateway'], 'credentials');

        // Save custom API credentials (only if filled, preserve existing if empty)
        if ($request->filled('custom_api_url')) {
            OtpConfiguration::set('custom_api_url', $request->custom_api_url, 'credentials');
        }
        if ($request->filled('custom_api_key')) {
            OtpConfiguration::set('custom_api_key', $request->custom_api_key, 'credentials');
        }
        if ($request->filled('custom_api_secret')) {
            OtpConfiguration::set('custom_api_secret', $request->custom_api_secret, 'credentials');
        }
        if ($request->filled('custom_sender_id')) {
            OtpConfiguration::set('custom_sender_id', $request->custom_sender_id, 'credentials');
        }
        if ($request->filled('custom_api_method')) {
            OtpConfiguration::set('custom_api_method', $request->custom_api_method, 'credentials');
        }
        if ($request->filled('custom_request_body')) {
            OtpConfiguration::set('custom_request_body', $request->custom_request_body, 'credentials');
        }

        // Save Twilio credentials
        if ($request->filled('twilio_sid')) {
            OtpConfiguration::set('twilio_sid', $request->twilio_sid, 'credentials');
        }
        if ($request->filled('twilio_token')) {
            OtpConfiguration::set('twilio_token', $request->twilio_token, 'credentials');
        }
        if ($request->filled('twilio_from')) {
            OtpConfiguration::set('twilio_from', $request->twilio_from, 'credentials');
        }

        // Save MSG91 credentials
        if ($request->filled('msg91_authkey')) {
            OtpConfiguration::set('msg91_authkey', $request->msg91_authkey, 'credentials');
        }
        if ($request->filled('msg91_sender_id')) {
            OtpConfiguration::set('msg91_sender_id', $request->msg91_sender_id, 'credentials');
        }
        if ($request->filled('msg91_route')) {
            OtpConfiguration::set('msg91_route', $request->msg91_route, 'credentials');
        }

        // Save SSL Wireless credentials
        if ($request->filled('ssl_sms_user')) {
            OtpConfiguration::set('ssl_sms_user', $request->ssl_sms_user, 'credentials');
        }
        if ($request->filled('ssl_sms_pass')) {
            OtpConfiguration::set('ssl_sms_pass', $request->ssl_sms_pass, 'credentials');
        }
        if ($request->filled('ssl_sid')) {
            OtpConfiguration::set('ssl_sid', $request->ssl_sid, 'credentials');
        }

        // Save Nexmo (Vonage) credentials
        if ($request->filled('nexmo_api_key')) {
            OtpConfiguration::set('nexmo_api_key', $request->nexmo_api_key, 'credentials');
        }
        if ($request->filled('nexmo_api_secret')) {
            OtpConfiguration::set('nexmo_api_secret', $request->nexmo_api_secret, 'credentials');
        }
        if ($request->filled('nexmo_from')) {
            OtpConfiguration::set('nexmo_from', $request->nexmo_from, 'credentials');
        }

        // Save Banglalion credentials
        if ($request->filled('banglalion_api_key')) {
            OtpConfiguration::set('banglalion_api_key', $request->banglalion_api_key, 'credentials');
        }
        if ($request->filled('banglalion_api_secret')) {
            OtpConfiguration::set('banglalion_api_secret', $request->banglalion_api_secret, 'credentials');
        }
        if ($request->filled('banglalion_sender_id')) {
            OtpConfiguration::set('banglalion_sender_id', $request->banglalion_sender_id, 'credentials');
        }

        // Save MIM SMS credentials
        if ($request->filled('mim_api_key')) {
            OtpConfiguration::set('mim_api_key', $request->mim_api_key, 'credentials');
        }
        if ($request->filled('mim_api_secret')) {
            OtpConfiguration::set('mim_api_secret', $request->mim_api_secret, 'credentials');
        }
        if ($request->filled('mim_sender_id')) {
            OtpConfiguration::set('mim_sender_id', $request->mim_sender_id, 'credentials');
        }

        // Save Bikiran SMS credentials
        if ($request->filled('bikiran_api_id')) {
            OtpConfiguration::set('bikiran_api_id', $request->bikiran_api_id, 'credentials');
        }
        if ($request->filled('bikiran_api_key')) {
            OtpConfiguration::set('bikiran_api_key', $request->bikiran_api_key, 'credentials');
        }
        if ($request->filled('bikiran_sender')) {
            OtpConfiguration::set('bikiran_sender', $request->bikiran_sender, 'credentials');
        }
        if ($request->filled('bikiran_country_code')) {
            OtpConfiguration::set('bikiran_country_code', $request->bikiran_country_code, 'credentials');
        }
        if ($request->filled('bikiran_domain')) {
            OtpConfiguration::set('bikiran_domain', $request->bikiran_domain, 'credentials');
        }

        return redirect()->route('admin.otp.credentials')
            ->with('success', 'OTP credentials updated successfully.');
    }

    /**
     * Send test SMS
     */
    public function sendTestSms(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', new Phone],
        ]);

        $result = $this->otpService->sendOtp($request->phone, 'test');

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Test SMS sent successfully!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Check SMS balance
     */
    public function checkBalance(Request $request)
    {
        $result = $this->otpService->checkBalance();

        return response()->json([
            'success' => $result['success'],
            'balance' => $result['balance'],
            'message' => $result['message'] ?? ''
        ]);
    }
}
