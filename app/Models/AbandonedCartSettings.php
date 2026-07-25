<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCartSettings extends Model
{
    protected $table = 'abandoned_cart_settings';

    protected $fillable = [
        'is_enabled',
        'abandonment_time',
        'send_recovery_email',
        'first_email_delay',
        'second_email_delay',
        'max_emails_per_cart',
        'email_subject',
        'email_template',
        'include_discount',
        'discount_percentage',
        'discount_code',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'send_recovery_email' => 'boolean',
        'include_discount' => 'boolean',
        'abandonment_time' => 'integer',
        'first_email_delay' => 'integer',
        'second_email_delay' => 'integer',
        'max_emails_per_cart' => 'integer',
        'discount_percentage' => 'decimal:2',
    ];

    /**
     * Get the singleton settings instance.
     */
    public static function getSettings()
    {
        return self::first() ?? new self();
    }

    /**
     * Check if the feature is enabled.
     */
    public static function isEnabled()
    {
        $settings = self::first();
        return $settings && $settings->is_enabled;
    }

    /**
     * Get formatted abandonment time.
     */
    public function getFormattedAbandonmentTimeAttribute()
    {
        $minutes = $this->abandonment_time;
        
        if ($minutes < 60) {
            return $minutes . ' minutes';
        } elseif ($minutes == 60) {
            return '1 hour';
        } elseif ($minutes < 1440) {
            return round($minutes / 60) . ' hours';
        } else {
            return round($minutes / 1440) . ' days';
        }
    }
}
