<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'color_code',
        'price',
        'image',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($value) {
            if (empty($value->slug)) {
                $value->slug = Str::slug($value->value);
            }
        });

        static::updating(function ($value) {
            if ($value->isDirty('value') && empty($value->slug)) {
                $value->slug = Str::slug($value->value);
            }
        });
    }

    /**
     * Get the attribute this value belongs to.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get products that have this attribute value.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values')
            ->withPivot('attribute_id')
            ->withTimestamps();
    }

    /**
     * Scope for active values.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered display.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('value');
    }

    /**
     * Get the display name with attribute name.
     */
    public function getFullNameAttribute()
    {
        return $this->attribute->name . ': ' . $this->value;
    }
}
