<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'balance',
        'initial_amount',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'user_id',
        'recipient_email',
        'recipient_name',
        'sender_name',
        'message',
        'status',
        'expiry_date',
        'usage_limit',
        'usage_count',
        'is_featured',
        'background_color',
        'terms_conditions',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'initial_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'expiry_date' => 'date',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($giftCard) {
            if (empty($giftCard->code)) {
                $giftCard->code = self::generateCode();
            }
        });
    }

    /**
     * Generate a unique gift card code.
     */
    public static function generateCode(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        
        do {
            $code = '';
            for ($i = 0; $i < 16; $i++) {
                if ($i > 0 && $i % 4 === 0) {
                    $code .= '-';
                }
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the user who purchased/owns the gift card.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the gift card is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->expiry_date === null || $this->expiry_date->isFuture()) &&
               ($this->usage_limit === null || $this->usage_count < $this->usage_limit) &&
               $this->balance > 0;
    }

    /**
     * Check if the gift card has expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if the gift card has been fully used.
     */
    public function isFullyUsed(): bool
    {
        return $this->balance <= 0 || 
               ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit);
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active' => $this->isExpired() ? 'danger' : 'success',
            'expired' => 'danger',
            'used' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get the remaining balance.
     */
    public function getRemainingBalance(): float
    {
        return max(0, (float) $this->balance);
    }

    /**
     * Calculate the discount amount.
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if ($orderAmount < $this->min_order_amount) {
            return 0;
        }

        $discount = 0;

        if ($this->discount_type === 'percent') {
            $discount = ($orderAmount * $this->discount_value) / 100;
        } else {
            $discount = $this->discount_value;
        }

        // Apply max discount cap if set
        if ($this->max_discount_amount !== null) {
            $discount = min($discount, $this->max_discount_amount);
        }

        // Can't exceed remaining balance
        return min($discount, $this->balance);
    }

    /**
     * Redeem the gift card.
     */
    public function redeem(float $amount): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $discount = $this->calculateDiscount($amount);
        
        if ($discount <= 0) {
            return false;
        }

        $this->balance = $this->balance - $discount;
        $this->usage_count = $this->usage_count + 1;
        
        if ($this->balance <= 0) {
            $this->status = 'used';
        } elseif ($this->isExpired()) {
            $this->status = 'expired';
        }

        return $this->save();
    }

    /**
     * Scope a query to only include active gift cards.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereRaw('usage_count < usage_limit');
            })
            ->where('balance', '>', 0);
    }

    /**
     * Scope a query to only include featured gift cards.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
