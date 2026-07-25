<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\AffiliateCategory;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateProductController extends Controller
{
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
                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
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
            'slug' => 'nullable|string|max:255|unique:affiliate_products,slug',
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
                $validated['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        AffiliateProduct::create($validated);

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
            'slug' => 'nullable|string|max:255|unique:affiliate_products,slug,' . $id,
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
                $validated['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product->update($validated);

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

        $product->delete();

        return redirect()->back()
            ->with('success', 'Affiliate product deleted successfully.');
    }
}
