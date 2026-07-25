<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductBundle;
use App\Models\Product;
use App\Models\ProductBundleItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;

class ProductBundleController extends Controller
{
    /**
     * Display bundles list with statistics.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $featured = $request->get('featured');
        $sort = $request->get('sort', 'sort_order');
        $direction = $request->get('direction', 'asc');
        $perPage = $request->get('per_page', 25);

        // Statistics
        $stats = [
            'total' => ProductBundle::count(),
            'active' => ProductBundle::where('is_active', true)->count(),
            'inactive' => ProductBundle::where('is_active', false)->count(),
            'featured' => ProductBundle::where('is_featured', true)->count(),
            'expired' => ProductBundle::where('expires_at', '<', now())->count(),
        ];

        // Build query
        $query = ProductBundle::withCount('products');

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status !== null && $status !== '') {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'expired') {
                $query->where('expires_at', '<', now());
            } elseif ($status === 'scheduled') {
                $query->where('starts_at', '>', now());
            }
        }

        // Featured filter
        if ($featured !== null && $featured !== '') {
            $query->where('is_featured', $featured === 'yes');
        }

        // Sorting
        $validSorts = ['name', 'sort_order', 'created_at', 'products_count', 'bundle_price'];
        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('sort_order')->orderBy('name');
        }

        $bundles = $query->paginate($perPage)->appends($request->query());

        // AJAX response
        if ($request->ajax || $request->ajax() || $request->wantsJson()) {
            $html = view('admin.product-bundles.partials.table-rows', compact('bundles'))->render();

            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $bundles->links()->toHtml(),
                'total' => $bundles->total()
            ]);
        }

        return view('admin.product-bundles.index', compact('bundles', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $products = Product::active()
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'sale_price', 'featured_image']);

        return view('admin.product-bundles.create', compact('products'));
    }

    /**
     * Store new bundle.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_bundles,slug',
            'description' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'bundle_price' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'max_purchases' => 'nullable|integer|min:1',
            'max_purchases_per_user' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.custom_price' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only([
            'name', 'slug', 'description', 'bundle_price',
            'discount_type', 'discount_value', 'starts_at', 'expires_at',
            'max_purchases', 'max_purchases_per_user',
            'is_active', 'is_featured', 'sort_order',
            'meta_title', 'meta_description'
        ]);

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            
            // Ensure unique slug
            $count = ProductBundle::where('slug', 'like', $data['slug'] . '%')
                ->withTrashed()
                ->count();
            
            if ($count > 0) {
                $data['slug'] .= '-' . ($count + 1);
            }
        }

        // Handle checkbox values
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['bundle_price'] = $data['bundle_price'] ?? 0;
        $data['discount_value'] = $data['discount_value'] ?? 0;

        // Upload featured image with WebP conversion
        if ($request->hasFile('featured_image')) {
            if (ImageHelper::isValidImage($request->file('featured_image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('featured_image'),
                    'bundles',      // directory
                    1920,           // max width
                    300,            // thumbnail width
                    85              // quality
                );
                $data['featured_image'] = $imageResult['path'];
            }
        }

        $bundle = ProductBundle::create($data);

        // Attach products
        $this->syncProducts($bundle, $request->products);

        return redirect()->route('admin.product-bundles.index')
            ->with('success', 'Product bundle created successfully.');
    }

    /**
     * Show bundle details.
     */
    public function show(ProductBundle $productBundle)
    {
        $productBundle->load(['products', 'items.product']);
        $productBundle->loadCount('products');

        return view('admin.product-bundles.show', compact('productBundle'));
    }

