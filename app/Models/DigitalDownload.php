<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'license_key',
        'download_count',
        'max_downloads',
        'expires_at',
        'last_download_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_download_at' => 'datetime',
    ];

    /**
     * Get the user who owns this download.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product for this download.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order for this download.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if download is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if download limit is reached.
     */
    public function isLimitReached(): bool
    {
        return $this->max_downloads && $this->download_count >= $this->max_downloads;
    }

    /**
     * Check if download is available.
     */
    public function isAvailable(): bool
    {
        return !$this->isExpired() && !$this->isLimitReached();
    }

    /**
     * Record a download.
     */
    public function recordDownload(string $ipAddress, string $userAgent): void
    {
        $this->increment('download_count');
        $this->update([
            'last_download_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Get remaining downloads.
     */
    public function getRemainingDownloadsAttribute(): ?int
    {
        if (!$this->max_downloads) {
            return null; // Unlimited
        }
        return max(0, $this->max_downloads - $this->download_count);
    }

    /**
     * Scope for active downloads.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        })->where(function ($q) {
            $q->whereNull('max_downloads')
                ->orWhereRaw('download_count < max_downloads');
        });
    }

    /**
     * Scope for expired downloads.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }
}
