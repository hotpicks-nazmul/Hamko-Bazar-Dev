<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'native_name',
        'flag',
        'is_rtl',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_rtl' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all active languages
     */
    public static function active()
    {
        return self::where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get the default language
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->first() ?? self::where('code', 'en')->first();
    }

    /**
     * Check if language is RTL
     */
    public function isRtl(): bool
    {
        return $this->is_rtl;
    }

    /**
     * Set this language as default (only one can be default)
     */
    public function setAsDefault(): void
    {
        self::where('is_default', true)->update(['is_default' => false]);
        $this->is_default = true;
        $this->save();
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one default language
        static::creating(function ($language) {
            if ($language->is_default) {
                self::where('is_default', true)->update(['is_default' => false]);
            }
        });

        static::updating(function ($language) {
            if ($language->is_default) {
                self::where('id', '!=', $language->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }
}
