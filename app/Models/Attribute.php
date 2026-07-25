<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'display_order',
        'is_active',
        'is_filterable',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_filterable' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });

        static::updating(function ($attribute) {
            if ($attribute->isDirty('name') && empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    /**
     * Get the values for this attribute.
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('display_order');
    }

    /**
     * Get active values for this attribute.
     */
    public function activeValues()
    {
        return $this->hasMany(AttributeValue::class)
            ->where('is_active', true)
            ->orderBy('display_order');
    }

    /**
     * Get products that have this attribute.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values')
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }

    /**
     * Get products that have this attribute in the JSON attributes column.
     */
    public function productsWithJson()
    {
        return Product::whereNotNull('attributes')
            ->where('attributes', '!=', '[]');
    }

    /**
     * Get the count of products using this attribute (from JSON column).
     */
    public function getProductsCountAttribute()
    {
        $attrId = (string) $this->id;
        return $this->productsWithJson()->get()->filter(function ($product) use ($attrId) {
            $attrsData = json_decode($product->getOriginal('attributes'), true);
            if (!$attrsData || !is_array($attrsData)) {
                return false;
            }
            return isset($attrsData[$attrId]);
        })->count();
    }

    /**
     * Get products using this attribute.
     */
    public function getProductsAttribute()
    {
        $attrId = (string) $this->id;
        return $this->productsWithJson()->get()->filter(function ($product) use ($attrId) {
            $attrsData = json_decode($product->getOriginal('attributes'), true);
            if (!$attrsData || !is_array($attrsData)) {
                return false;
            }
            return isset($attrsData[$attrId]);
        });
    }

    /**
     * Scope for active attributes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filterable attributes.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope for ordered display.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Get the count of values.
     */
    public function getValuesCountAttribute()
    {
        return $this->values()->count();
    }

    /**
     * Get the count of active values.
     */
    public function getActiveValuesCountAttribute()
    {
        return $this->activeValues()->count();
    }
}
