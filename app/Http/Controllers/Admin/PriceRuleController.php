<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceRule;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PriceRuleController extends Controller
{
    /**
     * Display a listing of the price rules.
     */
    public function index(Request $request)
    {
        $query = PriceRule::query();

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Discount type filter
        if ($request->discount_type) {
            $query->where('discount_type', $request->discount_type);
        }

        // Sorting — whitelisted (?sort=nope was SQLSTATE 42S22).
        $sortable = ['name', 'discount_type', 'discount_value', 'priority', 'status', 'start_date', 'end_date', 'created_at'];
        $sort = in_array($request->sort, $sortable, true) ? $request->sort : 'created_at';
        $direction = strtolower((string) $request->direction) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);

        $priceRules = $query->paginate(15);

        // Statistics
        // Same rule as the deal list: active means inside its window, and "upcoming" is not active yet.
        $stats = [
            'total' => PriceRule::count(),
            'active' => PriceRule::where('status', 'active')
                ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
                ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                ->count(),
            'upcoming' => PriceRule::where('status', 'active')
                ->where('start_date', '>', now())
                ->count(),
            'expired' => PriceRule::whereNotNull('end_date')->where('end_date', '<', now())->count(),
        ];

        return view('admin.marketing.price-rules.index', compact('priceRules', 'stats'));
    }

    /**
     * Show the form for creating a new price rule.
     */
    public function create()
    {
        $categories = Category::where('parent_id', 0)
            ->with('children')
            ->orderBy('name')
            ->get();
            
        return view('admin.marketing.price-rules.create', compact('categories'));
    }

    /**
     * Store a newly created price rule in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:price_rules,name',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'nullable|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $data = $request->all();
        $data['slug'] = $this->generateUniqueSlug($request->name);
        $data['is_featured'] = $request->boolean('is_featured', false);

        DB::beginTransaction();
        try {
            $priceRule = PriceRule::create($data);

            // Attach products if any
            if ($request->product_ids) {
                $products = [];
                foreach ($request->product_ids as $productId) {
                    $products[$productId] = [
                        'discount' => $request->discount_value,
                        'discount_type' => $request->discount_type,
                    ];
                }
                $priceRule->products()->sync($products);
            }

            // Attach categories if any
            if ($request->category_ids) {
                $priceRule->categories()->sync($request->category_ids);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create price rule: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.marketing.price-rules.index')
            ->with('success', 'Price rule created successfully.');
    }

    /**
     * Show the form for editing the specified price rule.
     */
    public function edit($id)
    {
        $priceRule = PriceRule::with(['products', 'categories'])->findOrFail($id);
        
        $categories = Category::where('parent_id', 0)
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('admin.marketing.price-rules.edit', compact('priceRule', 'categories'));
    }

    /**
     * Update the specified price rule in storage.
     */
    public function update(Request $request, $id)
    {
        $priceRule = PriceRule::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:price_rules,name,' . $id,
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'nullable|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $data = $request->all();
        $data['slug'] = $this->generateUniqueSlug($request->name, $id);
        $data['is_featured'] = $request->boolean('is_featured', false);

        DB::beginTransaction();
        try {
            $priceRule->update($data);

            // Sync products. Only when the form actually sent the field: a request without
            // product_ids (an API call, a partial save) used to detach every product the rule had.
            if ($request->has('product_ids')) {
                $products = [];
                foreach ((array) $request->product_ids as $productId) {
                    $products[$productId] = [
                        'discount' => $request->discount_value,
                        'discount_type' => $request->discount_type,
                    ];
                }
                $priceRule->products()->sync($products);
            }

            // Sync categories
            if ($request->has('category_ids')) {
                $priceRule->categories()->sync($request->category_ids ?? []);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update price rule: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.marketing.price-rules.index')
            ->with('success', 'Price rule updated successfully.');
    }

    /**
     * Remove the specified price rule from storage.
     */
    public function destroy($id)
    {
        $priceRule = PriceRule::findOrFail($id);
        $priceRule->delete();

        return redirect()->route('admin.marketing.price-rules.index')
            ->with('success', 'Price rule deleted successfully.');
    }

    /**
     * Toggle the status of the price rule.
     */
    public function toggleStatus($id)
    {
        $priceRule = PriceRule::findOrFail($id);

        if ($priceRule->status !== 'active' && $priceRule->isExpired()) {
            return back()->with('error', 'This price rule ended on ' . $priceRule->end_date->format('d M, Y') . '. Edit its dates before activating it again.');
        }

        $newStatus = $priceRule->status === 'active' ? 'inactive' : 'active';
        $priceRule->update(['status' => $newStatus]);

        return back()->with('success', 'Price rule status updated.');
    }

    /**
     * Show the form for managing products in a price rule.
     */
    public function products(Request $request, $id)
    {
        $priceRule = PriceRule::with('products')->findOrFail($id);

        // products has is_active/is_approved, not published/approved (the page was a 42S22 crash).
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

        return view('admin.marketing.price-rules.products', compact('priceRule', 'products', 'search'));
    }

    /**
     * Update products in the price rule.
     */
    public function updateProducts(Request $request, $id)
    {
        $priceRule = PriceRule::findOrFail($id);

        $request->validate([
            'products' => 'required|array',
        ]);

        $productsData = $request->products;

        foreach ($productsData as $productId => $productData) {
            if (isset($productData['selected']) && $productData['selected'] == '1') {
                $priceRule->products()->syncWithoutDetaching([
                    $productId => [
                        'discount' => $productData['discount'] ?? $priceRule->discount_value,
                        'discount_type' => $productData['discount_type'] ?? $priceRule->discount_type,
                    ]
                ]);
            } else {
                $priceRule->products()->detach($productId);
            }
        }

        return redirect()->route('admin.marketing.price-rules.index')
            ->with('success', 'Price rule products updated successfully.');
    }

    /**
     * Add products to the price rule.
     */
    public function addProducts(Request $request, $id)
    {
        $priceRule = PriceRule::findOrFail($id);

        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        foreach ($request->product_ids as $productId) {
            $priceRule->products()->syncWithoutDetaching([
                $productId => [
                    'discount' => $priceRule->discount_value,
                    'discount_type' => $priceRule->discount_type,
                ]
            ]);
        }

        return back()->with('success', 'Products added to price rule successfully.');
    }

    /**
     * Remove a product from the price rule.
     */
    public function removeProduct($id, $productId)
    {
        $priceRule = PriceRule::findOrFail($id);
        $priceRule->products()->detach($productId);

        return back()->with('success', 'Product removed from price rule.');
    }

    /**
     * Generate a unique slug from the name.
     */
    private function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = PriceRule::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            $query = PriceRule::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Get product list for AJAX selection.
     */
    public function getProducts(Request $request)
    {
        $search = $request->search ?? '';
        
        $products = Product::where('is_active', 1)
            ->where('is_approved', 1)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'sku', 'price']);

        return response()->json($products);
    }
}
