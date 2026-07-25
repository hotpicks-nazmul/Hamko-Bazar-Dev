<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductBundle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'featured_image',
        'bundle_price',
        'discount_type',
        'discount_value',
        'starts_at',
        'expires_at',
        'max_purchases',
        'max_purchases_per_user',
        'is_active',
        'is_featured',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'bundle_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the products in this bundle.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_bundle_items')
            ->withPivot(['quantity', 'custom_price', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Get the bundle items.
     */
    public function items()
    {
        return $this->hasMany(ProductBundleItem::class)->orderBy('sort_order');
    }

    /**
     * Get the purchases for this bundle.
     */
    public function purchases()
    {
        return $this->hasMany(ProductBundlePurchase::class);
    }

    /**
     * Scope for active bundles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured bundles.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for valid bundles (active and within date range).
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Check if bundle is currently valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if bundle has started.
     */
    public function hasStarted(): bool
    {
        if (!$this->starts_at) {
            return true;
        }
        return now()->gte($this->starts_at);
    }

    /**
     * Check if bundle has expired.
     */
    public function hasExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return now()->gt($this->expires_at);
    }

    /**
     * Get the original total price of all products in the bundle.
     */
    public function getOriginalPriceAttribute(): float
    {
        return $this->items->sum(function ($item) {
            $price = $item->custom_price ?? $item->product->final_price;
            return $price * $item->quantity;
        });
    }

    /**
     * Get the final bundle price after discount.
     */
    public function getFinalPriceAttribute(): float
    {
        // If bundle_price is set, use it directly
        if ($this->bundle_price > 0) {
            return $this->bundle_price;
        }

        // Otherwise calculate from discount
        $originalPrice = $this->original_price;

        if ($this->discount_type === 'percentage') {
            return $originalPrice - ($originalPrice * $this->discount_value / 100);
        }

        return max(0, $originalPrice - $this->discount_value);
    }

    /**
     * Get the savings amount.
     */
    public function getSavingsAttribute(): float
    {
        return $this->original_price - $this->final_price;
    }

    /**
     * Get the discount percentage.
     */
    public function getDiscountPercentageAttribute(): int
    {
        if ($this->original_price <= 0) {
            return 0;
        }

        return round(($this->savings / $this->original_price) * 100);
    }

    /**
     * Get total purchases count.
     */
    public function getTotalPurchasesAttribute(): int
    {
        return $this->purchases()->count();
    }

    /**
     * Check if user can purchase this bundle.
     */
    public function canBePurchasedBy(?User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check max purchases limit
        if ($this->max_purchases && $this->total_purchases >= $this->max_purchases) {
            return false;
        }

        // Check per-user limit
        if ($user && $this->max_purchases_per_user) {
            $userPurchases = $this->purchases()->where('user_id', $user->id)->count();
            if ($userPurchases >= $this->max_purchases_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get remaining purchases.
     */
    public function getRemainingPurchasesAttribute(): ?int
    {
        if (!$this->max_purchases) {
            return null;
        }
        return max(0, $this->max_purchases - $this->total_purchases);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if ($this->hasExpired()) {
            return 'Expired';
        }

        if (!$this->hasStarted()) {
            return 'Scheduled';
        }

        if ($this->max_purchases && $this->total_purchases >= $this->max_purchases) {
            return 'Sold Out';
        }

        return 'Active';
    }

    /**
     * Get status color for badges.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status_label) {
            'Active' => 'success',
            'Inactive' => 'secondary',
            'Expired' => 'danger',
            'Scheduled' => 'warning',
            'Sold Out' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Auto-generate slug from name.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bundle) {
            if (empty($bundle->slug)) {
                $bundle->slug = Str::slug($bundle->name);
                
                // Ensure unique slug
                $count = static::where('slug', 'like', $bundle->slug . '%')
                    ->withTrashed()
                    ->count();
                
                if ($count > 0) {
                    $bundle->slug .= '-' . ($count + 1);
                }
            }
        });
    }
}
