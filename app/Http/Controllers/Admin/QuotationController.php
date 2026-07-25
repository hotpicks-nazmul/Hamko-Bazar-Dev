<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Admin\Concerns\ListQueryParams;

class QuotationController extends Controller
{
    use ListQueryParams;

    /**
     * Get statistics for quotations
     */
    protected function getStats()
    {
        return [
            'total' => Quotation::count(),
            'pending' => Quotation::where('status', 'pending')->count(),
            'sent' => Quotation::where('status', 'sent')->count(),
            'accepted' => Quotation::where('status', 'accepted')->count(),
            'rejected' => Quotation::where('status', 'rejected')->count(),
            'converted' => Quotation::where('status', 'converted')->count(),
            'expired' => Quotation::expired()->count(),
        ];
    }

    /**
     * Get filtered stats based on current query filters.
     */
    protected function getFilteredStats($query)
    {
        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'sent' => (clone $query)->where('status', 'sent')->count(),
            'accepted' => (clone $query)->where('status', 'accepted')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'converted' => (clone $query)->where('status', 'converted')->count(),
            'expired' => (clone $query)->where(function($q) {
                $q->where('valid_until', '<', now()->startOfDay())
                  ->whereNotIn('status', ['converted', 'rejected']);
            })->count(),
        ];
    }

    /**
     * Display a listing of quotations.
     */
    public function index(Request $request)
    {
        $query = Quotation::with(['user', 'items']);

        // Search by quotation number, customer name, email, phone
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            if ($request->status === 'expired') {
                $query->expired();
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $this->applySort($query, $request, ['created_at', 'quotation_number', 'total', 'status',
            'valid_until', 'customer_name', 'updated_at', 'id']);

        // Stats follow the filters on both the first load and the AJAX refresh.
        $stats = $this->getFilteredStats(clone $query);

        $quotations = $query->paginate($this->perPage($request));

        // Get search term for highlighting
        $search = $request->get('search');

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.quotations.partials.table-rows', compact('quotations', 'search'))->render(),
                'pagination' => $quotations->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.quotations.index', compact('quotations', 'stats', 'search'));
    }

    /**
     * Show the form for creating a new quotation.
     */
    public function create()
    {
        $products = Product::where('is_active', true)
                          ->select('id', 'name', 'price', 'sku', 'quantity')
                          ->orderBy('name')
                          ->get();
        
        $customers = User::where('role', 'customer')
                        ->orWhereNull('role')
                        ->select('id', 'name', 'email', 'phone')
                        ->orderBy('name')
                        ->get();

        // Default valid until (30 days from now)
        $defaultValidUntil = now()->addDays(30)->format('Y-m-d');

        return view('admin.quotations.create', compact('products', 'customers', 'defaultValidUntil'));
    }

    /**
     * Store a newly created quotation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            'customer_city' => 'nullable|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_postcode' => 'nullable|string|max:20',
            'customer_country' => 'nullable|string|max:100',
            'valid_until' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:2000',
            'terms_conditions' => 'nullable|string|max:2000',
            'user_id' => 'nullable|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create quotation
            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateQuotationNumber(),
                'user_id' => $validated['user_id'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_city' => $validated['customer_city'] ?? null,
                'customer_state' => $validated['customer_state'] ?? null,
                'customer_postcode' => $validated['customer_postcode'] ?? null,
                'customer_country' => $validated['customer_country'] ?? null,
                'valid_until' => $validated['valid_until'],
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'status' => 'pending',
            ]);

            // Create quotation items
            foreach ($validated['items'] as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            // Calculate totals
            $quotation->calculateTotals();

            DB::commit();

            return redirect()->route('admin.quotations.show', $quotation)
                           ->with('success', 'Quotation created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create quotation: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Display the specified quotation.
     */
    public function show(Quotation $quotation)
    {
        $quotation->load(['user', 'items.product', 'convertedOrder']);
        return view('admin.quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified quotation.
     */
    public function edit(Quotation $quotation)
    {
        if (!$quotation->can_edit) {
            return redirect()->route('admin.quotations.show', $quotation)
                           ->with('error', 'This quotation cannot be edited.');
        }

        $quotation->load('items');
        $products = Product::where('is_active', true)
                          ->select('id', 'name', 'price', 'sku', 'quantity')
                          ->orderBy('name')
                          ->get();
        
        $customers = User::where('role', 'customer')
                        ->orWhereNull('role')
                        ->select('id', 'name', 'email', 'phone')
                        ->orderBy('name')
                        ->get();

        return view('admin.quotations.edit', compact('quotation', 'products', 'customers'));
    }

    /**
     * Update the specified quotation.
     */
    public function update(Request $request, Quotation $quotation)
    {
        if (!$quotation->can_edit) {
            return redirect()->route('admin.quotations.show', $quotation)
                           ->with('error', 'This quotation cannot be edited.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            'customer_city' => 'nullable|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_postcode' => 'nullable|string|max:20',
            'customer_country' => 'nullable|string|max:100',
            'valid_until' => 'required|date',
            'notes' => 'nullable|string|max:2000',
            'terms_conditions' => 'nullable|string|max:2000',
            'user_id' => 'nullable|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update quotation
            $quotation->update([
                'user_id' => $validated['user_id'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_city' => $validated['customer_city'] ?? null,
                'customer_state' => $validated['customer_state'] ?? null,
                'customer_postcode' => $validated['customer_postcode'] ?? null,
                'customer_country' => $validated['customer_country'] ?? null,
                'valid_until' => $validated['valid_until'],
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
            ]);

            // Delete existing items and recreate
            $quotation->items()->delete();

            foreach ($validated['items'] as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            // Recalculate totals
            $quotation->calculateTotals();

            DB::commit();

            return redirect()->route('admin.quotations.show', $quotation)
                           ->with('success', 'Quotation updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update quotation: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Remove the specified quotation.
     */
    public function destroy(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'Cannot delete a converted quotation.');
        }

        try {
            $quotation->delete();
            return redirect()->route('admin.quotations.index')
                           ->with('success', 'Quotation deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete quotation: ' . $e->getMessage());
        }
    }

    /**
     * Send quotation to customer.
     */
    public function send(Request $request, Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'This quotation was already converted to an order.');
        }

        if ($quotation->is_expired) {
            return back()->with('error', 'Cannot send an expired quotation.');
        }

        try {
            $quotation->markAsSent();
            
            // TODO: Send email notification
            // Mail::to($quotation->customer_email)->send(new QuotationSent($quotation));

            return back()->with('success', 'Quotation marked as sent.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send quotation: ' . $e->getMessage());
        }
    }

    /**
     * Convert quotation to order.
     */
    public function convertToOrder(Request $request, Quotation $quotation)
    {
        if (!$quotation->can_convert) {
            return back()->with('error', 'This quotation cannot be converted to an order.');
        }

        // These three went straight into Order::create(): an order_status outside the enum
        // answered 500 (SQLSTATE 01000), and payment_status was never checked at all.
        $request->validate([
            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'order_status' => 'nullable|in:pending,processing,confirmed',
        ]);

        try {
            DB::beginTransaction();

            // Create order from quotation
            $order = Order::create([
                'user_id' => $quotation->user_id,
                'order_number' => Order::generateOrderNumber(),
                'order_type' => 'inhouse',
                'billing_first_name' => explode(' ', $quotation->customer_name)[0] ?? $quotation->customer_name,
                'billing_last_name' => implode(' ', array_slice(explode(' ', $quotation->customer_name), 1)),
                'billing_email' => $quotation->customer_email,
                'billing_phone' => $quotation->customer_phone,
                'billing_address' => $quotation->customer_address,
                'billing_city' => $quotation->customer_city,
                'billing_state' => $quotation->customer_state,
                'billing_postcode' => $quotation->customer_postcode,
                'billing_country' => $quotation->customer_country,
                'shipping_first_name' => explode(' ', $quotation->customer_name)[0] ?? $quotation->customer_name,
                'shipping_last_name' => implode(' ', array_slice(explode(' ', $quotation->customer_name), 1)),
                'shipping_email' => $quotation->customer_email,
                'shipping_phone' => $quotation->customer_phone,
                'shipping_address' => $quotation->customer_address,
                'shipping_city' => $quotation->customer_city,
                'shipping_state' => $quotation->customer_state,
                'shipping_postcode' => $quotation->customer_postcode,
                'shipping_country' => $quotation->customer_country,
                'subtotal' => $quotation->subtotal,
                'shipping_cost' => 0,
                'tax' => $quotation->tax,
                'discount' => $quotation->discount,
                'total' => $quotation->total,
                'payment_method' => $request->payment_method ?? 'manual',
                'payment_status' => $request->payment_status ?? 'pending',
                'status' => $request->order_status ?? 'pending',
                'notes' => $quotation->notes,
                // Converted orders belong to the operator's warehouse, like every other order
                // created from the panel; without it warehouse staff never see them.
                'warehouse_id' => auth()->user()->warehouse_id ?: null,
            ]);

            // Create order items from quotation items
            foreach ($quotation->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'variation' => $item->variation,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'total' => $item->total,
                ]);

                // Decrease product stock if applicable (shared service: status + history)
                if ($item->product_id && $item->product) {
                    app(\App\Services\StockService::class)->deduct(
                        $item->product,
                        (int) $item->quantity,
                        'Quotation ' . ($quotation->quotation_number ?? $quotation->id) . ' converted to order'
                    );
                }
            }

            // Mark quotation as converted
            $quotation->markAsConverted($order->id, auth()->user()->name ?? 'Admin');

            DB::commit();

            return redirect()->route('admin.orders.in-house.show', $order)
                           ->with('success', 'Quotation converted to order successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to convert quotation: ' . $e->getMessage());
        }
    }

    /**
     * Print quotation.
     */
    public function print(Quotation $quotation)
    {
        $quotation->load(['user', 'items.product']);
        return view('admin.quotations.print', compact('quotation'));
    }

    /**
     * Download quotation as PDF.
     */
    public function download(Quotation $quotation)
    {
        $quotation->load(['user', 'items.product']);

        // No PDF library is installed, and the old version simply re-rendered the page in the
        // browser, so "Download" downloaded nothing. Serve the print document as a named file.
        $filename = ($quotation->quotation_number ?: 'quotation-' . $quotation->id) . '.html';

        return response()
            ->view('admin.quotations.print', compact('quotation'))
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Bulk action on quotations.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:quotations,id',
            'action' => 'required|in:delete,mark_sent',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        try {
            switch ($action) {
                case 'delete':
                    $convertedCount = Quotation::whereIn('id', $ids)
                                               ->where('status', 'converted')
                                               ->count();
                    
                    if ($convertedCount > 0) {
                        return back()->with('error', "Cannot delete {$convertedCount} converted quotation(s). Only non-converted quotations can be deleted.");
                    }
                    
                    $deleted = Quotation::whereIn('id', $ids)
                                        ->where('status', '!=', 'converted')
                                        ->delete();
                    
                    if ($deleted > 0) {
                        $message = "{$deleted} quotation(s) deleted successfully.";
                    } else {
                        $message = 'No quotations were deleted.';
                    }
                    break;

                case 'mark_sent':
                    $updated = Quotation::whereIn('id', $ids)
                                        ->whereIn('status', ['pending'])
                                        ->update([
                                            'status' => 'sent',
                                            'sent_at' => now(),
                                        ]);
                    
                    if ($updated > 0) {
                        $message = "{$updated} quotation(s) marked as sent.";
                    } else {
                        $message = 'No quotations were updated. Only pending quotations can be marked as sent.';
                    }
                    break;

                default:
                    return back()->with('error', 'Invalid action.');
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to perform bulk action: ' . $e->getMessage());
        }
    }

    /**
     * Update quotation status.
     */
    public function updateStatus(Request $request, Quotation $quotation)
    {
        // Resetting a converted quotation to pending/rejected left the order it created standing,
        // and once it looked ordinary again it could be deleted as well.
        if ($quotation->status === 'converted') {
            return back()->with('error', 'This quotation was converted to an order, so its status is final.');
        }

        $request->validate([
            'status' => 'required|in:pending,sent,accepted,rejected',
        ]);

        $status = $request->status;

        try {
            switch ($status) {
                case 'sent':
                    $quotation->markAsSent();
                    break;
                case 'accepted':
                    $quotation->markAsAccepted();
                    break;
                case 'rejected':
                    $quotation->markAsRejected();
                    break;
                default:
                    $quotation->update(['status' => $status]);
            }

            return back()->with('success', 'Quotation status updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Get product details for AJAX.
     */
    public function getProduct(Request $request)
    {
        $product = Product::find($request->product_id);
        
        if ($product) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'stock' => $product->quantity,
                    'description' => $product->short_description ?? $product->description,
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Product not found.']);
    }

    /**
     * Search products for AJAX.
     */
    public function searchProducts(Request $request)
    {
        $search = $request->get('q');
        
        $products = Product::where('is_active', true)
                          ->where(function($q) use ($search) {
                              $q->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                          })
                          ->select('id', 'name', 'price', 'sku', 'quantity')
                          ->limit(20)
                          ->get();

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }
}
