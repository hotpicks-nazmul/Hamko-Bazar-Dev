<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DeliveryZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'region',
        'country',
        'state',
        'city',
        'postal_code',
        'area_type',
        'cod_enabled',
        'cod_charge',
        'cod_charge_type',
        'free_shipping_enabled',
        'free_shipping_threshold',
        'shipping_cost',
        'shipping_cost_type',
        'min_order_amount',
        'max_order_weight',
        'estimated_days',
        'delivery_time_start',
        'delivery_time_end',
        'is_active',
        'is_default',
        'sort_order',
        'coordinates',
    ];

    protected $casts = [
        'cod_enabled' => 'boolean',
        'free_shipping_enabled' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'cod_charge' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_order_weight' => 'decimal:2',
        'coordinates' => 'array',
    ];

    /**
     * Scope for active zones.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default zone.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for ordering by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get area type label.
     */
    public function getAreaTypeLabelAttribute()
    {
        return match($this->area_type) {
            'nationwide' => 'Nationwide',
            'regional' => 'Regional',
            'city' => 'City',
            'district' => 'District',
            'thana' => 'Thana/Upazila',
            'zone' => 'Custom Zone',
            default => 'Unknown',
        };
    }

    /**
     * Get shipping cost display.
     */
    public function getShippingCostDisplayAttribute()
    {
        if ($this->shipping_cost_type === 'free') {
            return 'Free Shipping';
        }
        
        if ($this->shipping_cost_type === 'flat') {
            return config('app.currency_symbol', '৳') . ' ' . number_format($this->shipping_cost, 2);
        }
        
        return config('app.currency_symbol', '৳') . ' ' . number_format($this->shipping_cost, 2);
    }

    /**
     * Get COD charge display.
     */
    public function getCodChargeDisplayAttribute()
    {
        if (!$this->cod_enabled) {
            return 'N/A';
        }

        if ($this->cod_charge_type === 'percentage') {
            return $this->cod_charge . '%';
        }

        return config('app.currency_symbol', '৳') . ' ' . number_format($this->cod_charge, 2);
    }

    /**
     * Get estimated delivery time display.
     */
    public function getDeliveryTimeDisplayAttribute()
    {
        if ($this->estimated_days == 1) {
            return '1 Day';
        }
        
        if ($this->estimated_days > 1) {
            return $this->estimated_days . ' Days';
        }

        if ($this->delivery_time_start && $this->delivery_time_end) {
            return $this->delivery_time_start . ' - ' . $this->delivery_time_end;
        }

        return 'Standard Delivery';
    }

    /**
     * Get location summary.
     */
    public function getLocationSummaryAttribute()
    {
        $parts = [];
        
        if ($this->city) {
            $parts[] = $this->city;
        }
        
        if ($this->state) {
            $parts[] = $this->state;
        }
        
        if ($this->country) {
            $parts[] = $this->country;
        }
        
        return implode(', ', $parts) ?: 'All Areas';
    }

    /**
     * Auto-generate slug from name.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($zone) {
            if (empty($zone->slug)) {
                $zone->slug = Str::slug($zone->name);
                
                // Ensure unique slug
                $count = static::where('slug', 'like', $zone->slug . '%')->count();
                if ($count > 0) {
                    $zone->slug = $zone->slug . '-' . ($count + 1);
                }
            }

            // Set as default if it's the first zone
            if (static::count() === 0) {
                $zone->is_default = true;
            }
        });

        static::updating(function ($zone) {
            if ($zone->isDirty('name') && empty($zone->slug)) {
                $zone->slug = Str::slug($zone->name);
                
                // Ensure unique slug
                $count = static::where('slug', 'like', $zone->slug . '%')
                    ->where('id', '!=', $zone->id)
                    ->count();
                if ($count > 0) {
                    $zone->slug = $zone->slug . '-' . ($count + 1);
                }
            }
        });
    }
}
