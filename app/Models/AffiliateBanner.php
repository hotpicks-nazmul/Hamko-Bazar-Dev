<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Affiliate Banner Model
 */
class AffiliateBanner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'affiliate_id',
        'image',
        'thumbnail',
        'target_url',
        'size',
        'width',
        'height',
        'clicks',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'clicks' => 'integer',
    ];

    /**
     * Get the affiliate that owns the banner.
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get active banners.
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
     * Get banner dimensions as string.
     */
    public function getDimensionsAttribute()
    {
        if ($this->width && $this->height) {
            return $this->width . 'x' . $this->height;
        }
        return $this->size;
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, '/storage/') || str_starts_with($this->image, 'http')) {
            return asset($this->image);
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) {
            return null;
        }

        if (str_starts_with($this->thumbnail, '/storage/') || str_starts_with($this->thumbnail, 'http')) {
            return asset($this->thumbnail);
        }

        return asset('storage/' . $this->thumbnail);
    }
}
