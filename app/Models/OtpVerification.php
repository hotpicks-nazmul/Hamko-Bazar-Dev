<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'otp',
        'purpose',
        'status',
        'attempts',
        'expires_at',
        'verified_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Check if OTP is expired
     */
    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }

    /**
     * Check if OTP is verified
     */
    public function isVerified()
    {
        return $this->status === 'verified';
    }

    /**
     * Mark as verified
     */
    public function markAsVerified()
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Mark as expired
     */
    public function markAsExpired()
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    /**
     * Increment attempts
     */
    public function incrementAttempts()
    {
        $this->increment('attempts');
    }

    /**
     * Get latest OTP for a phone and purpose
     */
    public static function getLatest($phone, $purpose = 'verification')
    {
        return static::where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired($days = 7)
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }
}
