<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Currency;
use App\Models\Setting;

class CurrencyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load currency settings from database to config
        $this->loadCurrencySettings();
    }

    /**
     * Load currency settings and set them in the application config
     */
    protected function loadCurrencySettings(): void
    {
        try {
            // First try to get from settings table
            $currencyCode = Setting::get('currency', 'USD');
            $currencySymbol = Setting::get('currency_symbol', '$');

            // If currency table exists and has data, get from there
            if (\Schema::hasTable('currencies')) {
                $defaultCurrency = Currency::getDefault();
                if ($defaultCurrency) {
                    $currencyCode = $defaultCurrency->code;
                    $currencySymbol = $defaultCurrency->symbol;
                }
            }

            // Set config values
            config(['app.currency' => $currencyCode]);
            config(['app.currency_symbol' => $currencySymbol]);
        } catch (\Throwable $e) {
            // Fallback to defaults if something goes wrong
            config(['app.currency' => 'USD']);
            config(['app.currency_symbol' => '$']);
        }
    }
}
