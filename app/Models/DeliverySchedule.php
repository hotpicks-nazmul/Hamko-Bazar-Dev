<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'day_of_week', // 0-6 (Sunday-Saturday) or 1-7 (Monday-Sunday)
        'start_time',
        'end_time',
        'cutoff_time', // Order must be placed before this time
        'type', // 'same_day', 'next_day', 'express', 'scheduled'
        'is_active',
        'max_orders',
        'additional_fee',
        'min_order_amount',
        'delivery_zones', // JSON array of zone IDs
        'available_from',
        'available_to',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_orders' => 'integer',
        'additional_fee' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'delivery_zones' => 'array',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
    ];

    /**
     * Get the status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active 
            ? '<span class="badge bg-success">Active</span>' 
            : '<span class="badge bg-secondary">Inactive</span>';
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'same_day' => 'bg-primary',
            'next_day' => 'bg-info',
            'express' => 'bg-warning',
            'scheduled' => 'bg-secondary',
        ];
        
        return '<span class="badge ' . ($badges[$this->type] ?? 'bg-secondary') . '">' . 
            ucfirst(str_replace('_', ' ', $this->type)) . '</span>';
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return date('h:i A', strtotime($this->start_time)) . ' - ' . date('h:i A', strtotime($this->end_time));
    }

    /**
     * Get formatted day of week
     */
    public function getDayNameAttribute()
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Everyday',
        ];
        
        return $days[$this->day_of_week] ?? $this->day_of_week;
    }

    /**
     * Scope to get only active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if schedule is available for ordering
     */
    public function isAvailableForOrder()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        // Check date range
        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }
        
        if ($this->available_to && $now->gt($this->available_to)) {
            return false;
        }

        // Check day of week
        if ($this->day_of_week !== null && $this->day_of_week != 7) {
            if ($now->dayOfWeek != $this->day_of_week) {
                return false;
            }
        }

        // Check time window
        $currentTime = $now->format('H:i:s');
        if ($currentTime < $this->start_time || $currentTime > $this->end_time) {
            return false;
        }

        return true;
    }

    /**
     * Check if order can be placed before cutoff
     */
    public function isBeforeCutoff()
    {
        $now = now()->format('H:i:s');
        return $now <= $this->cutoff_time;
    }
}
