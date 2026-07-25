<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'conditions',
        'customer_count',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'conditions' => 'array',
        'customer_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this segment
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get customers that belong to this segment
     */
    public function customers()
    {
        return $this->belongsToMany(User::class, 'customer_segment_members', 'segment_id', 'user_id')
            ->withPivot('added_at')
            ->withTimestamps();
    }

    /**
     * Apply segment conditions to get matching customers
     */
    public function applyConditions($query = null)
    {
        $baseQuery = $query ?? User::where('role', 'customer');

        if (empty($this->conditions)) {
            return $baseQuery;
        }

        $conditions = $this->conditions;

        // Handle order count conditions
        if (isset($conditions['order_count_min']) || isset($conditions['order_count_max'])) {
            $orderCountQuery = User::where('role', 'customer')
                ->select('users.id')
                ->withCount('orders');

            if (isset($conditions['order_count_min'])) {
                $orderCountQuery->having('orders_count', '>=', $conditions['order_count_min']);
            }
            if (isset($conditions['order_count_max'])) {
                $orderCountQuery->having('orders_count', '<=', $conditions['order_count_max']);
            }

            $matchingUserIds = $orderCountQuery->pluck('id')->toArray();
            $baseQuery->whereIn('id', $matchingUserIds);
        }

        // Handle total spent conditions
        if (isset($conditions['total_spent_min']) || isset($conditions['total_spent_max'])) {
            $spentQuery = User::where('role', 'customer')
                ->select('users.id')
                // orders has `total`, not `grand_total`; the old name threw SQLSTATE 42S22 on every
                // segment that used a spend range.
                ->withSum('orders as total_spent', 'total');

            if (isset($conditions['total_spent_min'])) {
                $spentQuery->having('total_spent', '>=', $conditions['total_spent_min']);
            }
            if (isset($conditions['total_spent_max'])) {
                $spentQuery->having('total_spent', '<=', $conditions['total_spent_max']);
            }

            $matchingUserIds = $spentQuery->pluck('id')->toArray();
            $baseQuery->whereIn('id', $matchingUserIds);
        }

        // Handle last order days condition
        if (isset($conditions['last_order_days'])) {
            $days = $conditions['last_order_days'];
            $baseQuery->whereHas('orders', function ($q) use ($days) {
                $q->where('created_at', '>=', now()->subDays($days));
            });
        }

        // Handle customer group condition
        if (isset($conditions['customer_group_id'])) {
            $baseQuery->where('customer_group_id', $conditions['customer_group_id']);
        }

        // Handle customer active status condition
        if (isset($conditions['is_active'])) {
            // users has `status` (active|inactive|banned) — there is no is_active column, and the old
            // name made every segment with a status filter throw SQLSTATE 42S22.
            $baseQuery->where('status', $conditions['is_active'] ? 'active' : 'inactive');
        }

        // Handle registration date conditions
        if (isset($conditions['registration_date_from'])) {
            $baseQuery->whereDate('created_at', '>=', $conditions['registration_date_from']);
        }

        if (isset($conditions['registration_date_to'])) {
            // whereDate, not where(): comparing a timestamp to '2026-10-25' excluded everything
            // registered during that day.
            $baseQuery->whereDate('created_at', '<=', $conditions['registration_date_to']);
        }

        return $baseQuery;
    }

    /**
     * Update the customer count for this segment
     */
    public function updateCustomerCount()
    {
        $this->customer_count = $this->applyConditions()->count();
        $this->save();
    }
}
