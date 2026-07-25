<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PriceRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_quantity',
        'min_order_amount',
        'start_date',
        'end_date',
        'is_featured',
        'priority',
        'status',
        'conditions',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_quantity' => 'integer',
        'min_order_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_featured' => 'boolean',
        'priority' => 'integer',
        'conditions' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($priceRule) {
            if (empty($priceRule->slug)) {
                $priceRule->slug = Str::slug($priceRule->name);
            }
            if (empty($priceRule->priority)) {
                $priceRule->priority = 0;
            }
        });

        static::updating(function ($priceRule) {
            if (empty($priceRule->slug)) {
                $priceRule->slug = Str::slug($priceRule->name);
            }
        });
    }

    /**
     * Get the products that belong to the price rule.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'price_rule_products')
            ->withPivot('discount', 'discount_type')
            ->withTimestamps();
    }

    /**
     * Get the categories that belong to the price rule.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'price_rule_categories')
            ->withTimestamps();
    }

    /**
     * Check if the price rule is currently active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the price rule has expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date && now()->gt($this->end_date);
    }

    /**
     * Check if the price rule is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_date && now()->lt($this->start_date);
    }

    /**
     * Get the status badge attribute.
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->isExpired()) {
            return 'danger';
        }
        
        if ($this->isUpcoming()) {
            return 'info';
        }

        return match($this->status) {
            'active' => 'success',
            'inactive' => 'secondary',
            default => 'warning',
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }
        
        if ($this->isUpcoming()) {
            return 'Upcoming';
        }

        return match($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            default => 'Draft',
        };
    }

    /**
     * Get the discount type label.
     */
    public function getDiscountTypeLabelAttribute(): string
    {
        return match($this->discount_type) {
            'percent' => 'Percentage (%)',
            'fixed' => 'Fixed Amount',
            default => $this->discount_type,
        };
    }

    /**
     * Calculate the discounted price for a given product price.
     */
    public function calculateDiscount(float $price, int $quantity = 1): float
    {
        if (!$this->isActive()) {
            return 0;
        }

        // Check minimum quantity
        if ($this->min_quantity && $quantity < $this->min_quantity) {
            return 0;
        }

        $discount = 0;
        
        if ($this->discount_type === 'percent') {
            $discount = ($price * $this->discount_value) / 100;
        } else {
            $discount = $this->discount_value;
        }

        // Apply max discount cap
        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return min($discount, $price);
    }

    /**
     * Scope to get active price rules.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope to get valid price rules (active and within date range).
     */
    public function scopeValid($query)
    {
        return $query->active()->orderBy('priority', 'desc');
    }

    /**
     * Scope to order by priority.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('created_at', 'desc');
    }
}
