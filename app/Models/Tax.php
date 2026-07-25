<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'state',
        'zip_code',
        'rate',
        'type',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'rate' => 'float',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all active taxes
     */
    public static function active()
    {
        return self::where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get the default tax
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->first();
    }

    /**
     * Calculate tax amount for a given price
     */
    public function calculateTax($price): float
    {
        if ($this->type === 'percentage') {
            return ($price * $this->rate) / 100;
        }
        
        return $this->rate;
    }

    /**
     * Set this tax as default (only one can be default)
     */
    public function setAsDefault(): void
    {
        self::where('is_default', true)->update(['is_default' => false]);
        $this->is_default = true;
        $this->save();
    }

    /**
     * Get tax for a specific location
     */
    public static function getTaxForLocation($country = null, $state = null, $zipCode = null)
    {
        // Try to find exact match
        $tax = self::where('is_active', true)
            ->where(function ($query) use ($country, $state, $zipCode) {
                $query->where('country', $country)
                    ->orWhereNull('country');
            })
            ->where(function ($query) use ($state) {
                $query->where('state', $state)
                    ->orWhereNull('state');
            })
            ->where(function ($query) use ($zipCode) {
                $query->where('zip_code', $zipCode)
                    ->orWhereNull('zip_code');
            })
            ->orderBy('country', 'desc')
            ->orderBy('state', 'desc')
            ->orderBy('zip_code', 'desc')
            ->first();

        // Fall back to default tax if no location-specific tax found
        if (!$tax) {
            $tax = self::getDefault();
        }

        return $tax;
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one default tax
        static::creating(function ($tax) {
            if ($tax->is_default) {
                self::where('is_default', true)->update(['is_default' => false]);
            }
        });

        static::updating(function ($tax) {
            if ($tax->is_default) {
                self::where('id', '!=', $tax->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }
}
