<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Area extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'city_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // The slug carries the city name (UNIQUE areas_slug_city_id_unique), so it is rebuilt on rename
        // and on a move to another city — previously it was written on create only.
        static::saving(function ($area) {
            if (! $area->exists || $area->isDirty('name') || $area->isDirty('city_id')) {
                $city = City::find($area->city_id);
                $area->slug = Str::slug(trim($area->name . ' ' . ($city->name ?? '')));
            }
        });
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_area');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
