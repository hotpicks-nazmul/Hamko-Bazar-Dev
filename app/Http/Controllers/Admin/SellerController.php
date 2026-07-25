<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Rules\Phone;
use Illuminate\Validation\Rule;

class SellerController extends Controller
{
    /**
     * Display a listing of sellers (B2B).
     */
    public function index(Request $request)
    {
        // Get statistics
        $stats = $this->getStats();

        // Build query for sellers (role = vendor)
        $query = User::sellers()->withCount('products');

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by verification status
        if ($request->verification_status) {
            $query->where('verification_status', $request->verification_status);
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->per_page ?? 10;
        $sellers = $query->paginate($perPage);

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.sellers.partials.table-rows', compact('sellers'))->render(),
                'stats' => $stats,
                'pagination' => $sellers->links()->toHtml(),
                'pagination_info' => [
                    'firstItem' => $sellers->firstItem(),
                    'lastItem' => $sellers->lastItem(),
                    'total' => $sellers->total(),
                ],
            ]);
        }

        return view('admin.sellers.index', compact('sellers', 'stats'));
    }

    /**
     * Get statistics for sellers
     */
    protected function getStats()
    {
        $sellers = User::sellers();

        return [
            'total' => (clone $sellers)->count(),
            'active' => (clone $sellers)->where('status', 'active')->count(),
            'inactive' => (clone $sellers)->where('status', 'inactive')->count(),
            'verified' => (clone $sellers)->where('verification_status', 'verified')->count(),
            'pending' => (clone $sellers)->where('verification_status', 'pending')->count(),
            'rejected' => (clone $sellers)->where('verification_status', 'rejected')->count(),
        ];
    }

    /**
     * Show the form for creating a new seller.
     */
    public function create()
    {
        return view('admin.sellers.create');
    }

    /**
     * Store a newly created seller.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => ['nullable', 'string', 'max:20', new Phone],
            'password' => 'required|string|min:8|confirmed',
            'shop_name' => 'nullable|string|max:255',
            'shop_description' => 'nullable|string',
            'seller_type' => 'required|in:individual,company',
            'company_name' => 'nullable|string|max:255',
            'business_registration_number' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_phone' => ['nullable', 'string', 'max:20', new Phone],
            'contact_person_email' => 'nullable|email|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_routing_code' => 'nullable|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'verification_status' => 'required|in:pending,verified,rejected',
        ]);

        $seller = new User;
        $seller->name = $request->name;
        $seller->email = $request->email;
        $seller->phone = $request->phone;
        $seller->password = Hash::make($request->password);
        $seller->role = 'vendor';
        $seller->shop_name = $request->shop_name;
        $seller->shop_description = $request->shop_description;
        $seller->seller_type = $request->seller_type;
        $seller->company_name = $request->company_name;
        $seller->business_registration_number = $request->business_registration_number;
        $seller->tax_id = $request->tax_id;
        $seller->contact_person_name = $request->contact_person_name;
        $seller->contact_person_phone = $request->contact_person_phone;
        $seller->contact_person_email = $request->contact_person_email;
        $seller->bank_name = $request->bank_name;
        $seller->bank_account_number = $request->bank_account_number;
        $seller->bank_account_name = $request->bank_account_name;
        $seller->bank_routing_code = $request->bank_routing_code;
        $seller->commission_rate = $request->commission_rate ?? 10.00;
        $seller->status = $request->status;
        $seller->verification_status = $request->verification_status;

        if ($request->verification_status === 'verified') {
            $seller->verified_at = now();
        }

        // Handle shop logo upload
        if ($request->hasFile('shop_logo')) {
            $logo = $request->file('shop_logo');
            $logoName = time().'_'.$logo->getClientOriginalName();
            $logo->move(public_path('uploads/shop_logos'), $logoName);
            $seller->shop_logo = $logoName;
        }

        // Handle shop banner upload
        if ($request->hasFile('shop_banner')) {
            $banner = $request->file('shop_banner');
            $bannerName = time().'_'.$banner->getClientOriginalName();
            $banner->move(public_path('uploads/shop_banners'), $bannerName);
            $seller->shop_banner = $bannerName;
        }

        $seller->save();

        return redirect()->route('admin.sellers.index')
            ->with('success', 'Seller created successfully.');
    }

    /**
     * Display the specified seller.
     */
    public function show(Request $request, $id)
    {
        $seller = User::sellers()->withCount('products')->findOrFail($id);

        // Get seller orders with products from this seller
        $orderIds = OrderItem::whereHas('product', function ($q) use ($id) {
            $q->where('seller_id', $id);
        })->distinct()->pluck('order_id');

        $orders = Order::whereIn('id', $orderIds)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate stats
        $totalSales = Order::whereIn('id', $orderIds)
            ->where('payment_status', 'paid')
            ->whereIn('status', ['delivered', 'shipped', 'confirmed'])
            ->sum('grand_total');

        $totalOrders = Order::whereIn('id', $orderIds)->count();

        $pendingOrders = Order::whereIn('id', $orderIds)
            ->whereIn('status', ['pending', 'processing', 'confirmed'])
            ->count();

        return view('admin.sellers.show', compact('seller', 'orders', 'totalSales', 'totalOrders', 'pendingOrders'));
    }

    /**
     * Show the form for editing the specified seller.
     */
    public function edit($id)
    {
        $seller = User::sellers()->findOrFail($id);

        return view('admin.sellers.edit', compact('seller'));
    }

    /**
     * Update the specified seller.
     */
    public function update(Request $request, $id)
    {
        $seller = User::sellers()->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($seller->id)],
            'phone' => ['nullable', 'string', 'max:20', new Phone],
            'password' => 'nullable|string|min:8|confirmed',
            'shop_name' => 'nullable|string|max:255',
            'shop_description' => 'nullable|string',
            'seller_type' => 'required|in:individual,company',
            'company_name' => 'nullable|string|max:255',
            'business_registration_number' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email|max:255',
            'return_address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_routing_code' => 'nullable|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'wallet_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'verification_status' => 'required|in:pending,verified,rejected',
            'verification_notes' => 'nullable|string',
        ]);

        $seller->name = $request->name;
        $seller->email = $request->email;
        $seller->phone = $request->phone;

        if ($request->password) {
            $seller->password = Hash::make($request->password);
        }

        $seller->shop_name = $request->shop_name;
        $seller->shop_description = $request->shop_description;
        $seller->seller_type = $request->seller_type;
        $seller->company_name = $request->company_name;
        $seller->business_registration_number = $request->business_registration_number;
        $seller->tax_id = $request->tax_id;
        $seller->contact_person_name = $request->contact_person_name;
        $seller->contact_person_phone = $request->contact_person_phone;
        $seller->contact_person_email = $request->contact_person_email;
        $seller->return_address = $request->return_address;
        $seller->bank_name = $request->bank_name;
        $seller->bank_account_number = $request->bank_account_number;
        $seller->bank_account_name = $request->bank_account_name;
        $seller->bank_routing_code = $request->bank_routing_code;
        $seller->commission_rate = $request->commission_rate ?? 10.00;

        if ($request->has('wallet_balance')) {
            $seller->wallet_balance = $request->wallet_balance;
        }

        $seller->status = $request->status;

        // Handle verification status changes
        $oldVerificationStatus = $seller->verification_status;
        $newVerificationStatus = $request->verification_status;

        if ($newVerificationStatus === 'verified' && $oldVerificationStatus !== 'verified') {
            $seller->verified_at = now();
        } elseif ($newVerificationStatus !== 'verified') {
            $seller->verified_at = null;
        }

        $seller->verification_status = $newVerificationStatus;
        $seller->verification_notes = $request->verification_notes;

        // Handle shop logo upload
        if ($request->hasFile('shop_logo')) {
            // Delete old logo
            if ($seller->shop_logo && File::exists(public_path('uploads/shop_logos/'.$seller->shop_logo))) {
                File::delete(public_path('uploads/shop_logos/'.$seller->shop_logo));
            }
            $logo = $request->file('shop_logo');
            $logoName = time().'_'.$logo->getClientOriginalName();
            $logo->move(public_path('uploads/shop_logos'), $logoName);
            $seller->shop_logo = $logoName;
        }

        // Handle shop banner upload
        if ($request->hasFile('shop_banner')) {
            // Delete old banner
            if ($seller->shop_banner && File::exists(public_path('uploads/shop_banners/'.$seller->shop_banner))) {
                File::delete(public_path('uploads/shop_banners/'.$seller->shop_banner));
            }
            $banner = $request->file('shop_banner');
            $bannerName = time().'_'.$banner->getClientOriginalName();
            $banner->move(public_path('uploads/shop_banners'), $bannerName);
            $seller->shop_banner = $bannerName;
        }

        $seller->save();

        return redirect()->route('admin.sellers.index')
            ->with('success', 'Seller updated successfully.');
    }

    /**
     * Remove the specified seller.
     */
    public function destroy($id)
    {
        $seller = User::sellers()->findOrFail($id);

        // Delete associated files
        if ($seller->shop_logo && File::exists(public_path('uploads/shop_logos/'.$seller->shop_logo))) {
            File::delete(public_path('uploads/shop_logos/'.$seller->shop_logo));
        }
        if ($seller->shop_banner && File::exists(public_path('uploads/shop_banners/'.$seller->shop_banner))) {
            File::delete(public_path('uploads/shop_banners/'.$seller->shop_banner));
        }

        // Update products to remove seller association
        Product::where('seller_id', $seller->id)->update([
            'seller_id' => null,
            'product_source' => 'in_house',
        ]);

        $seller->delete();

        return redirect()->route('admin.sellers.index')
            ->with('success', 'Seller deleted successfully.');
    }

    /**
     * Approve a seller.
     */
    public function approve(Request $request, $id)
    {
        $seller = User::sellers()->findOrFail($id);

        $seller->verification_status = 'verified';
        $seller->verified_at = now();
        $seller->status = 'active';
        $seller->save();

        return redirect()->back()
            ->with('success', 'Seller approved successfully.');
    }

    /**
     * Reject a seller verification.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'verification_notes' => 'required|string',
        ]);

        $seller = User::sellers()->findOrFail($id);

        $seller->verification_status = 'rejected';
        $seller->verification_notes = $request->verification_notes;
        $seller->save();

        return redirect()->back()
            ->with('success', 'Seller verification rejected.');
    }

    /**
     * Suspend a seller.
     */
    public function suspend(Request $request, $id)
    {
        $seller = User::sellers()->findOrFail($id);

        $seller->status = 'inactive';
        $seller->save();

        return redirect()->back()
            ->with('success', 'Seller suspended successfully.');
    }

    /**
     * Activate a seller.
     */
    public function activate($id)
    {
        $seller = User::sellers()->findOrFail($id);

        $seller->status = 'active';
        $seller->save();

        return redirect()->back()
            ->with('success', 'Seller activated successfully.');
    }

    /**
     * Display seller payouts (completed payouts history).
     */
    public function payouts(Request $request)
    {
        $stats = $this->getPayoutStats();

        $query = SellerPayout::with('seller')
            ->select('seller_payouts.*');

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('seller', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // Date range filter
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->per_page ?? 25;
        $payouts = $query->paginate($perPage);

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.sellers.payouts.partials.table-rows', compact('payouts'))->render(),
                'stats' => $stats,
                'pagination' => $payouts->links()->toHtml(),
                'pagination_info' => 'Showing '.$payouts->firstItem().' - '.$payouts->lastItem().' of '.$payouts->total().' payouts',
            ]);
        }

        return view('admin.sellers.payouts.index', compact('payouts', 'stats'));
    }

    /**
     * Get payout statistics.
     */
    protected function getPayoutStats()
    {
        return [
            'total_payouts' => SellerPayout::count(),
            'total_amount' => SellerPayout::where('status', 'completed')->sum('amount'),
            'pending' => SellerPayout::whereIn('status', ['pending', 'approved'])->count(),
            'pending_amount' => SellerPayout::whereIn('status', ['pending', 'approved'])->sum('amount'),
            'pending_only' => SellerPayout::where('status', 'pending')->count(),
            'approved' => SellerPayout::where('status', 'approved')->count(),
            'completed' => SellerPayout::where('status', 'completed')->count(),
            'rejected' => SellerPayout::where('status', 'rejected')->count(),
        ];
    }

    /**
     * Display seller payout requests (pending payouts).
     */
    public function payoutRequests(Request $request)
    {
        $stats = $this->getPayoutStats();

        $query = SellerPayout::with('seller')
            ->select('seller_payouts.*');

        // Filter by status - default to pending
        if ($request->status === 'pending_approved' || ! $request->status) {
            $query->whereIn('status', ['pending', 'approved']);
        } elseif ($request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('seller', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->per_page ?? 25;
        $payouts = $query->paginate($perPage);

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.sellers.payouts.partials.request-rows', compact('payouts'))->render(),
                'stats' => $stats,
                'pagination' => $payouts->links()->toHtml(),
                'pagination_info' => 'Showing '.$payouts->firstItem().' - '.$payouts->lastItem().' of '.$payouts->total().' requests',
            ]);
        }

        return view('admin.sellers.payout-requests', compact('payouts', 'stats'));
    }

    /**
     * Show create payout form for a seller.
     */
    public function createPayout(Request $request, $id)
    {
        $seller = User::sellers()->findOrFail($id);

        return view('admin.sellers.payouts.create', compact('seller'));
    }

    /**
     * Store a new payout for a seller.
     */
    public function storePayout(Request $request, $id)
    {
        $seller = User::sellers()->findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:1|max:'.($seller->wallet_balance + $seller->pending_balance),
            'payment_method' => 'required|in:bank_transfer,cash,mobile_banking,cheque,other',
            'transaction_id' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Calculate commission
        $commissionRate = $seller->commission_rate ?? 10;
        $amount = $request->amount;
        $commission = ($amount * $commissionRate) / 100;
        $netAmount = $amount - $commission;

        // Create payout record
        $payout = new SellerPayout;
        $payout->seller_id = $seller->id;
        $payout->amount = $amount;
        $payout->commission = $commission;
        $payout->net_amount = $netAmount;
        $payout->status = 'completed'; // Direct completion for admin-initiated payouts
        $payout->payment_method = $request->payment_method;
        $payout->transaction_id = $request->transaction_id;
        $payout->bank_name = $request->bank_name;
        $payout->account_number = $request->account_number;
        $payout->account_name = $request->account_name;
        $payout->notes = $request->notes;
        $payout->processed_by = auth()->id();
        $payout->processed_at = now();
        $payout->approved_at = now();
        $payout->save();

        // Deduct from seller's wallet
        if ($request->from_wallet) {
            $seller->wallet_balance = max(0, $seller->wallet_balance - $amount);
        } else {
            $seller->pending_balance = max(0, $seller->pending_balance - $amount);
        }
        $seller->save();

        return redirect()->route('admin.sellers.payouts')
            ->with('success', 'Payout of BDT '.number_format($amount, 2).' processed successfully for '.($seller->shop_name ?? $seller->name));
    }

    /**
     * Approve a payout request.
     */
    public function approvePayout(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $payout = SellerPayout::findOrFail($id);
        $seller = $payout->seller;

        if ($seller->pending_balance < $payout->amount) {
            return redirect()->back()
                ->with('error', 'Insufficient pending balance. Seller has BDT '.number_format($seller->pending_balance, 2).' but payout amount is BDT '.number_format($payout->amount, 2));
        }

        // Deduct from seller's pending balance
        $seller->pending_balance = max(0, $seller->pending_balance - $payout->amount);
        $seller->save();

        // Update payout status
        $payout->status = 'completed';
        $payout->admin_notes = $request->admin_notes;
        $payout->transaction_id = $request->transaction_id;
        $payout->processed_by = auth()->id();
        $payout->processed_at = now();
        $payout->approved_at = now();
        $payout->save();

        return redirect()->back()
            ->with('success', 'Payout approved and completed successfully.');
    }

    /**
     * Reject a payout request.
     */
    public function rejectPayout(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $payout = SellerPayout::findOrFail($id);
        $seller = $payout->seller;

        // Update payout status
        $payout->status = 'rejected';
        $payout->admin_notes = $request->admin_notes;
        $payout->processed_by = auth()->id();
        $payout->rejected_at = now();
        $payout->save();

        // Restore seller's balance
        $seller->pending_balance = $seller->pending_balance + $payout->amount;
        $seller->save();

        return redirect()->back()
            ->with('success', 'Payout rejected. Seller balance has been restored.');
    }

    /**
     * Show payout details.
     */
    public function showPayout($id)
    {
        $payout = SellerPayout::with('seller', 'processedBy')->findOrFail($id);

        return view('admin.sellers.payouts.show', compact('payout'));
    }

    /**
     * Display seller commission settings.
     */
    public function commission(Request $request)
    {
        // Get default commission rate (from first seller or default)
        $defaultCommission = User::sellers()->avg('commission_rate') ?? 10.00;

        // Build query
        $query = User::sellers()
            ->select('id', 'name', 'shop_name', 'email', 'commission_rate', 'wallet_balance', 'pending_balance', 'status');

        // Search filter
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Commission range filter
        if ($request->commission_range) {
            $range = $request->commission_range;
            if ($range === '20+') {
                $query->where('commission_rate', '>=', 20);
            } elseif (strpos($range, '-') !== false) {
                [$min, $max] = explode('-', $range);
                $query->whereBetween('commission_rate', [(float) $min, (float) $max]);
            }
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sellers = $query->orderBy('shop_name')->paginate(20);

        // Calculate stats
        $stats = [
            'total_sellers' => User::sellers()->count(),
            'avg_commission' => number_format(User::sellers()->avg('commission_rate') ?? 0, 2),
            'total_revenue' => User::sellers()->sum('wallet_balance'),
        ];

        // AJAX request
        if ($request->ajax()) {
            $html = '';
            foreach ($sellers as $key => $seller) {
                $html .= '<tr>';
                $html .= '<td>' . ($sellers->firstItem() + $key) . '</td>';
                $html .= '<td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle bg-primary text-white me-2">' . strtoupper(substr($seller->name ?? 'S', 0, 1)) . '</div>
                        <div class="fw-medium">' . $seller->name . '</div>
                    </div>
                </td>';
                $html .= '<td>' . ($seller->shop_name ?? '-') . '</td>';
                $html .= '<td>' . $seller->email . '</td>';
                $html .= '<td><span class="badge bg-info">' . ($seller->commission_rate ?? $defaultCommission) . '%</span></td>';
                $html .= '<td>৳' . number_format($seller->wallet_balance ?? 0, 2) . '</td>';
                $html .= '<td>' . ($seller->status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>') . '</td>';
                $html .= '<td>
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#editCommissionModal"
                            data-seller-id="' . $seller->id . '"
                            data-seller-name="' . htmlspecialchars($seller->name) . '"
                            data-shop-name="' . htmlspecialchars($seller->shop_name ?? '') . '"
                            data-commission-rate="' . ($seller->commission_rate ?? $defaultCommission) . '"
                            title="Edit Commission">
                        <i class="bi bi-pencil"></i>
                    </button>
                </td>';
                $html .= '</tr>';
            }

            if ($sellers->isEmpty()) {
                $html .= '<tr><td colspan="8" class="text-center py-5">
                    <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mb-2 mt-2">No sellers found</p>
                </td></tr>';
            }

            $paginationHtml = '';
            if ($sellers->hasPages()) {
                $paginationHtml = '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">Showing ' . $sellers->firstItem() . ' - ' . $sellers->lastItem() . ' of ' . $sellers->total() . ' sellers</div>
                    <div>' . $sellers->appends(request()->query())->links() . '</div>
                </div>';
            }

            return response()->json([
                'html' => $html,
                'pagination' => $paginationHtml,
                'stats' => $stats,
            ]);
        }

        return view('admin.sellers.commission', compact('sellers', 'defaultCommission', 'stats'));
    }

    /**
     * Update seller commission settings.
     */
    public function updateCommission(Request $request)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Update individual seller if ID provided
        if ($request->seller_id) {
            $seller = User::sellers()->findOrFail($request->seller_id);
            $seller->commission_rate = $request->commission_rate;
            $seller->save();

            return redirect()->route('admin.sellers.commission')
                ->with('success', 'Commission rate updated for '.($seller->shop_name ?? $seller->name));
        }

        // Update all sellers (bulk)
        User::sellers()->update(['commission_rate' => $request->commission_rate]);

        return redirect()->route('admin.sellers.commission')
            ->with('success', 'Default commission rate updated for all sellers.');
    }

    /**
     * Display seller verification requests.
     */
    public function verification(Request $request)
    {
        // Get statistics
        $stats = $this->getVerificationStats();

        $query = User::sellers()->whereIn('verification_status', ['pending', 'rejected']);

        if ($request->verification_status) {
            $query->where('verification_status', $request->verification_status);
        }

        // Filter by seller type
        if ($request->seller_type) {
            $query->where('seller_type', $request->seller_type);
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $sellers = $query->orderBy('created_at', 'desc')->paginate(20);

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.sellers.partials.verification-rows', compact('sellers'))->render(),
                'pagination' => $sellers->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.sellers.verification', compact('sellers', 'stats'));
    }

    /**
     * Get verification statistics.
     */
    protected function getVerificationStats()
    {
        $sellers = User::sellers();

        return [
            'pending' => (clone $sellers)->where('verification_status', 'pending')->count(),
            'rejected' => (clone $sellers)->where('verification_status', 'rejected')->count(),
            'verified' => (clone $sellers)->where('verification_status', 'verified')->count(),
            'total' => (clone $sellers)->count(),
        ];
    }

    /**
     * Process seller verification.
     */
    public function processVerification(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'verification_notes' => 'nullable|string',
        ]);

        $seller = User::sellers()->findOrFail($id);

        if ($request->action === 'approve') {
            $seller->verification_status = 'verified';
            $seller->verified_at = now();
            $seller->verification_notes = $request->verification_notes;
            $seller->status = 'active';
            $seller->save();

            return redirect()->back()
                ->with('success', 'Seller verification approved.');
        } else {
            $seller->verification_status = 'rejected';
            $seller->verification_notes = $request->verification_notes;
            $seller->save();

            return redirect()->back()
                ->with('success', 'Seller verification rejected.');
        }
    }

    /**
     * Bulk update seller status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'seller_ids' => 'required|array',
            'status' => 'required|in:active,inactive',
        ]);

        User::sellers()->whereIn('id', $request->seller_ids)->update([
            'status' => $request->status,
        ]);

        $count = count($request->seller_ids);

        return response()->json([
            'success' => true,
            'message' => "{$count} seller(s) status updated to {$request->status}",
        ]);
    }

    /**
     * Bulk delete sellers
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'seller_ids' => 'required|array',
        ]);

        $sellers = User::sellers()->whereIn('id', $request->seller_ids)->get();

        foreach ($sellers as $seller) {
            // Delete associated files
            if ($seller->shop_logo && File::exists(public_path('uploads/shop_logos/'.$seller->shop_logo))) {
                File::delete(public_path('uploads/shop_logos/'.$seller->shop_logo));
            }
            if ($seller->shop_banner && File::exists(public_path('uploads/shop_banners/'.$seller->shop_banner))) {
                File::delete(public_path('uploads/shop_banners/'.$seller->shop_banner));
            }

            // Update products
            Product::where('seller_id', $seller->id)->update([
                'seller_id' => null,
                'product_source' => 'in_house',
            ]);

            $seller->delete();
        }

        $count = count($request->seller_ids);

        return response()->json([
            'success' => true,
            'message' => "{$count} seller(s) deleted successfully",
        ]);
    }
}
