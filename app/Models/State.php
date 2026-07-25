<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class State extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'country_id',
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

        // Runs on create *and* update, and resolves the country name before the slug: the slug is built
        // from it, and the denormalised `country` column is what the lists filter and search on. Both
        // used to be written on create only, so a rename kept a slug that no longer matched the name and
        // moving the record to another country left the old country name behind.
        static::saving(function ($state) {
            if ($state->country_id && (empty($state->country) || $state->isDirty('country_id'))) {
                $state->country = Country::find($state->country_id)?->name ?? $state->country;
            }

            if (! $state->exists || $state->isDirty('name') || $state->isDirty('country_id')) {
                $state->slug = Str::slug(trim($state->name . ' ' . ($state->country ?? '')));
            }
        });
    }

    public function countryRelation()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(City::class);
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
