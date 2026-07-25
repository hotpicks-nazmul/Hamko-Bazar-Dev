<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postcode',
        'country',
        'latitude',
        'longitude',
        'logo',
        'favicon',
        'banner',
        'opening_hours',
        'is_active',
        'is_default',
        'is_physical',
        'sort_order',
        'primary_color',
        'secondary_color',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_physical' => 'boolean',
        'sort_order' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
            }
            
            // If this is the first store, make it default
            if (!self::exists()) {
                $store->is_default = true;
            }
        });

        static::updating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
            }
        });
    }

    /**
     * Scope for active stores.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default store.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for physical stores.
     */
    public function scopePhysical($query)
    {
        return $query->where('is_physical', true);
    }

    /**
     * Scope ordered by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get formatted address attribute.
     */
    public function getFormattedAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postcode,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get full address as HTML.
     */
    public function getFullAddressHtmlAttribute()
    {
        $html = '';
        if ($this->address) {
            $html .= '<div>' . e($this->address) . '</div>';
        }
        if ($this->city || $this->state || $this->postcode) {
            $html .= '<div>';
            $html .= implode(', ', array_filter([
                $this->city,
                $this->state,
                $this->postcode
            ]));
            $html .= '</div>';
        }
        if ($this->country) {
            $html .= '<div>' . e($this->country) . '</div>';
        }
        
        return $html;
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->is_active ? 'bg-success' : 'bg-secondary';
    }

    /**
     * Get status text.
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get store type text.
     */
    public function getTypeTextAttribute()
    {
        return $this->is_physical ? 'Physical Store' : 'Online Store';
    }

    /**
     * Get logo URL.
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, '/storage/') || str_starts_with($this->logo, 'http')) {
                return $this->logo;
            }
            return '/storage/' . $this->logo;
        }
        return null;
    }

    /**
     * Get favicon URL.
     */
    public function getFaviconUrlAttribute()
    {
        if ($this->favicon) {
            if (str_starts_with($this->favicon, '/storage/') || str_starts_with($this->favicon, 'http')) {
                return $this->favicon;
            }
            return '/storage/' . $this->favicon;
        }
        return null;
    }

    /**
     * Get banner URL.
     */
    public function getBannerUrlAttribute()
    {
        if ($this->banner) {
            if (str_starts_with($this->banner, '/storage/') || str_starts_with($this->banner, 'http')) {
                return $this->banner;
            }
            return '/storage/' . $this->banner;
        }
        return null;
    }

    /**
     * Get products associated with this store.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get orders associated with this store.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if store has products.
     */
    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }

    /**
     * Get product count.
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Set a store as default.
     */
    public static function setAsDefault($id)
    {
        self::where('is_default', true)->update(['is_default' => false]);
        self::findOrFail($id)->update(['is_default' => true]);
    }

    /**
     * Get the default store.
     */
    public static function getDefault()
    {
        return self::default()->first() ?? self::active()->first();
    }

    /**
     * Get all active stores ordered.
     */
    public static function getActiveStores()
    {
        return self::active()->ordered()->get();
    }
}
