<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Cache key for bKash grant token.
     */
    private const BKASH_TOKEN_CACHE_KEY = 'bkash_grant_token';

    /**
     * Initiate payment based on method.
     */
    public function initiatePayment(Order $order, string $method): ?string
    {
        $result = match ($method) {
            'bkash' => $this->createBkashPayment($order),
            'sslcommerz' => $this->createSslcommerzPayment($order),
            'nagad' => $this->createNagadPayment($order),
            'rocket' => $this->createRocketPayment($order),
            default => null,
        };

        if (!$result || isset($result['error'])) {
            Log::error('Payment initiation failed', [
                'method' => $method,
                'order_number' => $order->order_number,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
            return null;
        }

        // bKash returns paymentID-based URL, SSLCommerz returns GatewayPageURL
        return $result['url'] ?? $result['GatewayPageURL'] ?? $result['redirect_url'] ?? null;
    }

    /**
     * ─────────────────────────────────────────────
     *  bKash Checkout (URL Based) API Integration
     * ─────────────────────────────────────────────
     * Reference: official bKash developer samples
     *   - pgw-merchant-backend-php (GitHub)
     *   - bKash-for-woocommerce (WooCommerce plugin)
     */

    /**
     * Get a cached bKash grant token — reuses until expiry.
     *
     * The token endpoint returns an id_token with expires_in (seconds).
     * We cache it for (expires_in - 60) seconds to add a safety margin.
     */
    private function getBkashToken(PaymentGateway $gateway): ?string
    {
        $cacheKey = self::BKASH_TOKEN_CACHE_KEY . '_' . $gateway->id;

        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $config = $this->getBkashConfig($gateway);

        if (empty($config['app_key']) || empty($config['app_secret'])) {
            Log::error('bKash token: app_key or app_secret not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'username' => $config['username'],
                'password' => $config['password'],
            ])->post($this->getBkashBaseUrl($config['sandbox']) . '/checkout/token/grant', [
                'app_key' => $config['app_key'],
                'app_secret' => $config['app_secret'],
            ]);

            if (!$response->successful()) {
                Log::error('bKash token grant failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $token = $data['id_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 3600;

            if (!$token) {
                Log::error('bKash token grant denied', ['msg' => $data['msg'] ?? 'No id_token in response']);
                return null;
            }

            // Cache for (expires_in - 60) seconds to avoid edge-of-expiry failures
            Cache::put($cacheKey, $token, now()->addSeconds(max($expiresIn - 60, 60)));

            Log::info('bKash token granted, caching for ' . ($expiresIn - 60) . 's');

            return $token;
        } catch (\Exception $e) {
            Log::error('bKash token grant exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Clear the cached bKash token so the next call fetches a fresh one.
     */
    private function clearBkashToken(PaymentGateway $gateway): void
    {
        Cache::forget(self::BKASH_TOKEN_CACHE_KEY . '_' . $gateway->id);
    }

    /**
     * Perform an authenticated POST to the bKash Checkout API.
     *
     * Automatically retries once on 401 by refreshing the token.
     *
     * @param  PaymentGateway  $gateway  The bKash gateway model
     * @param  string          $path     API path after the base URL (e.g. 'payment/create')
     * @param  array|null      $body     Request body, or null for no body
     * @param  string          $method   'POST' or 'GET' (default: POST)
     * @return array|null               Decoded JSON response, or null on failure
     */
    private function bkashRequest(PaymentGateway $gateway, string $path, ?array $body = null, string $method = 'POST'): ?array
    {
        $token = $this->getBkashToken($gateway);
        if (!$token) {
            return null;
        }

        $config = $this->getBkashConfig($gateway);
        $baseUrl = $this->getBkashBaseUrl($config['sandbox']);
        $url = $baseUrl . '/' . $path;

        $headers = [
            'Authorization' => $token,
            'X-APP-Key' => $config['app_key'],
            'Accept' => 'application/json',
        ];

        try {
            if ($method === 'GET') {
                $response = Http::withHeaders($headers)->get($url);
            } else {
                $response = Http::withHeaders($headers)->post($url, $body ?? []);
            }

            // 401 — token likely expired; refresh and retry once
            if ($response->status() === 401) {
                Log::info('bKash 401 detected — refreshing token and retrying', ['path' => $path]);
                $this->clearBkashToken($gateway);
                $token = $this->getBkashToken($gateway);
                if (!$token) {
                    Log::error('bKash retry failed: could not refresh token', ['path' => $path]);
                    return null;
                }

                $headers['Authorization'] = $token;
                if ($method === 'GET') {
                    $response = Http::withHeaders($headers)->get($url);
                } else {
                    $response = Http::withHeaders($headers)->post($url, $body ?? []);
                }
            }

            if (!$response->successful()) {
                Log::error('bKash API request failed', [
                    'path' => $path,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $response->json() ?: null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('bKash API request exception', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create a bKash payment session.
     *
     * The Checkout (URL Based) API only needs amount, currency, intent,
     * and merchantInvoiceNumber. Fields like mode, payerReference, and
     * callbackURL belong to the Tokenized API — do NOT send them here.
     */
    public function createBkashPayment(Order $order): array
    {
        $gateway = PaymentGateway::findBySlug('bkash');
        if (!$gateway || !$gateway->is_active) {
            return ['error' => 'bKash gateway not configured or inactive'];
        }

        try {
            $response = $this->bkashRequest($gateway, 'checkout/payment/create', [
                'amount' => (string) $order->total,
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $order->order_number,
            ]);

            if (!$response) {
                return ['error' => 'Empty or failed response from bKash create payment'];
            }

            if (isset($response['transactionStatus']) && $response['transactionStatus'] === 'Initiated') {
                $paymentId = $response['paymentID'] ?? null;
                if ($paymentId) {
                    $config = $this->getBkashConfig($gateway);
                    // Include SDK URL for frontend popup flow
                    $response['sdk_url'] = $config['sandbox']
                        ? 'https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js'
                        : 'https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js';
                }
            } else {
                Log::error('bKash create payment not initiated', [
                    'response' => $response,
                    'order_number' => $order->order_number,
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('bKash create payment exception', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Execute a bKash payment after the user completes payment.
     *
     * The Checkout API execute endpoint is POST /checkout/payment/execute/{paymentID}
     * with NO request body. The paymentID goes in the URL path.
     */
    public function executeBkashPayment(string $paymentId): array
    {
        $gateway = PaymentGateway::findBySlug('bkash');
        if (!$gateway || !$gateway->is_active) {
            return ['error' => 'bKash gateway not configured'];
        }

        try {
            $response = $this->bkashRequest(
                $gateway,
                'checkout/payment/execute/' . $paymentId,
                null,   // NO body for Checkout API execute — paymentID is in URL
                'POST'
            );

            return $response ?: ['error' => 'Empty response from bKash execute'];
        } catch (\Exception $e) {
            Log::error('bKash execute exception', ['paymentID' => $paymentId, 'error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Query the status of a bKash payment.
     *
     * The Checkout API query endpoint is GET /checkout/payment/query/{paymentID}
     * with NO request body.
     */
    public function queryBkashPayment(string $paymentId): array
    {
        $gateway = PaymentGateway::findBySlug('bkash');
        if (!$gateway) {
            return ['error' => 'bKash gateway not configured'];
        }

        try {
            $response = $this->bkashRequest(
                $gateway,
                'checkout/payment/query/' . $paymentId,
                null,
                'GET'
            );

            return $response ?: ['error' => 'Empty response from bKash status'];
        } catch (\Exception $e) {
            Log::error('bKash status query exception', ['paymentID' => $paymentId, 'error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * ─────────────────────────────────────────────
     *  SSLCommerz Integration
     * ─────────────────────────────────────────────
     */

    /**
     * Create SSLCommerz payment session.
     */
    public function createSslcommerzPayment(Order $order): array
    {
        $gateway = PaymentGateway::findBySlug('sslcommerz');
        if (!$gateway || !$gateway->is_active) {
            return ['error' => 'SSLCommerz gateway not configured or inactive'];
        }

        $config = $this->getSslcommerzConfig($gateway);

        try {
            $response = Http::asForm()->post($this->getSslcommerzBaseUrl($config['sandbox']) . '/gwprocess/v4/api.php', [
                'store_id' => $config['store_id'],
                'store_passwd' => $config['store_password'],
                'total_amount' => $order->total,
                'currency' => 'BDT',
                'tran_id' => $order->order_number,
                'success_url' => route('payment.sslcommerz.success'),
                'fail_url' => route('payment.sslcommerz.fail'),
                'cancel_url' => route('payment.sslcommerz.cancel'),
                'ipn_url' => route('payment.sslcommerz.ipn'),
                'cus_name' => $order->shipping_name ?? $order->billing_name ?? 'Customer',
                'cus_email' => $order->shipping_email ?? $order->billing_email ?? '',
                'cus_phone' => $order->shipping_phone ?? $order->billing_phone ?? '',
                'cus_add1' => $order->shipping_address ?? $order->billing_address ?? '',
                'cus_city' => $order->shipping_city ?? $order->billing_city ?? '',
                'cus_state' => $order->shipping_state ?? $order->billing_state ?? '',
                'cus_postcode' => $order->shipping_postcode ?? '',
                'cus_country' => 'Bangladesh',
                'shipping_method' => 'NO',
                'product_name' => 'Order ' . $order->order_number,
                'product_category' => 'E-commerce',
                'product_profile' => 'general',
            ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === 'FAILED') {
                Log::error('SSLCommerz initiation failed', ['response' => $result]);
            }

            return $result ?: ['error' => 'Empty response from SSLCommerz'];
        } catch (\Exception $e) {
            Log::error('SSLCommerz payment exception', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Validate SSLCommerz IPN hash for security.
     */
    public function validateSslcommerzIpn(array $data): bool
    {
        $gateway = PaymentGateway::findBySlug('sslcommerz');
        if (!$gateway) {
            return false;
        }

        $config = $this->getSslcommerzConfig($gateway);

        if (!isset($data['verify_hash']) || !isset($data['verify_key'])) {
            return false;
        }

        // SSLCommerz sends verify_key like "amount,bank_tran_id,base_facility,val_id"
        $verifyKey = $data['verify_key'];
        $verifyHash = $data['verify_hash'];

        $keyList = explode(',', $verifyKey);
        $hashData = '';

        foreach ($keyList as $key) {
            $key = trim($key);
            if (isset($data[$key])) {
                $hashData .= $data[$key];
            }
        }

        $expectedHash = strtoupper(hash('md5', $hashData . $config['store_password']));

        return $expectedHash === $verifyHash;
    }

    /**
     * ─────────────────────────────────────────────
     *  Placeholder gateways
     * ─────────────────────────────────────────────
     */

    /**
     * Create Nagad payment (placeholder).
     */
    public function createNagadPayment(Order $order): array
    {
        return ['error' => 'Nagad integration coming soon'];
    }

    /**
     * Create Rocket payment (placeholder).
     */
    public function createRocketPayment(Order $order): array
    {
        return ['error' => 'Rocket integration coming soon'];
    }

    /**
     * ─────────────────────────────────────────────
     *  Configuration Helpers
     * ─────────────────────────────────────────────
     */

    /**
     * Get bKash configuration from PaymentGateway model.
     */
    private function getBkashConfig(PaymentGateway $gateway): array
    {
        return [
            'merchant_number' => $gateway->getCredential('merchant_number'),
            'username' => $gateway->getCredential('username') ?: $gateway->getCredential('api_key'),
            'password' => $gateway->getCredential('password') ?: $gateway->getCredential('api_secret'),
            'app_key' => $gateway->getCredential('app_key'),
            'app_secret' => $gateway->getCredential('app_secret'),
            'sandbox' => $gateway->test_mode,
        ];
    }

    /**
     * Get SSLCommerz configuration from PaymentGateway model.
     */
    private function getSslcommerzConfig(PaymentGateway $gateway): array
    {
        return [
            'store_id' => $gateway->getCredential('store_id'),
            'store_password' => $gateway->getCredential('store_password'),
            'sandbox' => $gateway->test_mode,
        ];
    }

    /**
     * Get bKash base URL based on mode.
     * Matches the official Checkout API URL pattern:
     *   Sandbox:    https://checkout.sandbox.bka.sh/v1.2.0-beta
     *   Production: https://checkout.pay.bka.sh/v1.2.0-beta
     */
    private function getBkashBaseUrl(bool $sandbox): string
    {
        return $sandbox
            ? 'https://checkout.sandbox.bka.sh/v1.2.0-beta'
            : 'https://checkout.pay.bka.sh/v1.2.0-beta';
    }

    /**
     * Get SSLCommerz base URL based on mode.
     */
    private function getSslcommerzBaseUrl(bool $sandbox): string
    {
        return $sandbox
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }
}
