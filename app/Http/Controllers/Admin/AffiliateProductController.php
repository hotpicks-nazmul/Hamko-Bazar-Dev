<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AffiliateProduct;
use App\Models\AffiliateCategory;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateProductController extends Controller
{
    /**
     * A slug the table will accept.
     *
     * A duplicate product name generated the same slug and hit the UNIQUE index: the create form
     * answered 500 instead of a validation message.
     */
    private function uniqueSlug(string $name, ?string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: 'product';
        $candidate = $base;
        $n = 2;

        while (AffiliateProduct::where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base . '-' . $n++;
        }

        return $candidate;
    }
    /**
     * Display list of affiliate products
     * 
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $affiliateCategories = AffiliateCategory::where('status', 'active')->get();
        
        $products = AffiliateProduct::with('category')
            ->when($request->search, function($query) use ($request) {
                $search = $request->search;
                // Grouped, or the later `and status = …` binds to the last OR term only and the
                // status filter stops filtering as soon as somebody searches.
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->category, function($query) use ($request) {
                $query->where('category_id', $request->category);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Statistics for stat cards
        $stats = [
            'total' => AffiliateProduct::count(),
            'active' => AffiliateProduct::where('status', 'active')->count(),
            'inactive' => AffiliateProduct::where('status', 'inactive')->count(),
            'avg_price' => AffiliateProduct::avg('price') ?? 0,
        ];
        
        // AJAX response for live search
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.affiliate.products.partials.table-rows', compact('products'))->render(),
            ]);
        }
        
        return view('admin.affiliate.products.index', compact('products', 'stats', 'affiliateCategories'));
    }

    /**
     * Show form to create new affiliate product
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = AffiliateCategory::where('status', 'active')->get();
        
        return view('admin.affiliate.products.create', compact('categories'));
    }

    /**
     * Store new affiliate product
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:affiliate_categories,id',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'price' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'external_url' => 'required|url',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle image upload using ImageHelper
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'affiliate-products',
                    1920,
                    300,
                    85
                );
                $validated['image'] = $imageResult['path'];
            }
        }

        $validated['slug'] = $this->uniqueSlug($validated['name'], $validated['slug'] ?? null);

        $product = AffiliateProduct::create($validated);

        ActivityLog::adminLog('Affiliate product created', $product, auth()->user(), [
            'slug' => $product->slug,
            'price' => (float) $product->price,
            'commission_rate' => (float) $product->commission_rate,
            'status' => $product->status,
        ]);

        return redirect()->route('admin.affiliate.products.index')
            ->with('success', 'Affiliate product created successfully.');
    }

    /**
     * Display affiliate product details
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $product = AffiliateProduct::with('category')->findOrFail($id);
        
        return view('admin.affiliate.products.show', compact('product'));
    }

    /**
     * Show form to edit affiliate product
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $product = AffiliateProduct::findOrFail($id);
        $categories = AffiliateCategory::where('status', 'active')->get();
        
        return view('admin.affiliate.products.edit', compact('product', 'categories'));
    }

    /**
     * Update affiliate product
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $product = AffiliateProduct::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:affiliate_categories,id',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'price' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'external_url' => 'required|url',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle image upload using ImageHelper
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                ImageHelper::deleteImage($product->image, $product->thumbnail ?? null);
            }
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'affiliate-products',
                    1920,
                    300,
                    85
                );
                $validated['image'] = $imageResult['path'];
            }
        }

        $validated['slug'] = $this->uniqueSlug($validated['name'], $validated['slug'] ?? null, $product->id);

        $from = ['price' => (float) $product->price, 'commission_rate' => (float) $product->commission_rate, 'status' => $product->status];
        $product->update($validated);

        ActivityLog::adminLog('Affiliate product updated', $product, auth()->user(), [
            'from' => $from,
            'to' => ['price' => (float) $product->price, 'commission_rate' => (float) $product->commission_rate, 'status' => $product->status],
        ]);

        return redirect()->route('admin.affiliate.products.index')
            ->with('success', 'Affiliate product updated successfully.');
    }

    /**
     * Delete affiliate product
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $product = AffiliateProduct::findOrFail($id);

        // Delete image using ImageHelper
        if ($product->image) {
            ImageHelper::deleteImage($product->image, $product->thumbnail ?? null);
        }

        $name = $product->name;
        $product->delete();

        ActivityLog::adminLog('Affiliate product deleted', null, auth()->user(), [
            'product_id' => $id,
            'name' => $name,
        ]);

        return redirect()->back()
            ->with('success', 'Affiliate product deleted successfully.');
    }
}
