<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'order_type',
        'seller_id',
        'store_id',
        'pickup_point_id',
        'picked_up_at',
        'picked_up_by',
        'billing_first_name',
        'billing_last_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_city_id',
        'billing_state',
        'billing_postcode',
        'billing_country',
        'billing_area_id',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_city_id',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'shipping_area_id',
        'subtotal',
        'shipping_cost',
        'shipping_method',
        'tax',
        'discount',
        'total',
        'payment_method',
        'payment_gateway',
        'payment_status',
        'transaction_id',
        'status',
        'tracking_number',
        'shipping_company',
        'warehouse_id',
        'notes',
        'coupon_code',
        'packed_at',
        'packed_by',
        'picking_started_at',
    ];

    protected $dates = [
        'picked_up_at',
        'packed_at',
        'picking_started_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
            if (empty($order->tracking_number)) {
                $order->tracking_number = self::generateTrackingNumber();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function pickedBy()
    {
        return $this->belongsTo(User::class, 'picked_by');
    }

    public function packedBy()
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the pickup point for this order.
     */
    public function pickupPointLocation()
    {
        return $this->belongsTo(PickupPoint::class, 'pickup_point_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function billingCity()
    {
        return $this->belongsTo(City::class, 'billing_city_id');
    }

    public function shippingCity()
    {
        return $this->belongsTo(City::class, 'shipping_city_id');
    }

    public function billingArea()
    {
        return $this->belongsTo(Area::class, 'billing_area_id');
    }

    public function shippingArea()
    {
        return $this->belongsTo(Area::class, 'shipping_area_id');
    }

    /**
     * Check if this is a pickup point order.
     */
    public function getIsPickupOrderAttribute()
    {
        return $this->order_type === 'pickup_point';
    }

    /**
     * Get pickup status badge class.
     */
    public function getPickupStatusBadgeClassAttribute()
    {
        if ($this->picked_up_at) {
            return 'bg-success';
        }
        return $this->status_badge_class;
    }

    /**
     * Get pickup status text.
     */
    public function getPickupStatusTextAttribute()
    {
        if ($this->picked_up_at) {
            return 'Picked Up';
        }
        return ucfirst($this->status);
    }

    /**
     * Get shipping method display name.
     */
    public function getShippingMethodNameAttribute()
    {
        return match($this->shipping_method) {
            'home_delivery' => 'Home Delivery',
            'local_pickup' => 'Local Pickup',
            default => 'Standard Delivery',
        };
    }

    /**
     * Get the billing full name.
     */
    public function getBillingFullNameAttribute()
    {
        return trim($this->billing_first_name . ' ' . $this->billing_last_name);
    }

    /**
     * Get the shipping full name.
     */
    public function getShippingFullNameAttribute()
    {
        if ($this->shipping_first_name || $this->shipping_last_name) {
            return trim($this->shipping_first_name . ' ' . $this->shipping_last_name);
        }
        return $this->billing_full_name;
    }

    /**
     * Get the status badge class for Bootstrap.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'processing' => 'bg-info',
            'ready_to_pick' => 'bg-info',
            'picking' => 'bg-primary',
            'packed' => 'bg-success',
            'confirmed' => 'bg-primary',
            'shipped' => 'bg-primary',
            'delivered' => 'bg-success',
            'cancelled' => 'bg-danger',
            'refunded' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the status badge attribute (alias for compatibility).
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status_badge_class;
    }

    /**
     * Check if order is ready for picking.
     */
    public function getIsReadyToPickAttribute()
    {
        return in_array($this->status, ['processing', 'confirmed']) && $this->payment_status === 'paid';
    }

    /**
     * Check if order is currently being picked.
     */
    public function getIsPickingAttribute()
    {
        return $this->status === 'picking';
    }

    /**
     * Check if order is packed and ready to ship.
     */
    public function getIsPackedAttribute()
    {
        return $this->status === 'packed';
    }

    /**
     * Get status display name for picking workflow.
     */
    public function getStatusDisplayNameAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending Payment',
            'processing' => 'Processing',
            'ready_to_pick' => 'Ready to Pick',
            'picking' => 'Picking in Progress',
            'packed' => 'Packed',
            'confirmed' => 'Confirmed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the payment status badge class for Bootstrap.
     */
    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'pending' => 'bg-warning',
            'paid' => 'bg-success',
            'failed' => 'bg-danger',
            'refunded' => 'bg-info',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the payment status badge attribute (alias for compatibility).
     */
    public function getPaymentStatusBadgeAttribute()
    {
        return $this->payment_status_badge_class;
    }

    /**
     * Scope for inhouse orders
     */
    public function scopeInhouse($query)
    {
        return $query->where('order_type', 'inhouse');
    }

    /**
     * Scope for seller orders
     */
    public function scopeSeller($query)
    {
        return $query->where('order_type', 'seller');
    }

    /**
     * Scope for pickup point orders
     */
    public function scopePickupPoint($query)
    {
        return $query->where('order_type', 'pickup_point');
    }

    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = Setting::get('order_prefix', 'ORD');
        $suffix = Setting::get('order_suffix', '');
        $length = (int) Setting::get('order_number_length', 8);
        $format = Setting::get('order_number_format', 'random');
        
        if ($format === 'date') {
            $date = now()->format('Ymd');
            $lastOrder = self::withTrashed()
                ->whereDate('created_at', today())
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastOrder ? (int) substr($lastOrder->order_number, -4) + 1 : 1;
            $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
            
            return "{$prefix}-{$date}-{$sequence}{$suffix}";
        } elseif ($format === 'sequential') {
            $lastOrder = self::withTrashed()
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastOrder ? $lastOrder->id + 1 : 1;
            $sequence = str_pad($sequence, $length, '0', STR_PAD_LEFT);
            
            return "{$prefix}{$sequence}{$suffix}";
        } else {
            $random = '';
            $characters = '0123456789';
            $charactersLength = strlen($characters);
            
            for ($i = 0; $i < $length; $i++) {
                $random .= $characters[rand(0, $charactersLength - 1)];
            }
            
            return "{$prefix}{$random}{$suffix}";
        }
    }

    public static function generateTrackingNumber(): string
    {
        $prefix = 'TRK';
        $random = '';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        for ($i = 0; $i < 8; $i++) {
            $random .= $characters[rand(0, strlen($characters) - 1)];
        }

        $tracking = "{$prefix}-{$random}";

        if (self::where('tracking_number', $tracking)->exists()) {
            return self::generateTrackingNumber();
        }

        return $tracking;
    }
}
