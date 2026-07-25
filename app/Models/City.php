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

        static::creating(function ($city) {
            if (empty($city->slug)) {
                $city->slug = Str::slug($city->name . '-' . ($city->country ?? ''));
            }
            if (empty($city->country) && $city->country_id) {
                $country = Country::find($city->country_id);
                $city->country = $country?->name;
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
