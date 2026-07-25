<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Affiliate Link Model
 * 
 * TODO: Implement full functionality
 */
class AffiliateLink extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'affiliate_id',
        'product_id',
        'name',
        'affiliate_code',
        'description',
        'target_url',
        'clicks',
        'conversions',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Get the affiliate that owns the link.
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get the product that the link belongs to.
     */
    public function product()
    {
        return $this->belongsTo(AffiliateProduct::class, 'product_id');
    }

    /**
     * Get the clicks for the link.
     */
    public function clicks()
    {
        return $this->hasMany(AffiliateClick::class, 'link_id');
    }

    /**
     * Get active links.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Increment click count.
     */
    public function incrementClicks()
    {
        $this->increment('clicks');
    }

    /**
     * Increment conversion count.
     */
    public function incrementConversions()
    {
        $this->increment('conversions');
    }

    /**
     * Generate unique affiliate code.
     */
    public static function generateCode()
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (self::where('affiliate_code', $code)->exists());

        return $code;
    }

    /**
     * Get the full affiliate URL.
     */
    public function getFullUrlAttribute()
    {
        return url('/aff/' . $this->affiliate_code);
    }

    /**
     * Auto-generate affiliate code.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($link) {
            if (empty($link->affiliate_code)) {
                $link->affiliate_code = self::generateCode();
            }
        });
    }
}
