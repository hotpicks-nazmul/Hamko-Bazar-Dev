<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MembershipPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'discount_percentage',
        'minimum_spent',
        'benefits',
        'icon',
        'color',
        'sort_order',
        'is_active',
        'is_featured',
        'max_members',
        'members_count',
        'valid_from',
        'valid_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'discount_percentage' => 'decimal:2',
        'minimum_spent' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'max_members' => 'integer',
        'members_count' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });

        static::updating(function ($plan) {
            if ($plan->isDirty('name') && empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    /**
     * Get the benefits as an array.
     */
    public function getBenefitsArrayAttribute()
    {
        if (empty($this->benefits)) {
            return [];
        }
        
        // If it's already an array, return it
        if (is_array($this->benefits)) {
            return $this->benefits;
        }
        
        // Try to decode as JSON
        $decoded = json_decode($this->benefits, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        
        // Otherwise, split by newlines
        return array_filter(array_map('trim', explode("\n", $this->benefits)));
    }

    /**
     * Check if plan is currently valid.
     */
    public function isValid(): bool
    {
        $now = now();
        
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if plan has reached max members.
     */
    public function hasReachedMaxMembers(): bool
    {
        if ($this->max_members === null) {
            return false;
        }
        
        return $this->members_count >= $this->max_members;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        if ($this->price == 0) {
            return 'Free';
        }
        
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get the formatted duration.
     */
    public function getFormattedDurationAttribute()
    {
        $days = $this->duration_days;
        
        if ($days >= 365) {
            $years = floor($days / 365);
            return $years . ' Year' . ($years > 1 ? 's' : '');
        }
        
        if ($days >= 30) {
            $months = floor($days / 30);
            return $months . ' Month' . ($months > 1 ? 's' : '');
        }
        
        return $days . ' Days';
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured plans.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope a query to only include valid plans.
     */
    public function scopeValid($query)
    {
        $now = now();
        
        return $query->where(function ($q) use ($now) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', $now);
        });
    }
}
