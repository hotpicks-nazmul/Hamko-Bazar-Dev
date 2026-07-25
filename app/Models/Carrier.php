<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Carrier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'carrier_type',
        'service_type',
        'contact_person',
        'phone',
        'email',
        'address',
        'website',
        'api_key',
        'api_secret',
        'api_token',
        'account_number',
        'api_mode',
        'is_api_configured',
        'tracking_url_pattern',
        'tracking_prefix',
        'base_rate',
        'per_kg_rate',
        'fuel_surcharge_percent',
        'cod_charge',
        'free_shipping_threshold',
        'coverage_countries',
        'excluded_countries',
        'estimated_delivery_days',
        'is_active',
        'is_featured',
        'supports_tracking',
        'supports_cod',
        'supports_insurance',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_api_configured' => 'boolean',
        'supports_tracking' => 'boolean',
        'supports_cod' => 'boolean',
        'supports_insurance' => 'boolean',
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'fuel_surcharge_percent' => 'decimal:2',
        'cod_charge' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
    ];

    /**
     * Scope for active carriers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured carriers.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for carriers with API configured.
     */
    public function scopeApiConfigured($query)
    {
        return $query->where('is_api_configured', true);
    }

    /**
     * Scope for carriers that support tracking.
     */
    public function scopeSupportsTracking($query)
    {
        return $query->where('supports_tracking', true);
    }

    /**
     * Scope for carriers that support COD.
     */
    public function scopeSupportsCod($query)
    {
        return $query->where('supports_cod', true);
    }

    /**
     * Scope for ordering by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the carrier's logo URL.
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, 'http')) {
                return $this->logo;
            }
            return asset('storage/' . $this->logo);
        }
        return asset('images/default-carrier.png');
    }

    /**
     * Get carrier type label.
     */
    public function getCarrierTypeLabelAttribute()
    {
        return match($this->carrier_type) {
            'international' => 'International',
            'regional' => 'Regional',
            'local' => 'Local',
            'express' => 'Express',
            'freight' => 'Freight',
            'all' => 'All Types',
            default => 'Unknown',
        };
    }

    /**
     * Get service type label.
     */
    public function getServiceTypeLabelAttribute()
    {
        return match($this->service_type) {
            'express' => 'Express Delivery',
            'standard' => 'Standard Delivery',
            'economy' => 'Economy Delivery',
            'overnight' => 'Overnight Delivery',
            'international' => 'International Shipping',
            'freight' => 'Freight',
            'all' => 'All Services',
            default => 'Unknown',
        };
    }

    /**
     * Get coverage countries as array.
     */
    public function getCoverageCountriesArrayAttribute()
    {
        if (empty($this->coverage_countries)) {
            return [];
        }
        return explode(',', $this->coverage_countries);
    }

    /**
     * Get excluded countries as array.
     */
    public function getExcludedCountriesArrayAttribute()
    {
        if (empty($this->excluded_countries)) {
            return [];
        }
        return explode(',', $this->excluded_countries);
    }

    /**
     * Generate tracking URL from tracking number.
     */
    public function getTrackingUrl($trackingNumber)
    {
        if (empty($this->tracking_url_pattern) || empty($trackingNumber)) {
            return null;
        }
        
        $trackingNumber = $this->tracking_prefix ? $this->tracking_prefix . $trackingNumber : $trackingNumber;
        return str_replace('{tracking_number}', $trackingNumber, $this->tracking_url_pattern);
    }

    /**
     * Calculate shipping cost.
     */
    public function calculateShippingCost($weight = 1, $codAmount = 0)
    {
        $cost = $this->base_rate;
        
        // Weight-based cost
        if ($this->per_kg_rate > 0) {
            $cost += ($weight * $this->per_kg_rate);
        }
        
        // Fuel surcharge
        if ($this->fuel_surcharge_percent > 0) {
            $cost += ($cost * $this->fuel_surcharge_percent / 100);
        }
        
        // COD charge
        if ($this->supports_cod && $codAmount > 0) {
            $cost += $this->cod_charge;
        }
        
        return round($cost, 2);
    }

    /**
     * Check if carrier delivers to a country.
     */
    public function deliversTo($country)
    {
        // If no coverage countries specified, assume worldwide
        if (empty($this->coverage_countries)) {
            // Check excluded countries
            $excluded = $this->excludedCountriesArray;
            return !in_array($country, $excluded);
        }
        
        $coverage = $this->coverageCountriesArray;
        return in_array($country, $coverage);
    }

    /**
     * Auto-generate slug from name.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($carrier) {
            if (empty($carrier->slug)) {
                $carrier->slug = Str::slug($carrier->name);
                
                // Ensure unique slug
                $count = static::where('slug', 'like', $carrier->slug . '%')->count();
                if ($count > 0) {
                    $carrier->slug = $carrier->slug . '-' . ($count + 1);
                }
            }
        });

        static::updating(function ($carrier) {
            if ($carrier->isDirty('name') && empty($carrier->slug)) {
                $carrier->slug = Str::slug($carrier->name);
                
                // Ensure unique slug
                $count = static::where('slug', 'like', $carrier->slug . '%')
                    ->where('id', '!=', $carrier->id)
                    ->count();
                if ($count > 0) {
                    $carrier->slug = $carrier->slug . '-' . ($count + 1);
                }
            }
        });
    }
}
