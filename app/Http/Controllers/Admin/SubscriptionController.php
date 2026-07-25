<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Get statistics for subscriptions
     */
    protected function getStats()
    {
        return [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'pending' => Subscription::where('status', 'pending')->count(),
            'paused' => Subscription::where('status', 'paused')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
            'due_for_billing' => Subscription::dueForBilling()->count(),
        ];
    }

    /**
     * Get filtered stats based on current query filters.
     */
    protected function getFilteredStats($query)
    {
        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'paused' => (clone $query)->where('status', 'paused')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'expired' => (clone $query)->where('status', 'expired')->count(),
            'due_for_billing' => (clone $query)->where('status', 'active')
                ->whereDate('next_billing_date', '<=', today())->count(),
        ];
    }

    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'product']);

        // Search by subscription number, customer name, email, phone
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subscription_number', 'like', "%{$search}%")
                  ->orWhere('shipping_first_name', 'like', "%{$search}%")
                  ->orWhere('shipping_last_name', 'like', "%{$search}%")
                  ->orWhere('shipping_email', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by billing frequency
        if ($request->billing_frequency) {
            $query->where('billing_frequency', $request->billing_frequency);
        }

        // Filter by payment status
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $subscriptions = $query->paginate($perPage);

        // Get stats - filtered for AJAX, all stats for page load
        if ($request->ajax()) {
            $stats = $this->getFilteredStats($query);
        } else {
            $stats = $this->getStats();
        }

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.subscriptions.partials.table-rows', compact('subscriptions'))->render(),
                'pagination' => $subscriptions->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.subscriptions.index', compact('subscriptions', 'stats'));
    }

    /**
     * Show the form for creating a new subscription.
     */
    public function create()
    {
        $products = Product::where('is_active', true)
                          ->select('id', 'name', 'price', 'sku', 'quantity', 'featured_image')
                          ->orderBy('name')
                          ->get();
        
        $customers = User::where('status', 'active')
                        ->orderBy('name')
                        ->get(['id', 'name', 'email', 'phone']);

        return view('admin.subscriptions.create', compact('products', 'customers'));
    }

    /**
     * Store a newly created subscription.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'plan_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'billing_frequency' => 'required|in:weekly,bi_weekly,monthly,quarterly,semi_annually,annually',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'total_billing_cycles' => 'nullable|integer|min:1',
            'shipping_first_name' => 'required|string|max:255',
            'shipping_last_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_postcode' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        // Calculate prices
        $unitPrice = $product->price;
        $quantity = $request->quantity;
        $totalPrice = $unitPrice * $quantity;

        // Calculate next billing date
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $nextBillingDate = match($request->billing_frequency) {
            'weekly' => $startDate->copy()->addWeek(),
            'bi_weekly' => $startDate->copy()->addWeeks(2),
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            'semi_annually' => $startDate->copy()->addMonths(6),
            'annually' => $startDate->copy()->addYear(),
            default => $startDate->copy()->addMonth(),
        };

        // Create subscription
        $subscription = Subscription::create([
            'subscription_number' => Subscription::generateSubscriptionNumber(),
            'user_id' => $request->customer_id,
            'product_id' => $request->product_id,
            'plan_name' => $request->plan_name,
            'description' => $request->description,
            'billing_frequency' => $request->billing_frequency,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'start_date' => $startDate,
            'next_billing_date' => $nextBillingDate,
            'end_date' => $request->end_date,
            'total_billing_cycles' => $request->total_billing_cycles,
            'status' => $request->status ?? 'pending',
            'shipping_first_name' => $request->shipping_first_name,
            'shipping_last_name' => $request->shipping_last_name,
            'shipping_email' => $request->shipping_email,
            'shipping_phone' => $request->shipping_phone,
            'shipping_address' => $request->shipping_address,
            'shipping_city' => $request->shipping_city,
            'shipping_state' => $request->shipping_state,
            'shipping_postcode' => $request->shipping_postcode,
            'shipping_country' => $request->shipping_country,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.subscriptions.index')
                        ->with('success', 'Subscription created successfully.');
    }

    /**
     * Display the specified subscription.
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'product', 'order', 'cancelledBy']);
        
        // Get subscription history (for now, just mock data structure)
        $billingHistory = [];
        
        return view('admin.subscriptions.show', compact('subscription', 'billingHistory'));
    }

    /**
     * Show the form for editing the specified subscription.
     */
    public function edit(Subscription $subscription)
    {
        $products = Product::where('is_active', true)
                          ->select('id', 'name', 'price', 'sku', 'quantity', 'featured_image')
                          ->orderBy('name')
                          ->get();
        
        $customers = User::where('status', 'active')
                        ->orderBy('name')
                        ->get(['id', 'name', 'email', 'phone']);

        return view('admin.subscriptions.edit', compact('subscription', 'products', 'customers'));
    }

    /**
     * Update the specified subscription.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'plan_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'billing_frequency' => 'required|in:weekly,bi_weekly,monthly,quarterly,semi_annually,annually',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'total_billing_cycles' => 'nullable|integer|min:1',
            'status' => 'required|in:active,paused,pending,cancelled,expired',
            'shipping_first_name' => 'required|string|max:255',
            'shipping_last_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_postcode' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Recalculate prices if product or quantity changed
        $product = Product::find($request->product_id);
        $unitPrice = $product->price;
        
        if ($request->product_id != $subscription->product_id || $request->quantity != $subscription->quantity) {
            $totalPrice = $unitPrice * $request->quantity;
        } else {
            $totalPrice = $subscription->total_price;
        }

        // Recalculate next billing date if frequency changed
        if ($request->billing_frequency != $subscription->billing_frequency || $request->start_date != $subscription->start_date->format('Y-m-d')) {
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $nextBillingDate = match($request->billing_frequency) {
                'weekly' => $startDate->copy()->addWeek(),
                'bi_weekly' => $startDate->copy()->addWeeks(2),
                'monthly' => $startDate->copy()->addMonth(),
                'quarterly' => $startDate->copy()->addMonths(3),
                'semi_annually' => $startDate->copy()->addMonths(6),
                'annually' => $startDate->copy()->addYear(),
                default => $startDate->copy()->addMonth(),
            };
        } else {
            $nextBillingDate = $subscription->next_billing_date;
        }

        $subscription->update([
            'product_id' => $request->product_id,
            'plan_name' => $request->plan_name,
            'description' => $request->description,
            'billing_frequency' => $request->billing_frequency,
            'quantity' => $request->quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'start_date' => $request->start_date,
            'next_billing_date' => $nextBillingDate,
            'end_date' => $request->end_date,
            'total_billing_cycles' => $request->total_billing_cycles,
            'status' => $request->status,
            'shipping_first_name' => $request->shipping_first_name,
            'shipping_last_name' => $request->shipping_last_name,
            'shipping_email' => $request->shipping_email,
            'shipping_phone' => $request->shipping_phone,
            'shipping_address' => $request->shipping_address,
            'shipping_city' => $request->shipping_city,
            'shipping_state' => $request->shipping_state,
            'shipping_postcode' => $request->shipping_postcode,
            'shipping_country' => $request->shipping_country,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.subscriptions.index')
                        ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Remove the specified subscription.
     */
    public function destroy(Subscription $subscription)
    {
        // Only allow deletion of pending or cancelled subscriptions
        if (!in_array($subscription->status, ['pending', 'cancelled', 'expired'])) {
            return redirect()->route('admin.subscriptions.index')
                            ->with('error', 'Only pending, cancelled, or expired subscriptions can be deleted.');
        }

        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
                        ->with('success', 'Subscription deleted successfully.');
    }

    /**
     * Activate a subscription.
     */
    public function activate(Subscription $subscription)
    {
        if (!in_array($subscription->status, ['pending', 'paused'])) {
            return redirect()->route('admin.subscriptions.show', $subscription)
                            ->with('error', 'Only pending or paused subscriptions can be activated.');
        }

        $subscription->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
                        ->with('success', 'Subscription activated successfully.');
    }

    /**
     * Pause a subscription.
     */
    public function pause(Request $request, Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            return redirect()->route('admin.subscriptions.show', $subscription)
                            ->with('error', 'Only active subscriptions can be paused.');
        }

        $subscription->update([
            'status' => 'paused',
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
                        ->with('success', 'Subscription paused successfully.');
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        if (!in_array($subscription->status, ['active', 'paused', 'pending'])) {
            return redirect()->route('admin.subscriptions.show', $subscription)
                            ->with('error', 'This subscription cannot be cancelled.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_by' => Auth::id(),
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
                        ->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Process billing for a subscription.
     */
    public function processBilling(Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            return redirect()->route('admin.subscriptions.show', $subscription)
                            ->with('error', 'Only active subscriptions can be billed.');
        }

        DB::beginTransaction();
        try {
            // Create order for this billing cycle
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'user_id' => $subscription->user_id,
                'order_type' => 'subscription',
                'status' => 'confirmed',
                'payment_status' => 'pending',
                'billing_first_name' => $subscription->shipping_first_name,
                'billing_last_name' => $subscription->shipping_last_name,
                'billing_email' => $subscription->shipping_email,
                'billing_phone' => $subscription->shipping_phone,
                'billing_address' => $subscription->shipping_address,
                'billing_city' => $subscription->shipping_city,
                'billing_state' => $subscription->shipping_state,
                'billing_postcode' => $subscription->shipping_postcode,
                'billing_country' => $subscription->shipping_country,
                'shipping_first_name' => $subscription->shipping_first_name,
                'shipping_last_name' => $subscription->shipping_last_name,
                'shipping_email' => $subscription->shipping_email,
                'shipping_phone' => $subscription->shipping_phone,
                'shipping_address' => $subscription->shipping_address,
                'shipping_city' => $subscription->shipping_city,
                'shipping_state' => $subscription->shipping_state,
                'shipping_postcode' => $subscription->shipping_postcode,
                'shipping_country' => $subscription->shipping_country,
                'subtotal' => $subscription->total_price,
                'tax' => 0,
                'shipping_cost' => 0,
                'discount' => 0,
                'total' => $subscription->total_price,
                'notes' => "Subscription Order - {$subscription->subscription_number}",
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $subscription->product_id,
                'product_name' => $subscription->product->name,
                'quantity' => $subscription->quantity,
                'price' => $subscription->unit_price,
                'total' => $subscription->total_price,
            ]);

            // Update subscription
            $completedCycles = $subscription->completed_billing_cycles + 1;
            $nextBillingDate = $subscription->calculateNextBillingDate();

            // Check if subscription should expire
            $status = 'active';
            if (!$subscription->hasUnlimitedCycles() && $completedCycles >= $subscription->total_billing_cycles) {
                $status = 'expired';
            }
            if ($subscription->end_date && now()->startOfDay()->gt($subscription->end_date)) {
                $status = 'expired';
            }

            $subscription->update([
                'order_id' => $order->id,
                'completed_billing_cycles' => $completedCycles,
                'next_billing_date' => $status === 'expired' ? null : $nextBillingDate,
                'last_billing_at' => now(),
                'status' => $status,
                'payment_status' => 'pending',
            ]);

            // Update product stock
            $subscription->product->decrement('quantity', $subscription->quantity);

            DB::commit();

            return redirect()->route('admin.subscriptions.show', $subscription)
                            ->with('success', 'Billing processed successfully. Order #' . $order->order_number . ' created.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.subscriptions.show', $subscription)
                            ->with('error', 'Failed to process billing: ' . $e->getMessage());
        }
    }

    /**
     * Bulk action handler.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,pause,cancel,delete',
            'ids' => 'required|json',
        ]);

        $ids = json_decode($request->ids, true);
        $action = $request->action;

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No subscriptions selected.');
        }

        $subscriptions = Subscription::whereIn('id', $ids)->get();
        $processed = 0;

        foreach ($subscriptions as $subscription) {
            switch ($action) {
                case 'activate':
                    if (in_array($subscription->status, ['pending', 'paused'])) {
                        $subscription->update([
                            'status' => 'active',
                            'activated_at' => $subscription->activated_at ?? now(),
                        ]);
                        $processed++;
                    }
                    break;

                case 'pause':
                    if ($subscription->status === 'active') {
                        $subscription->update(['status' => 'paused']);
                        $processed++;
                    }
                    break;

                case 'cancel':
                    if (in_array($subscription->status, ['active', 'paused', 'pending'])) {
                        $subscription->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                            'cancelled_by' => Auth::id(),
                            'cancellation_reason' => $request->cancellation_reason ?? 'Bulk cancellation',
                        ]);
                        $processed++;
                    }
                    break;

                case 'delete':
                    if (in_array($subscription->status, ['pending', 'cancelled', 'expired'])) {
                        $subscription->delete();
                        $processed++;
                    }
                    break;
            }
        }

        return redirect()->back()->with('success', "{$processed} subscription(s) processed successfully.");
    }

    /**
     * Get customer details for AJAX request.
     */
    public function getCustomerDetails(User $user)
    {
        $defaultAddress = $user->addresses()->where('is_default', true)->first();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $defaultAddress ? [
                'first_name' => $defaultAddress->first_name,
                'last_name' => $defaultAddress->last_name,
                'email' => $user->email,
                'phone' => $defaultAddress->phone ?? $user->phone,
                'address' => $defaultAddress->address,
                'city' => $defaultAddress->city,
                'state' => $defaultAddress->state,
                'postcode' => $defaultAddress->postcode,
                'country' => $defaultAddress->country,
            ] : null,
        ]);
    }

    /**
     * Get product details for AJAX request.
     */
    public function getProductDetails(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'sku' => $product->sku,
            'quantity' => $product->quantity,
            'featured_image' => $product->featured_image,
        ]);
    }
}