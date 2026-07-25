<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'event',
        'method',
        'secret',
        'is_active',
        'timeout',
        'retry_count',
        'headers',
        'last_triggered_at',
        'success_count',
        'failure_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'timeout' => 'integer',
        'retry_count' => 'integer',
        'headers' => 'array',
        'last_triggered_at' => 'datetime',
        'success_count' => 'integer',
        'failure_count' => 'integer',
    ];

    /**
     * Get available webhook events
     */
    public static function getEvents(): array
    {
        return [
            // Order events
            'order.created' => 'Order Created',
            'order.updated' => 'Order Updated',
            'order.completed' => 'Order Completed',
            'order.cancelled' => 'Order Cancelled',
            'order.refunded' => 'Order Refunded',
            
            // Payment events
            'payment.completed' => 'Payment Completed',
            'payment.pending' => 'Payment Pending',
            'payment.failed' => 'Payment Failed',
            'payment.refunded' => 'Payment Refunded',
            
            // Product events
            'product.created' => 'Product Created',
            'product.updated' => 'Product Updated',
            'product.deleted' => 'Product Deleted',
            'product.stock_updated' => 'Product Stock Updated',
            
            // Customer events
            'customer.created' => 'Customer Created',
            'customer.updated' => 'Customer Updated',
            
            // Cart events
            'cart.created' => 'Cart Created',
            'cart.updated' => 'Cart Updated',
            'cart.abandoned' => 'Cart Abandoned',
            
            // Subscription events
            'subscription.created' => 'Subscription Created',
            'subscription.renewed' => 'Subscription Renewed',
            'subscription.cancelled' => 'Subscription Cancelled',
            
            // Affiliate events
            'affiliate.commission' => 'Affiliate Commission',
            'affiliate.payout' => 'Affiliate Payout',
        ];
    }

    /**
     * Get available HTTP methods
     */
    public static function getMethods(): array
    {
        return ['POST', 'GET', 'PUT', 'PATCH', 'DELETE'];
    }

    /**
     * Test the webhook connection
     */
    public function test(): array
    {
        $startTime = microtime(true);
        
        try {
            $response = \Http::timeout($this->timeout)
                ->withHeaders($this->headers ?? [])
                ->withBody(json_encode([
                    'event' => 'test',
                    'timestamp' => now()->toIso8601String(),
                ]), 'application/json')
                ->{$this->method}($this->url);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            $success = $response->successful();
            
            if ($success) {
                $this->success_count++;
            } else {
                $this->failure_count++;
            }
            
            $this->last_triggered_at = now();
            $this->save();
            
            return [
                'success' => $success,
                'status_code' => $response->status(),
                'response_time' => round($responseTime),
                'message' => $success ? 'Webhook test successful' : 'Webhook test failed',
            ];
        } catch (\Exception $e) {
            $this->failure_count++;
            $this->save();
            
            return [
                'success' => false,
                'status_code' => null,
                'response_time' => null,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Scope to get only active webhooks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by event
     */
    public function scopeForEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Get the success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        $total = $this->success_count + $this->failure_count;
        
        if ($total === 0) {
            return 100;
        }
        
        return round(($this->success_count / $total) * 100, 2);
    }
}
