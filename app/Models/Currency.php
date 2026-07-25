<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'exchange_rate' => 'float',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all active currencies
     */
    public static function active()
    {
        return self::where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get the default currency
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->first() ?? self::where('code', 'USD')->first();
    }

    /**
     * Format price with currency symbol
     */
    public function formatPrice($price): string
    {
        return $this->symbol . ' ' . number_format($price, 2);
    }

    /**
     * Set this currency as default (only one can be default)
     */
    public function setAsDefault(): void
    {
        self::where('is_default', true)->update(['is_default' => false]);
        $this->is_default = true;
        $this->save();
    }

    /**
     * Convert price from base currency to selected currency
     */
    public static function convertPrice($price, $fromCurrencyCode = null, $toCurrencyCode = null): float
    {
        // If no currencies specified, get from session or use default
        if (!$fromCurrencyCode) {
            $fromCurrencyCode = session('currency_code', self::getDefault()?->code ?? 'USD');
        }
        if (!$toCurrencyCode) {
            $toCurrencyCode = self::getDefault()?->code ?? 'USD';
        }

        // If same currency, no conversion needed
        if ($fromCurrencyCode === $toCurrencyCode) {
            return (float) $price;
        }

        $fromCurrency = self::where('code', $fromCurrencyCode)->first();
        $toCurrency = self::where('code', $toCurrencyCode)->first();

        if (!$fromCurrency || !$toCurrency) {
            return (float) $price;
        }

        // Convert: price * (from_rate / to_rate)
        // Base currency has rate 1, others have their rate relative to base
        $convertedPrice = $price * ($fromCurrency->exchange_rate / $toCurrency->exchange_rate);

        return round($convertedPrice, 2);
    }

    /**
     * Format price with current currency symbol
     */
    public static function formatPriceWithCurrency($price): string
    {
        $currencySymbol = session('currency_symbol', self::getDefault()?->symbol ?? '$');
        
        return $currencySymbol . ' ' . number_format($price, 2);
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one default currency
        static::creating(function ($currency) {
            if ($currency->is_default) {
                self::where('is_default', true)->update(['is_default' => false]);
            }
        });

        static::updating(function ($currency) {
            if ($currency->is_default) {
                self::where('id', '!=', $currency->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }
}
