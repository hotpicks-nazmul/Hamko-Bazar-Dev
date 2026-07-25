<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FlashDealController extends Controller
{
    /**
     * Display a listing of the flash deals.
     */
    public function index()
    {
        $flashDeals = FlashDeal::latest()->paginate(15);
        
        // "Active" must mean running: a deal whose end date passed is expired whatever the status
        // column says, and nothing ever writes status='expired' (the card was permanently 0).
        $stats = [
            'total' => FlashDeal::count(),
            'active' => FlashDeal::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count(),
            'inactive' => FlashDeal::where('status', 'inactive')->count(),
            'expired' => FlashDeal::where('end_date', '<', now())->count(),
            'featured' => FlashDeal::where('is_featured', true)->count(),
        ];
        
        return view('admin.marketing.flash-deals.index', compact('flashDeals', 'stats'));
    }

    /**
     * Show the form for creating a new flash deal.
     */
    public function create()
    {
        return view('admin.marketing.flash-deals.create');
    }

    /**
     * Store a newly created flash deal in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:flash_deals,title',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,expired',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'banner_image' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'title', 'start_date', 'end_date', 'status', 'background_color', 'text_color',
            'banner_image', 'is_featured', 'sort_order',
        ]);
        $data['is_featured'] = $request->boolean('is_featured');
        // The slug carries a UNIQUE index while store() never checked it: the second deal with the
        // same title was a 1062 crash.
        $data['slug'] = $this->uniqueSlug($request->title);

        FlashDeal::create($data);

        return redirect()->route('admin.marketing.flash-deals.index')
            ->with('success', 'Flash deal created successfully.');
    }

    /**
     * Show the form for editing the specified flash deal.
     */
    public function edit($id)
    {
        $flashDeal = FlashDeal::with('products')->findOrFail($id);
        return view('admin.marketing.flash-deals.edit', compact('flashDeal'));
    }

    /**
     * Update the specified flash deal in storage.
     */
    public function update(Request $request, $id)
    {
        $flashDeal = FlashDeal::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255|unique:flash_deals,title,' . $id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,expired',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'banner_image' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'title', 'start_date', 'end_date', 'status', 'background_color', 'text_color',
            'banner_image', 'is_featured', 'sort_order',
        ]);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['slug'] = $this->uniqueSlug($request->title, $id);

        $flashDeal->update($data);

        return redirect()->route('admin.marketing.flash-deals.index')
            ->with('success', 'Flash deal updated successfully.');
    }

    /**
     * Remove the specified flash deal from storage.
     */
    public function destroy($id)
    {
        $flashDeal = FlashDeal::findOrFail($id);
        $flashDeal->delete();

        return redirect()->route('admin.marketing.flash-deals.index')
            ->with('success', 'Flash deal deleted successfully.');
    }

    /**
     * Toggle the status of the flash deal.
     */
    public function toggleStatus($id)
    {
        $flashDeal = FlashDeal::findOrFail($id);

        // The list hides the button for an ended deal, but the route is still reachable: refuse to
        // switch on something whose window has closed instead of writing a status that cannot show.
        if ($flashDeal->status !== 'active' && $flashDeal->isExpired()) {
            return back()->with('error', 'This deal ended on ' . $flashDeal->end_date->format('d M, Y') . '. Edit its dates before activating it again.');
        }

        $newStatus = $flashDeal->status === 'active' ? 'inactive' : 'active';
        $flashDeal->update(['status' => $newStatus]);

        return back()->with('success', 'Flash deal status updated.');
    }

    /**
     * Slug that does not collide with an existing deal.
     */
    private function uniqueSlug(string $title, $excludeId = null): string
    {
        $base = Str::slug($title) ?: 'flash-deal';
        $slug = $base;
        $i = 1;

        while (FlashDeal::where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Show the form for adding products to a flash deal.
     */
    public function products(Request $request, $id)
    {
        $flashDeal = FlashDeal::with('products')->findOrFail($id);

        // `products` has no published/approved columns — it has is_active/is_approved, so this page
        // was SQLSTATE 42S22 (the picker could never open).
        $search = trim((string) $request->search);
        $products = Product::where('is_active', 1)
            ->where('is_approved', 1)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->appends($request->only('search'));

        return view('admin.marketing.flash-deals.products', compact('flashDeal', 'products', 'search'));
    }

    /**
     * Update products in the flash deal (bulk update).
     */
    public function updateProducts(Request $request, $id)
    {
        $flashDeal = FlashDeal::findOrFail($id);
        
        $request->validate([
            'products' => 'required|array',
        ]);
        
        $productsData = $request->products;
        
        foreach ($productsData as $productId => $productData) {
            if (isset($productData['selected']) && $productData['selected'] == '1') {
                // Keep the counter: writing sold_count => 0 on every save wiped the deal's sales.
                $existing = $flashDeal->products()->where('product_id', $productId)->first();
                $flashDeal->products()->syncWithoutDetaching([
                    $productId => [
                        'discount' => $productData['discount'] ?? 0,
                        'discount_type' => $productData['discount_type'] ?? 'percent',
                        'min_quantity' => $existing?->pivot->min_quantity ?? 1,
                        'max_quantity' => $existing?->pivot->max_quantity ?? 10,
                        'sold_count' => $existing?->pivot->sold_count ?? 0,
                    ]
                ]);
            } else {
                $flashDeal->products()->detach($productId);
            }
        }
        
        return redirect()->route('admin.marketing.flash-deals.products', $flashDeal->id)
            ->with('success', 'Flash deal products updated successfully.');
    }

    /**
     * Add products to the flash deal.
     */
    public function addProducts(Request $request, $id)
    {
        $flashDeal = FlashDeal::findOrFail($id);

        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'discounts' => 'required|array',
            'discounts.*' => 'required|numeric|min:0',
            'discount_types' => 'required|array',
            'discount_types.*' => 'required|in:percent,fixed',
            'min_quantities' => 'required|array',
            'min_quantities.*' => 'required|integer|min:1',
            'max_quantities' => 'required|array',
            'max_quantities.*' => 'required|integer|min:1',
        ]);

        // Validate that max_quantity >= min_quantity for each product
        foreach ($request->product_ids as $index => $productId) {
            if ($request->max_quantities[$index] < $request->min_quantities[$index]) {
                return back()->with('error', 'Maximum quantity must be greater than or equal to minimum quantity for each product.');
            }
        }

        foreach ($request->product_ids as $index => $productId) {
            $existing = $flashDeal->products()->where('product_id', $productId)->first();
            $flashDeal->products()->syncWithoutDetaching([
                $productId => [
                    'discount' => $request->discounts[$index],
                    'discount_type' => $request->discount_types[$index],
                    'min_quantity' => $request->min_quantities[$index],
                    'max_quantity' => $request->max_quantities[$index],
                    'sold_count' => $existing?->pivot->sold_count ?? 0,
                ]
            ]);
        }

        return back()->with('success', 'Products added to flash deal successfully.');
    }

    /**
     * Remove a product from the flash deal.
     */
    public function removeProduct($id, $productId)
    {
        $flashDeal = FlashDeal::findOrFail($id);
        $flashDeal->products()->detach($productId);

        return back()->with('success', 'Product removed from flash deal.');
    }

    /**
     * Update a specific product in the flash deal.
     */
    public function updateProduct(Request $request, $id, $productId)
    {
        $flashDeal = FlashDeal::findOrFail($id);

        $request->validate([
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percent,fixed',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'required|integer|min:1',
        ]);

        // Validate that max_quantity >= min_quantity
        if ($request->max_quantity < $request->min_quantity) {
            return back()->with('error', 'Maximum quantity must be greater than or equal to minimum quantity.');
        }

        $flashDeal->products()->updateExistingPivot($productId, [
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
            'min_quantity' => $request->min_quantity,
            'max_quantity' => $request->max_quantity,
        ]);

        return back()->with('success', 'Product updated in flash deal.');
    }
}