    /**
     * Show edit form.
     */
    public function edit(ProductBundle $productBundle)
    {
        $productBundle->load(['items.product']);

        $products = Product::active()
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'sale_price', 'featured_image']);

        return view('admin.product-bundles.edit', compact('productBundle', 'products'));
    }

    /**
     * Update bundle.
     */
    public function update(Request $request, ProductBundle $productBundle)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_bundles,slug,' . $productBundle->id,
            'description' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'bundle_price' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'max_purchases' => 'nullable|integer|min:1',
            'max_purchases_per_user' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.custom_price' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only([
            'name', 'slug', 'description', 'bundle_price',
            'discount_type', 'discount_value', 'starts_at', 'expires_at',
            'max_purchases', 'max_purchases_per_user',
            'is_active', 'is_featured', 'sort_order',
            'meta_title', 'meta_description'
        ]);

        // Handle checkbox values
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['bundle_price'] = $data['bundle_price'] ?? 0;
        $data['discount_value'] = $data['discount_value'] ?? 0;

        // Upload featured image with WebP conversion
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($productBundle->featured_image) {
                ImageHelper::deleteImage($productBundle->featured_image);
            }
            if (ImageHelper::isValidImage($request->file('featured_image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('featured_image'),
                    'bundles',      // directory
                    1920,           // max width
                    300,            // thumbnail width
                    85              // quality
                );
                $data['featured_image'] = $imageResult['path'];
            }
        }

        $productBundle->update($data);

        // Sync products
        $this->syncProducts($productBundle, $request->products);

        return redirect()->route('admin.product-bundles.index')
            ->with('success', 'Product bundle updated successfully.');
    }

    /**
     * Delete bundle.
     */
    public function destroy(ProductBundle $productBundle)
    {
        // Delete featured image
        if ($productBundle->featured_image) {
            ImageHelper::deleteImage($productBundle->featured_image);
        }

        $productBundle->delete();

        return redirect()->route('admin.product-bundles.index')
            ->with('success', 'Product bundle deleted successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(ProductBundle $productBundle)
    {
        $productBundle->update([
            'is_active' => !$productBundle->is_active
        ]);

        return response()->json([
            'success' => true,
            'status' => $productBundle->is_active ? 'Active' : 'Inactive',
            'color' => $productBundle->is_active ? 'success' : 'secondary'
        ]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(ProductBundle $productBundle)
    {
        $productBundle->update([
            'is_featured' => !$productBundle->is_featured
        ]);

        return response()->json([
            'success' => true,
            'status' => $productBundle->is_featured ? 'Featured' : 'Not Featured',
            'color' => $productBundle->is_featured ? 'warning' : 'secondary'
        ]);
    }

    /**
     * Bulk actions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:product_bundles,id'
        ]);

        $bundles = ProductBundle::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'activate':
                $bundles->update(['is_active' => true]);
                $message = 'Selected bundles activated successfully.';
                break;
            case 'deactivate':
                $bundles->update(['is_active' => false]);
                $message = 'Selected bundles deactivated successfully.';
                break;
            case 'feature':
                $bundles->update(['is_featured' => true]);
                $message = 'Selected bundles featured successfully.';
                break;
            case 'unfeature':
                $bundles->update(['is_featured' => false]);
                $message = 'Selected bundles unfeatured successfully.';
                break;
            case 'delete':
                foreach ($bundles->get() as $bundle) {
                    if ($bundle->featured_image) {
                        ImageHelper::deleteImage($bundle->featured_image);
                    }
                }
                $bundles->delete();
                $message = 'Selected bundles deleted successfully.';
                break;
        }

        return redirect()->route('admin.product-bundles.index')
            ->with('success', $message);
    }

    /**
     * Export bundles.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        $bundles = ProductBundle::with('products')
            ->orderBy('sort_order')
            ->get();

        if ($format === 'json') {
            return response()->json($bundles);
        }

        // CSV export
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product-bundles.csv"',
        ];

        $callback = function () use ($bundles) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Slug', 'Price', 'Discount', 'Status', 'Products Count', 'Created At']);

            foreach ($bundles as $bundle) {
                fputcsv($file, [
                    $bundle->id,
                    $bundle->name,
                    $bundle->slug,
                    $bundle->final_price,
                    $bundle->discount_type === 'percentage' 
                        ? $bundle->discount_value . '%' 
                        : '$' . $bundle->discount_value,
                    $bundle->status_label,
                    $bundle->products_count,
                    $bundle->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get products for AJAX search.
     */
    public function getProducts(Request $request)
    {
        $search = $request->get('search');
        $excludeIds = $request->get('exclude', []);

        $products = Product::active()
            ->whereNotIn('id', $excludeIds)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'price', 'sale_price', 'featured_image', 'sku']);

        return response()->json($products);
    }

    /**
     * Sync products to bundle.
     */
    protected function syncProducts(ProductBundle $bundle, array $products)
    {
        // Delete existing items
        $bundle->items()->delete();

        // Create new items
        $sortOrder = 0;
        foreach ($products as $productData) {
            ProductBundleItem::create([
                'product_bundle_id' => $bundle->id,
                'product_id' => $productData['id'],
                'quantity' => $productData['quantity'] ?? 1,
                'custom_price' => $productData['custom_price'] ?? null,
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}
