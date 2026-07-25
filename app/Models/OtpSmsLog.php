<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpSmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'message',
        'status',
        'gateway',
        'gateway_response',
        'otp_code',
        'purpose',
    ];

    /**
     * Get successful logs
     */
    public static function successful()
    {
        return static::where('status', 'sent')->orWhere('status', 'delivered');
    }

    /**
     * Get failed logs
     */
    public static function failed()
    {
        return static::where('status', 'failed');
    }

    /**
     * Get today's logs
     */
    public static function today()
    {
        return static::whereDate('created_at', today());
    }

    /**
     * Get count for today
     */
    public static function countToday()
    {
        return static::today()->count();
    }

    /**
     * Get successful count for today
     */
    public static function successfulToday()
    {
        return static::today()->successful()->count();
    }

    /**
     * Log a successful SMS
     */
    public static function logSuccess($phone, $message, $otpCode, $purpose, $gateway, $response = null)
    {
        return static::create([
            'phone' => $phone,
            'message' => $message,
            'otp_code' => $otpCode,
            'purpose' => $purpose,
            'status' => 'sent',
            'gateway' => $gateway,
            'gateway_response' => $response,
        ]);
    }

    /**
     * Log a failed SMS
     */
    public static function logFailure($phone, $message, $purpose, $gateway, $response = null)
    {
        return static::create([
            'phone' => $phone,
            'message' => $message,
            'purpose' => $purpose,
            'status' => 'failed',
            'gateway' => $gateway,
            'gateway_response' => $response,
        ]);
    }

    /**
     * Clean up old logs
     */
    public static function cleanup($days = 30)
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }
}
