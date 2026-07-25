<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\JakatCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JakatController extends Controller
{
    protected $jakatCalculator;

    public function __construct(JakatCalculator $jakatCalculator)
    {
        $this->jakatCalculator = $jakatCalculator;
    }

    /**
     * Display the Jakat calculator form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get current market prices from settings
        $goldPrice = floatval(Setting::where('key', 'gold_price_per_gram')->value('value') ?? 6000);
        $silverPrice = floatval(Setting::where('key', 'silver_price_per_gram')->value('value') ?? 80);

        $nisabInfo = $this->jakatCalculator->getNisabInfo($goldPrice, $silverPrice);

        return view('admin.jakat.index', compact('goldPrice', 'silverPrice', 'nisabInfo'));
    }

    /**
     * Calculate Jakat based on user input
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function calculate(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            // Gold weights in grams
            'gold_24k' => 'nullable|numeric|min:0',
            'gold_22k' => 'nullable|numeric|min:0',
            'gold_21k' => 'nullable|numeric|min:0',
            'gold_18k' => 'nullable|numeric|min:0',
            // Silver weight in grams
            'silver' => 'nullable|numeric|min:0',
            // Financial assets
            'cash' => 'nullable|numeric|min:0',
            'bank_balance' => 'nullable|numeric|min:0',
            'business_assets' => 'nullable|numeric|min:0',
            'investments' => 'nullable|numeric|min:0',
            'stocks' => 'nullable|numeric|min:0',
            'crypto' => 'nullable|numeric|min:0',
            'other_assets' => 'nullable|numeric|min:0',
            // Market prices
            'gold_price' => 'nullable|numeric|min:0',
            'silver_price' => 'nullable|numeric|min:0',
            // Nisab type
            'nisab_type' => 'nullable|in:gold,silver',
        ]);

        // Get market prices from request or use settings
        $goldPrice = floatval($validated['gold_price'] ?? Setting::where('key', 'gold_price_per_gram')->value('value') ?? 6000);
        $silverPrice = floatval($validated['silver_price'] ?? Setting::where('key', 'silver_price_per_gram')->value('value') ?? 80);
        $nisabType = $validated['nisab_type'] ?? 'gold';

        // Prepare assets array
        $assets = [
            'gold_24k' => floatval($validated['gold_24k'] ?? 0),
            'gold_22k' => floatval($validated['gold_22k'] ?? 0),
            'gold_21k' => floatval($validated['gold_21k'] ?? 0),
            'gold_18k' => floatval($validated['gold_18k'] ?? 0),
            'silver' => floatval($validated['silver'] ?? 0),
            'cash' => floatval($validated['cash'] ?? 0),
            'bank_balance' => floatval($validated['bank_balance'] ?? 0),
            'business_assets' => floatval($validated['business_assets'] ?? 0),
            'investments' => floatval($validated['investments'] ?? 0),
            'stocks' => floatval($validated['stocks'] ?? 0),
            'crypto' => floatval($validated['crypto'] ?? 0),
            'other_assets' => floatval($validated['other_assets'] ?? 0),
        ];

        // Calculate Jakat
        $result = $this->jakatCalculator->calculate(
            $assets,
            $goldPrice,
            $silverPrice,
            $nisabType
        );

        // Get Nisab info
        $nisabInfo = $this->jakatCalculator->getNisabInfo($goldPrice, $silverPrice);

        // Return the view with results
        return view('admin.jakat.index', [
            'goldPrice' => $goldPrice,
            'silverPrice' => $silverPrice,
            'nisabInfo' => $nisabInfo,
            'result' => $result,
            'inputs' => $assets,
            'selectedNisabType' => $nisabType,
        ]);
    }

    /**
     * Update market prices in settings
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePrices(Request $request)
    {
        $validated = $request->validate([
            'gold_price' => 'required|numeric|min:0',
            'silver_price' => 'required|numeric|min:0',
        ]);

        try {
            // Save prices to settings
            Setting::updateOrCreate(['key' => 'gold_price_per_gram'], ['value' => $validated['gold_price']]);
            Setting::updateOrCreate(['key' => 'silver_price_per_gram'], ['value' => $validated['silver_price']]);

            return redirect()->route('admin.jakat.index')
                ->with('success', 'Market prices updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update Jakat market prices: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update market prices. Please try again.')
                ->withInput();
        }
    }
}
