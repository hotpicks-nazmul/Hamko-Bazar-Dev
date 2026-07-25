<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class JakatCalculator
{
    /**
     * Standard Nisab thresholds (in grams)
     * Nisab is the minimum amount of wealth that must be owned for one year to be liable for Zakat
     */
    public const NISAB_GOLD_GRAMS = 87.48; // Approximately 7.5 tola/masha
    public const NISAB_SILVER_GRAMS = 612.36; // Approximately 52 tola/masha

    /**
     * Standard Zakat rate
     */
    public const ZAKAT_RATE = 0.025; // 2.5%

    /**
     * Gold purity percentages
     */
    public const GOLD_PURITY = [
        '24k' => 1.0,
        '22k' => 0.916,
        '21k' => 0.875,
        '18k' => 0.750,
    ];

    /**
     * Calculate total assets and Zakat
     *
     * @param array $assets Array containing various asset values
     * @param float|null $goldPricePerGram Current price of gold per gram
     * @param float|null $silverPricePerGram Current price of silver per gram
     * @param string $nisabType 'gold' or 'silver'
     * @return array
     */
    public function calculate(array $assets, ?float $goldPricePerGram = null, ?float $silverPricePerGram = null, string $nisabType = 'gold'): array
    {
        // Set default prices if not provided (can be updated via admin settings)
        $goldPricePerGram = $goldPricePerGram ?? $this->getDefaultGoldPrice();
        $silverPricePerGram = $silverPricePerGram ?? $this->getDefaultSilverPrice();

        // Calculate total value of each asset type
        $goldValue = $this->calculateGoldValue(
            $assets['gold_24k'] ?? 0,
            $assets['gold_22k'] ?? 0,
            $assets['gold_21k'] ?? 0,
            $assets['gold_18k'] ?? 0,
            $goldPricePerGram
        );

        $silverValue = $this->calculateSilverValue(
            $assets['silver'] ?? 0,
            $silverPricePerGram
        );

        $cashValue = floatval($assets['cash'] ?? 0);
        $bankBalanceValue = floatval($assets['bank_balance'] ?? 0);
        $businessAssetsValue = floatval($assets['business_assets'] ?? 0);
        $investmentsValue = floatval($assets['investments'] ?? 0);
        $stocksValue = floatval($assets['stocks'] ?? 0);
        $cryptoValue = floatval($assets['crypto'] ?? 0);
        $otherAssetsValue = floatval($assets['other_assets'] ?? 0);

        // Calculate total wealth
        $totalWealth = $goldValue + $silverValue + $cashValue + $bankBalanceValue + 
                       $businessAssetsValue + $investmentsValue + $stocksValue + 
                       $cryptoValue + $otherAssetsValue;

        // Calculate Nisab value
        $nisabValue = $nisabType === 'gold' 
            ? ($goldPricePerGram * self::NISAB_GOLD_GRAMS)
            : ($silverPricePerGram * self::NISAB_SILVER_GRAMS);

        // Check if wealth exceeds Nisab
        $isLiable = $totalWealth >= $nisabValue;

        // Calculate Zakat (only if above Nisab)
        $zakatAmount = $isLiable ? ($totalWealth * self::ZAKAT_RATE) : 0;

        return [
            'assets' => [
                'gold' => [
                    '24k' => floatval($assets['gold_24k'] ?? 0),
                    '22k' => floatval($assets['gold_22k'] ?? 0),
                    '21k' => floatval($assets['gold_21k'] ?? 0),
                    '18k' => floatval($assets['gold_18k'] ?? 0),
                    'value' => $goldValue,
                ],
                'silver' => [
                    'grams' => floatval($assets['silver'] ?? 0),
                    'value' => $silverValue,
                ],
                'cash' => $cashValue,
                'bank_balance' => $bankBalanceValue,
                'business_assets' => $businessAssetsValue,
                'investments' => $investmentsValue,
                'stocks' => $stocksValue,
                'crypto' => $cryptoValue,
                'other_assets' => $otherAssetsValue,
            ],
            'total_wealth' => $totalWealth,
            'nisab' => [
                'type' => $nisabType,
                'threshold_grams' => $nisabType === 'gold' ? self::NISAB_GOLD_GRAMS : self::NISAB_SILVER_GRAMS,
                'threshold_value' => $nisabValue,
                'current_gold_price' => $goldPricePerGram,
                'current_silver_price' => $silverPricePerGram,
            ],
            'is_liable' => $isLiable,
            'zakat_rate' => self::ZAKAT_RATE,
            'zakat_amount' => $zakatAmount,
            'remaining_for_nisab' => $isLiable ? 0 : ($nisabValue - $totalWealth),
        ];
    }

    /**
     * Calculate gold value based on weight and purity
     *
     * @param float $grams24k Weight in grams of 24k gold
     * @param float $grams22k Weight in grams of 22k gold
     * @param float $grams21k Weight in grams of 21k gold
     * @param float $grams18k Weight in grams of 18k gold
     * @param float $pricePerGram Price per gram of gold
     * @return float
     */
    private function calculateGoldValue(float $grams24k, float $grams22k, float $grams21k, float $grams18k, float $pricePerGram): float
    {
        $value24k = $grams24k * $pricePerGram * self::GOLD_PURITY['24k'];
        $value22k = $grams22k * $pricePerGram * self::GOLD_PURITY['22k'];
        $value21k = $grams21k * $pricePerGram * self::GOLD_PURITY['21k'];
        $value18k = $grams18k * $pricePerGram * self::GOLD_PURITY['18k'];

        return $value24k + $value22k + $value21k + $value18k;
    }

    /**
     * Calculate silver value
     *
     * @param float $grams Weight in grams of silver
     * @param float $pricePerGram Price per gram of silver
     * @return float
     */
    private function calculateSilverValue(float $grams, float $pricePerGram): float
    {
        return $grams * $pricePerGram;
    }

    /**
     * Get default gold price (from settings or use default)
     *
     * @return float
     */
    private function getDefaultGoldPrice(): float
    {
        // Try to get from settings, otherwise use default
        $setting = Setting::where('key', 'gold_price_per_gram')->value('value');
        return $setting ? floatval($setting) : 6000; // Default BDT per gram
    }

    /**
     * Get default silver price (from settings or use default)
     *
     * @return float
     */
    private function getDefaultSilverPrice(): float
    {
        // Try to get from settings, otherwise use default
        $setting = Setting::where('key', 'silver_price_per_gram')->value('value');
        return $setting ? floatval($setting) : 80; // Default BDT per gram
    }

    /**
     * Get Nisab information
     *
     * @param float $goldPricePerGram
     * @param float $silverPricePerGram
     * @return array
     */
    public function getNisabInfo(float $goldPricePerGram = null, float $silverPricePerGram = null): array
    {
        $goldPricePerGram = $goldPricePerGram ?? $this->getDefaultGoldPrice();
        $silverPricePerGram = $silverPricePerGram ?? $this->getDefaultSilverPrice();

        return [
            'gold' => [
                'grams' => self::NISAB_GOLD_GRAMS,
                'value' => self::NISAB_GOLD_GRAMS * $goldPricePerGram,
                'price_per_gram' => $goldPricePerGram,
            ],
            'silver' => [
                'grams' => self::NISAB_SILVER_GRAMS,
                'value' => self::NISAB_SILVER_GRAMS * $silverPricePerGram,
                'price_per_gram' => $silverPricePerGram,
            ],
        ];
    }

    /**
     * Check if wealth is above Nisab
     *
     * @param float $totalWealth
     * @param string $nisabType
     * @param float $goldPricePerGram
     * @param float $silverPricePerGram
     * @return bool
     */
    public function isAboveNisab(float $totalWealth, string $nisabType = 'gold', float $goldPricePerGram = null, float $silverPricePerGram = null): bool
    {
        $goldPricePerGram = $goldPricePerGram ?? $this->getDefaultGoldPrice();
        $silverPricePerGram = $silverPricePerGram ?? $this->getDefaultSilverPrice();

        $nisabValue = $nisabType === 'gold' 
            ? ($goldPricePerGram * self::NISAB_GOLD_GRAMS)
            : ($silverPricePerGram * self::NISAB_SILVER_GRAMS);

        return $totalWealth >= $nisabValue;
    }
}
