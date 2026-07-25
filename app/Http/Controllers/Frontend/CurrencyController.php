<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CurrencyController extends Controller
{
    /**
     * Switch currency
     */
    public function switch(Request $request, $code)
    {
        $currency = Currency::where('code', strtoupper($code))->where('is_active', true)->first();
        
        if (!$currency) {
            return back()->with('error', 'Currency not found or inactive.');
        }
        
        // Store currency in session
        session(['currency_code' => $currency->code]);
        session(['currency_symbol' => $currency->symbol]);
        session(['exchange_rate' => $currency->exchange_rate]);
        
        // Also update config for current request
        config(['app.currency' => $currency->code]);
        config(['app.currency_symbol' => $currency->symbol]);
        
        return back()->with('success', 'Currency changed to ' . $currency->name);
    }
}
