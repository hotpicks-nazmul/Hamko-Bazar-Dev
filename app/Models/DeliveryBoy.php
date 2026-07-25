<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DeliveryBoy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'photo',
        'address',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'national_id',
        'date_of_birth',
        'emergency_contact_name',
        'emergency_contact_phone',
        'salary',
        'commission_rate',
        'rating',
        'total_deliveries',
        'successful_deliveries',
        'failed_deliveries',
        'status',
        'notes',
        'is_available',
        'shift_start',
        'shift_end',
        'zone_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_deliveries' => 'integer',
        'successful_deliveries' => 'integer',
        'failed_deliveries' => 'integer',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'shift_start' => 'datetime:H:i',
        'shift_end' => 'datetime:H:i',
    ];

    /**
     * Get the delivery zone associated with the delivery boy.
     */
    public function zone()
    {
        return $this->belongsTo(DeliveryZone::class, 'zone_id');
    }

    /**
     * Get the user who created this delivery boy.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the orders assigned to this delivery boy.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_boy_id');
    }

    /**
     * Scope for active delivery boys.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Scope for available delivery boys.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)->where('is_available', true)->where('status', 'active');
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for searching.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Get the delivery boy's photo URL.
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            // Check if it's already a full URL (from ImageHelper)
            if (str_starts_with($this->photo, 'http')) {
                return $this->photo;
            }
            // Otherwise, it's a filename in the delivery-boys directory
            return asset('storage/delivery-boys/' . $this->photo);
        }
        return asset('images/default-delivery-boy.png');
    }

    /**
     * Get vehicle type label.
     */
    public function getVehicleTypeLabelAttribute()
    {
        return match($this->vehicle_type) {
            'bicycle' => 'Bicycle',
            'bike' => 'Motorcycle',
            'car' => 'Car',
            'van' => 'Van',
            'truck' => 'Truck',
            default => 'Not Set',
        };
    }

    /**
     * Get status label with color.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            'on_leave' => '<span class="badge bg-warning">On Leave</span>',
            'suspended' => '<span class="badge bg-danger">Suspended</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    /**
     * Calculate success rate.
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_deliveries == 0) {
            return 0;
        }
        return round(($this->successful_deliveries / $this->total_deliveries) * 100, 1);
    }

    /**
     * Check if currently working (within shift hours).
     */
    public function isCurrentlyWorking()
    {
        if (!$this->is_available || $this->status !== 'active') {
            return false;
        }

        if (!$this->shift_start || !$this->shift_end) {
            return true; // Always available if no shift defined
        }

        $now = now()->format('H:i:s');
        $start = $this->shift_start;
        $end = $this->shift_end;

        // Handle overnight shifts
        if ($start > $end) {
            return $now >= $start || $now <= $end;
        }

        return $now >= $start && $now <= $end;
    }

    /**
     * Auto-generate slug from name.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deliveryBoy) {
            if (empty($deliveryBoy->commission_rate)) {
                $deliveryBoy->commission_rate = 0;
            }
        });
    }
}
