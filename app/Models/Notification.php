<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'icon',
        'link',
        'notifiable_id',
        'notifiable_type',
        'is_read',
        'is_for_admin',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_for_admin' => 'boolean',
        'data' => 'array',
    ];

    // Notification types with icons
    public static $types = [
        'order' => [
            'icon' => 'bi bi-bag',
            'color' => 'primary',
        ],
        'review' => [
            'icon' => 'bi bi-star',
            'color' => 'warning',
        ],
        'stock' => [
            'icon' => 'bi bi-box',
            'color' => 'danger',
        ],
        'refund' => [
            'icon' => 'bi bi-arrow-return-left',
            'color' => 'info',
        ],
        'customer' => [
            'icon' => 'bi bi-person-plus',
            'color' => 'success',
        ],
        'support' => [
            'icon' => 'bi bi-headset',
            'color' => 'secondary',
        ],
        'system' => [
            'icon' => 'bi bi-gear',
            'color' => 'dark',
        ],
        'product' => [
            'icon' => 'bi bi-box-seam',
            'color' => 'primary',
        ],
    ];

    /**
     * Get the notifiable entity (user) for frontend notifications.
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Scope for admin notifications.
     */
    public function scopeForAdmin($query)
    {
        return $query->where('is_for_admin', true);
    }

    /**
     * Scope for user notifications.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('is_for_admin', false)
                    ->where('notifiable_id', $userId);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Get the icon based on type.
     */
    public function getIconAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        return self::$types[$this->type]['icon'] ?? 'bi bi-bell';
    }

    /**
     * Get the color based on type.
     */
    public function getColorAttribute()
    {
        return self::$types[$this->type]['color'] ?? 'primary';
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->update(['is_read' => false]);
    }

    /**
     * Create a notification.
     */
    public static function createNotification($data)
    {
        return self::create($data);
    }

    /**
     * Create notification for admin.
     */
    public static function notifyAdmin($type, $title, $message, $link = null, $data = null)
    {
        return self::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_for_admin' => true,
            'is_read' => false,
            'data' => $data,
        ]);
    }

    /**
     * Create notification for a specific user.
     */
    public static function notifyUser($user, $type, $title, $message, $link = null, $data = null)
    {
        $preferenceKey = self::getPreferenceKeyForType($type);
        if ($preferenceKey && !self::isNotificationEnabled($user, 'push', $preferenceKey)) {
            return null;
        }

        return self::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'is_for_admin' => false,
            'is_read' => false,
            'data' => $data,
        ]);
    }

    /**
     * Map notification type to preference key.
     */
    protected static function getPreferenceKeyForType($type)
    {
        $mapping = [
            'order' => 'order_updates',
            'review' => 'new_products',
            'stock' => 'order_updates',
            'refund' => 'order_updates',
            'customer' => 'promotional',
            'support' => 'new_products',
            'system' => 'promotional',
            'product' => 'new_products',
        ];

        return $mapping[$type] ?? null;
    }

    /**
     * Check if notification is enabled for user.
     */
    protected static function isNotificationEnabled($user, $type, $key)
    {
        $preference = UserNotificationPreference::where('user_id', $user->id)
            ->where('type', $type)
            ->where('key', $key)
            ->first();

        return $preference ? $preference->enabled : true;
    }

    /**
     * Get time ago string.
     */
    public function getTimeAgoAttribute()
    {
        $diff = now()->diffInMinutes($this->created_at);
        
        if ($diff < 1) {
            return 'Just now';
        } elseif ($diff < 60) {
            return $diff . ' min ago';
        } elseif ($diff < 1440) {
            return floor($diff / 60) . ' hr ago';
        } elseif ($diff < 10080) {
            return floor($diff / 1440) . ' day ago';
        } else {
            return $this->created_at->format('M d, Y');
        }
    }
}
