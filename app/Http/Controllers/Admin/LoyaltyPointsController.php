<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPointsTransaction;
use App\Models\LoyaltyReward;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoyaltyPointsController extends Controller
{
    /**
     * Get setting value
     */
    private function getSetting($key, $default = null)
    {
        return Setting::get($key, $default);
    }

    /**
     * Set setting value
     */
    private function setSetting($key, $value)
    {
        return Setting::set($key, $value);
    }

    /**
     * Display loyalty points listing with search and filters.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');

        // Search by name, email, phone
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        // Filter by points range
        if ($request->min_points) {
            $query->where('loyalty_points', '>=', $request->min_points);
        }

        if ($request->max_points) {
            $query->where('loyalty_points', '<=', $request->max_points);
        }

        // Filter by spend range
        if ($request->min_spent) {
            $query->where('total_spent', '>=', $request->min_spent);
        }

        if ($request->max_spent) {
            $query->where('total_spent', '<=', $request->max_spent);
        }

        // Sorting
        $sort = $request->sort ?? 'loyalty_points';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        $customers = $query->paginate(25)->appends($request->query());

        // Calculate statistics
        $stats = [
            'total_customers' => User::where('role', 'customer')->count(),
            'total_points' => User::where('role', 'customer')->sum('loyalty_points'),
            'total_points_spent' => User::where('role', 'customer')->sum('loyalty_points_spent'),
            'total_spent' => User::where('role', 'customer')->sum('total_spent'),
            'active_customers' => User::where('role', 'customer')->where('loyalty_points', '>', 0)->count(),
        ];

        // Recent transactions
        $recentTransactions = LoyaltyPointsTransaction::with('user')
            ->latest()
            ->take(10)
            ->get();

        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.loyalty-points.partials.table-rows', compact('customers'))->render();

            return response()->json([
                'html' => $html,
                'pagination' => $customers->links()->toHtml(),
                'first_item' => $customers->firstItem(),
                'last_item' => $customers->lastItem(),
                'total' => $customers->total(),
            ]);
        }

        return view('admin.loyalty-points.index', compact('customers', 'stats', 'recentTransactions'));
    }

    /**
     * Display customer loyalty details.
     */
    public function show(Request $request, $customerId)
    {
        $customer = User::where('role', 'customer')
            ->withCount('orders')
            ->findOrFail($customerId);

        $transactions = LoyaltyPointsTransaction::where('user_id', $customerId)
            ->latest()
            ->paginate(20);

        $rewards = LoyaltyReward::active()->valid()->get();

        return view('admin.loyalty-points.show', compact('customer', 'transactions', 'rewards'));
    }

    /**
     * Add points to customer account.
     */
    public function addPoints(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'type' => 'required|in:bonus,earned,adjusted',
            'description' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($request->user_id);

        DB::transaction(function () use ($user, $request) {
            // Update user points
            $user->increment('loyalty_points', $request->points);

            // Create transaction record
            LoyaltyPointsTransaction::create([
                'user_id' => $user->id,
                'points' => $request->points,
                'points_balance' => $user->fresh()->loyalty_points,
                'type' => $request->type,
                'description' => $request->description,
            ]);
        });

        return back()->with('success', 'Points added successfully.');
    }

    /**
     * Deduct points from customer account.
     */
    public function deductPoints(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->loyalty_points < $request->points) {
            return back()->with('error', 'Insufficient points balance.');
        }

        DB::transaction(function () use ($user, $request) {
            // Update user points
            $user->decrement('loyalty_points', $request->points);
            $user->increment('loyalty_points_spent', $request->points);

            // Create transaction record
            LoyaltyPointsTransaction::create([
                'user_id' => $user->id,
                'points' => -$request->points,
                'points_balance' => $user->fresh()->loyalty_points,
                'type' => 'adjusted',
                'description' => $request->description ?? 'Points deducted by admin',
            ]);
        });

        return back()->with('success', 'Points deducted successfully.');
    }

    /**
     * Display loyalty settings page.
     */
    public function settings()
    {
        // Get current settings from database or use defaults
        $settings = [
            'points_per_currency' => $this->getSetting('loyalty_points_per_currency', 1),
            'currency_per_points' => $this->getSetting('loyalty_currency_per_points', 1),
            'minimum_spend' => $this->getSetting('loyalty_minimum_spend', 0),
            'points_expiry_days' => $this->getSetting('loyalty_points_expiry_days', 365),
            'new_customer_bonus' => $this->getSetting('loyalty_new_customer_bonus', 0),
            'is_enabled' => $this->getSetting('loyalty_enabled', '1') === '1',
        ];

        $rewards = LoyaltyReward::orderBy('points_required')->get();

        return view('admin.loyalty-points.settings', compact('settings', 'rewards'));
    }

    /**
     * Update loyalty settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'points_per_currency' => 'required|numeric|min:0',
            'currency_per_points' => 'required|numeric|min:0',
            'minimum_spend' => 'required|numeric|min:0',
            'points_expiry_days' => 'required|integer|min:0',
            'new_customer_bonus' => 'required|integer|min:0',
        ]);

        // Save settings
        $this->setSetting('loyalty_points_per_currency', $request->points_per_currency);
        $this->setSetting('loyalty_currency_per_points', $request->currency_per_points);
        $this->setSetting('loyalty_minimum_spend', $request->minimum_spend);
        $this->setSetting('loyalty_points_expiry_days', $request->points_expiry_days);
        $this->setSetting('loyalty_new_customer_bonus', $request->new_customer_bonus);
        $this->setSetting('loyalty_enabled', $request->has('is_enabled') ? '1' : '0');

        return back()->with('success', 'Loyalty settings updated successfully.');
    }

    /**
     * Create a new reward.
     */
    public function createReward(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_required' => 'required|integer|min:1',
            'discount_value' => 'nullable|numeric|min:0',
            'reward_type' => 'required|in:discount,voucher,product,coupon',
            'code' => 'nullable|string|unique:loyalty_rewards,code',
            'max_redemptions' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'nullable|boolean',
        ]);

        LoyaltyReward::create([
            'name' => $request->name,
            'description' => $request->description,
            'points_required' => $request->points_required,
            'discount_value' => $request->discount_value,
            'reward_type' => $request->reward_type,
            'code' => $request->code,
            'max_redemptions' => $request->max_redemptions,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Reward created successfully.');
    }

    /**
     * Update a reward.
     */
    public function updateReward(Request $request, $rewardId)
    {
        $reward = LoyaltyReward::findOrFail($rewardId);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_required' => 'required|integer|min:1',
            'discount_value' => 'nullable|numeric|min:0',
            'reward_type' => 'required|in:discount,voucher,product,coupon',
            'code' => 'nullable|string|unique:loyalty_rewards,code,' . $reward->id,
            'max_redemptions' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        $reward->update($request->all());

        return back()->with('success', 'Reward updated successfully.');
    }

    /**
     * Delete a reward.
     */
    public function deleteReward($rewardId)
    {
        $reward = LoyaltyReward::findOrFail($rewardId);
        $reward->delete();

        return back()->with('success', 'Reward deleted successfully.');
    }

    /**
     * Toggle reward status.
     */
    public function toggleReward($rewardId)
    {
        $reward = LoyaltyReward::findOrFail($rewardId);
        $reward->update(['is_active' => !$reward->is_active]);

        $status = $reward->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Reward {$status} successfully.");
    }

    /**
     * Export loyalty points data.
     */
    public function export(Request $request): StreamedResponse
    {
        $customers = User::where('role', 'customer')
            ->select('name', 'email', 'phone', 'loyalty_points', 'loyalty_points_spent', 'total_spent', 'created_at')
            ->orderBy('loyalty_points', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="loyalty-points-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Name', 'Email', 'Phone', 'Points Balance', 'Points Spent', 'Total Spent', 'Join Date']);
            
            // Data rows
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->name,
                    $customer->email,
                    $customer->phone ?? '',
                    $customer->loyalty_points,
                    $customer->loyalty_points_spent,
                    $customer->total_spent,
                    $customer->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * View transaction history for a customer.
     */
    public function transactions(Request $request, $customerId)
    {
        $customer = User::findOrFail($customerId);

        $transactions = LoyaltyPointsTransaction::where('user_id', $customerId)
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->latest()
            ->paginate(20);

        return view('admin.loyalty-points.transactions', compact('customer', 'transactions'));
    }
}
