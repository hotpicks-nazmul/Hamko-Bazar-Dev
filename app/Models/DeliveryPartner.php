<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DeliveryPartner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'contact_person',
        'phone',
        'email',
        'address',
        'website',
        'service_type',
        'coverage_area',
        'base_rate',
        'cod_charge',
        'free_shipping_threshold',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'base_rate' => 'decimal:2',
        'cod_charge' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
    ];

    /**
     * Scope for active partners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured partners.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for ordering by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the partner's logo URL.
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return asset('images/default-partner.png');
    }

    /**
     * Get service type label.
     */
    public function getServiceTypeLabelAttribute()
    {
        return match($this->service_type) {
            'express' => 'Express Delivery',
            'standard' => 'Standard Delivery',
            'overnight' => 'Overnight Delivery',
            'international' => 'International Shipping',
            'all' => 'All Services',
            default => 'Unknown',
        };
    }

    /**
     * Auto-generate slug from name.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($partner) {
            if (empty($partner->slug)) {
                $partner->slug = Str::slug($partner->name);
                
                // Ensure unique slug
                $count = static::where('slug', 'like', $partner->slug . '%')->count();
                if ($count > 0) {
                    $partner->slug = $partner->slug . '-' . ($count + 1);
                }
            }
        });

        static::updating(function ($partner) {
            if ($partner->isDirty('name') && empty($partner->slug)) {
                $partner->slug = Str::slug($partner->name);
                
                // Ensure unique slug
                $count = static::where('slug', 'like', $partner->slug . '%')
                    ->where('id', '!=', $partner->id)
                    ->count();
                if ($count > 0) {
                    $partner->slug = $partner->slug . '-' . ($count + 1);
                }
            }
        });
    }
}
