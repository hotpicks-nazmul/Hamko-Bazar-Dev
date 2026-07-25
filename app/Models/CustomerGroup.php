<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerGroup extends Model
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
        'discount_percentage',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customerGroup) {
            if (empty($customerGroup->slug)) {
                $customerGroup->slug = Str::slug($customerGroup->name);
            }
        });

        static::updating(function ($customerGroup) {
            if ($customerGroup->isDirty('name') && empty($customerGroup->slug)) {
                $customerGroup->slug = Str::slug($customerGroup->name);
            }
        });
    }

    /**
     * Get the users belonging to this customer group.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'customer_group_id');
    }

    /**
     * Get the customers count.
     */
    public function getCustomersCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Scope a query to only include active groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Check if group has discount.
     */
    public function hasDiscount(): bool
    {
        return $this->discount_percentage > 0;
    }
}
