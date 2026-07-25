<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LicenseKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'license_key',
        'status',
        'order_id',
        'user_id',
        'assigned_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the product for this license key.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order for this license key.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user for this license key.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if license key is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if license key is used.
     */
    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    /**
     * Check if license key is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if license key is valid.
     */
    public function isValid(): bool
    {
        return $this->isUsed() && !$this->isExpired();
    }

    /**
     * Assign license key to user/order.
     */
    public function assign(int $userId, int $orderId, ?int $expiryDays = null): void
    {
        $this->update([
            'status' => 'used',
            'user_id' => $userId,
            'order_id' => $orderId,
            'assigned_at' => now(),
            'expires_at' => $expiryDays ? now()->addDays($expiryDays) : null,
        ]);
    }

    /**
     * Disable license key.
     */
    public function disable(string $reason = null): void
    {
        $this->update([
            'status' => 'disabled',
            'notes' => $reason,
        ]);
    }

    /**
     * Generate a unique license key.
     */
    public static function generate(string $format = 'XXXX-XXXX-XXXX-XXXX'): string
    {
        do {
            $key = preg_replace_callback('/X/', function () {
                return strtoupper(Str::random(1));
            }, $format);
        } while (self::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * Generate multiple license keys.
     */
    public static function generateMultiple(int $count, int $productId, string $format = 'XXXX-XXXX-XXXX-XXXX'): array
    {
        $keys = [];
        for ($i = 0; $i < $count; $i++) {
            $keys[] = self::create([
                'product_id' => $productId,
                'license_key' => self::generate($format),
                'status' => 'available',
            ]);
        }
        return $keys;
    }

    /**
     * Scope for available keys.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for used keys.
     */
    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    /**
     * Scope for disabled keys.
     */
    public function scopeDisabled($query)
    {
        return $query->where('status', 'disabled');
    }

    /**
     * Scope for valid (used and not expired) keys.
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'used')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
