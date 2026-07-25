<?php

namespace App\Services;

use App\Models\Tax;
use App\Models\Setting;

class TaxHelper
{
    /**
     * Check if tax is enabled
     */
    public static function isEnabled(): bool
    {
        return Setting::get('tax_enabled', '1') === '1';
    }

    /**
     * Get tax type (global or location)
     */
    public static function getTaxType(): string
    {
        return Setting::get('tax_type', 'global');
    }

    /**
     * Check if per-product tax is enabled
     */
    public static function isPerProduct(): bool
    {
        return Setting::get('tax_per_product', '0') === '1';
    }

    /**
     * Get tax calculation address type (shipping or billing)
     */
    public static function getCalculationAddressType(): string
    {
        return Setting::get('tax_calulation_address', 'shipping');
    }

    /**
     * Calculate tax for a given price
     */
    public static function calculateTax($price, $country = null, $state = null, $zipCode = null): float
    {
        if (!self::isEnabled()) {
            return 0;
        }

        $taxType = self::getTaxType();

        if ($taxType === 'location') {
            $tax = Tax::getTaxForLocation($country, $state, $zipCode);
        } else {
            $tax = Tax::getDefault();
        }

        if (!$tax) {
            return 0;
        }

        return $tax->calculateTax($price);
    }

    /**
     * Get tax rate for a given location
     */
    public static function getTaxRate($country = null, $state = null, $zipCode = null): float
    {
        if (!self::isEnabled()) {
            return 0;
        }

        $taxType = self::getTaxType();

        if ($taxType === 'location') {
            $tax = Tax::getTaxForLocation($country, $state, $zipCode);
        } else {
            $tax = Tax::getDefault();
        }

        return $tax ? $tax->rate : 0;
    }

    /**
     * Get tax for a given location
     */
    public static function getTax($country = null, $state = null, $zipCode = null): ?Tax
    {
        if (!self::isEnabled()) {
            return null;
        }

        $taxType = self::getTaxType();

        if ($taxType === 'location') {
            return Tax::getTaxForLocation($country, $state, $zipCode);
        }

        return Tax::getDefault();
    }

    /**
     * Format tax amount with currency
     */
    public static function formatTaxAmount(float $amount): string
    {
        $currencySymbol = config('app.currency_symbol', '$');
        return $currencySymbol . ' ' . number_format($amount, 2);
    }

    /**
     * Format tax rate percentage
     */
    public static function formatTaxRate(float $rate): string
    {
        return number_format($rate, 2) . '%';
    }
}
