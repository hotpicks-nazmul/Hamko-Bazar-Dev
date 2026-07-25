<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'country_id',
        'state_id',
        'country',
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

        // See State: one hook for create and update, country resolved before the slug it feeds, so a
        // rename refreshes the slug and a country change refreshes the denormalised name.
        static::saving(function ($city) {
            if ($city->country_id && (empty($city->country) || $city->isDirty('country_id'))) {
                $city->country = Country::find($city->country_id)?->name ?? $city->country;
            }

            if (! $city->exists || $city->isDirty('name') || $city->isDirty('country_id')) {
                $city->slug = Str::slug(trim($city->name . ' ' . ($city->country ?? '')));
            }
        });
    }

    public function countryRelation()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function activeAreas()
    {
        return $this->areas()->where('is_active', true)->orderBy('sort_order');
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_city');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCountryId($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeByStateId($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
