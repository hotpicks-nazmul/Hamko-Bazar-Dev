<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCategory;
use App\Models\AffiliateProduct;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateCategoryController extends Controller
{
    /**
     * Display list of affiliate categories
     * 
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = AffiliateCategory::withCount('products');
        
        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $allowedSorts = ['name', 'commission_rate', 'created_at'];
        
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $categories = $query->paginate(15);
        
        // Statistics for stat cards
        $stats = [
            'total' => AffiliateCategory::count(),
            'active' => AffiliateCategory::where('status', 'active')->count(),
            'inactive' => AffiliateCategory::where('status', 'inactive')->count(),
            'total_products' => AffiliateProduct::count(),
            'avg_commission' => AffiliateCategory::avg('commission_rate') ?? 0,
        ];
        
        // AJAX response for live search
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.affiliate.categories.partials.category-rows', compact('categories'))->render(),
            ]);
        }
        
        return view('admin.affiliate.categories.index', compact('categories', 'stats'));
    }

    /**
     * Show form to create new affiliate category
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.affiliate.categories.create');
    }

    /**
     * Store new affiliate category
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:affiliate_categories,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle image upload using ImageHelper
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'affiliate-categories',
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

        // Handle status checkbox
        $validated['status'] = $request->has('status') ? 'active' : 'inactive';

        AffiliateCategory::create($validated);

        return redirect()->route('admin.affiliate.categories.index')
            ->with('success', 'Affiliate category created successfully.');
    }

    /**
     * Show form to edit affiliate category
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $category = AffiliateCategory::findOrFail($id);
        
        return view('admin.affiliate.categories.edit', compact('category'));
    }

    /**
     * Update affiliate category
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $category = AffiliateCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:affiliate_categories,slug,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle image upload using ImageHelper
        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                ImageHelper::deleteImage($category->image, $category->thumbnail ?? null);
            }
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'affiliate-categories',
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

        // Handle status checkbox
        $validated['status'] = $request->has('status') ? 'active' : 'inactive';

        $category->update($validated);

        return redirect()->route('admin.affiliate.categories.index')
            ->with('success', 'Affiliate category updated successfully.');
    }

    /**
     * Delete affiliate category
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $category = AffiliateCategory::findOrFail($id);
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category. It has associated products.');
        }

        // Delete image using ImageHelper
        if ($category->image) {
            ImageHelper::deleteImage($category->image, $category->thumbnail ?? null);
        }

        $category->delete();

        return redirect()->back()
            ->with('success', 'Affiliate category deleted successfully.');
    }

    /**
     * Bulk action for categories
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|json',
        ]);

        $ids = json_decode($request->ids, true);
        $action = $request->action;
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No categories selected.');
        }

        $categories = AffiliateCategory::whereIn('id', $ids)->get();

        switch ($action) {
            case 'activate':
                $categories->each->update(['status' => 'active']);
                return redirect()->back()->with('success', 'Selected categories activated.');
                
            case 'deactivate':
                $categories->each->update(['status' => 'inactive']);
                return redirect()->back()->with('success', 'Selected categories deactivated.');
                
            case 'delete':
                // Check for categories with products
                $categoriesWithProducts = $categories->filter(function($cat) {
                    return $cat->products()->count() > 0;
                });
                
                if ($categoriesWithProducts->isNotEmpty()) {
                    $names = $categoriesWithProducts->pluck('name')->join(', ');
                    return redirect()->back()->with('error', "Cannot delete these categories as they have products: {$names}");
                }
                
                // Delete images and categories
                $categories->each(function($category) {
                    if ($category->image) {
                        ImageHelper::deleteImage($category->image, $category->thumbnail ?? null);
                    }
                    $category->delete();
                });
                
                return redirect()->back()->with('success', 'Selected categories deleted.');
        }

        return redirect()->back()->with('error', 'Invalid action.');
    }
}
