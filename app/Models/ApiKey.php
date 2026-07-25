<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'secret',
        'type',
        'description',
        'is_active',
        'rate_limit',
        'permissions',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_limit' => 'integer',
        'permissions' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a new API key
     */
    public static function generateKey(): string
    {
        return 'sk_' . Str::random(48);
    }

    /**
     * Generate a new API secret
     */
    public static function generateSecret(): string
    {
        return Str::random(64);
    }

    /**
     * Regenerate the API key
     */
    public function regenerate(): void
    {
        $this->key = self::generateKey();
        $this->save();
    }

    /**
     * Check if the API key is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the API key can be used
     */
    public function canBeUsed(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Record usage of the API key
     */
    public function recordUsage(): void
    {
        $this->last_used_at = now();
        $this->save();
    }

    /**
     * Get the logs for this API key
     */
    public function logs()
    {
        return $this->hasMany(ApiKeyLog::class);
    }

    /**
     * Scope to get only active keys
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get available API key types
     */
    public static function getTypes(): array
    {
        return [
            'general' => 'General API',
            'payment' => 'Payment Gateway',
            'shipping' => 'Shipping API',
            'sms' => 'SMS Gateway',
            'email' => 'Email Service',
            'warehouse' => 'Warehouse/Inventory',
            'dropship' => 'Dropshipping',
            'custom' => 'Custom Integration',
        ];
    }

    /**
     * Mask the API key for display
     */
    public function getMaskedKeyAttribute(): string
    {
        if (strlen($this->key) <= 12) {
            return str_repeat('*', strlen($this->key));
        }
        
        return substr($this->key, 0, 8) . '...' . substr($this->key, -4);
    }
}
