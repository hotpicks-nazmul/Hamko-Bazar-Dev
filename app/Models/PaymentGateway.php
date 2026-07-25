<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'credentials',
        'is_active',
        'test_mode',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active' => 'boolean',
        'test_mode' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Credential keys that are stored in plaintext (not encrypted).
     */
    protected array $plaintextKeys = ['additional_settings', 'instructions', 'public_key'];

    /**
     * Get active gateways.
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get default gateway.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first() 
            ?? static::where('is_active', true)->orderBy('sort_order')->first();
    }

    /**
     * Get gateway by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Get credential value (auto-decrypt if encrypted).
     */
    public function getCredential(string $key, $default = null)
    {
        $value = $this->credentials[$key] ?? $default;

        if ($value === null || $value === '') {
            return $default;
        }

        // Plaintext keys — return as-is
        if (in_array($key, $this->plaintextKeys)) {
            return $value;
        }

        // Try to decrypt — if it's already plaintext, return as-is
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Get all decrypted credentials as a flat array.
     */
    public function getDecryptedCredentials(): array
    {
        $result = [];
        foreach (($this->credentials ?? []) as $key => $value) {
            $result[$key] = $this->getCredential($key);
        }
        return $result;
    }

    /**
     * Scope for active gateways.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered gateways.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if gateway is in test mode.
     */
    public function isTestMode(): bool
    {
        return $this->test_mode;
    }

    /**
     * Get gateway configuration.
     */
    public function getConfig(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'test_mode' => $this->test_mode,
            'credentials' => $this->credentials,
        ];
    }
}
