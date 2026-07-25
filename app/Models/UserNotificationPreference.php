<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'key',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get the user that owns the notification preference.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create notification preference for a user.
     */
    public static function getPreference($userId, $type, $key)
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'type' => $type, 'key' => $key],
            ['enabled' => true]
        );
    }

    /**
     * Update notification preference for a user.
     */
    public static function updatePreference($userId, $type, $key, $enabled)
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'type' => $type, 'key' => $key],
            ['enabled' => $enabled]
        );
    }

    /**
     * Get all notification preferences for a user.
     */
    public static function getAllPreferences($userId, $type = null)
    {
        $query = static::where('user_id', $userId);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->get();
    }

    /**
     * Default notification keys for different types.
     */
    public static function getDefaultKeys()
    {
        return [
            'email' => [
                'order_placed' => 'Order Placed',
                'order_confirmed' => 'Order Confirmed',
                'order_processing' => 'Order Processing',
                'order_shipped' => 'Order Shipped',
                'order_delivered' => 'Order Delivered',
                'order_cancelled' => 'Order Cancelled',
                'refund_approved' => 'Refund Approved',
                'refund_rejected' => 'Refund Rejected',
                'new_message' => 'New Message',
                'promotional' => 'Promotional Emails',
            ],
            'sms' => [
                'order_status' => 'Order Status Updates',
                'delivery_update' => 'Delivery Updates',
                'otp' => 'OTP Verification',
            ],
            'push' => [
                'order_updates' => 'Order Updates',
                'promotional' => 'Promotional Notifications',
                'new_products' => 'New Products',
            ],
        ];
    }
}
