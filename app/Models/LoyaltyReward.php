<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'points_required',
        'discount_value',
        'reward_type',
        'code',
        'max_redemptions',
        'redemption_count',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'points_required' => 'integer',
        'discount_value' => 'decimal:2',
        'max_redemptions' => 'integer',
        'redemption_count' => 'integer',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    /**
     * Check if the reward is currently valid
     */
    public function isValid(): bool
    {
        $now = now();
        
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        if ($this->max_redemptions && $this->redemption_count >= $this->max_redemptions) {
            return false;
        }

        return true;
    }

    /**
     * Check if users can redeem this reward
     */
    public function canRedeem(): bool
    {
        return $this->isValid();
    }

    /**
     * Increment redemption count
     */
    public function incrementRedemption(): void
    {
        $this->increment('redemption_count');
    }

    /**
     * Scope for active rewards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid rewards
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('max_redemptions')
                  ->orWhereRaw('redemption_count < max_redemptions');
            });
    }
}
