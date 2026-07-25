<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SortsLists;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AffiliateCategory;
use App\Models\AffiliateProduct;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateCategoryController extends Controller
{
    use SortsLists;

    /**
     * A slug the table will actually accept.
     *
     * The old code ran Str::slug() and inserted it; a second category with the same name hit the
     * UNIQUE index and answered 500, because the rule only validated a slug the user typed.
     */
    private function uniqueSlug(string $name, ?string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: 'category';
        $candidate = $base;
        $n = 2;

        while (AffiliateCategory::where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base . '-' . $n++;
        }

        return $candidate;
    }
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
            $query->orderBy($sort, $this->sortDirection($request));
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
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'status' => 'nullable|in:active,inactive',
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
            }
        }

        $validated['slug'] = $this->uniqueSlug($validated['name'], $validated['slug'] ?? null);

        // Handle status checkbox: the form posts nothing when unticked, so presence is the state.
        $validated['status'] = $request->has('status') ? 'active' : 'inactive';

        $category = AffiliateCategory::create($validated);

        ActivityLog::adminLog('Affiliate category created', $category, auth()->user(), [
            'slug' => $category->slug,
            'commission_rate' => (float) $category->commission_rate,
            'status' => $category->status,
        ]);

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
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'status' => 'nullable|in:active,inactive',
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
            }
        }

        $validated['slug'] = $this->uniqueSlug($validated['name'], $validated['slug'] ?? null, $category->id);

        // Handle status checkbox: the form posts nothing when unticked, so presence is the state.
        $validated['status'] = $request->has('status') ? 'active' : 'inactive';

        $from = ['status' => $category->status, 'commission_rate' => (float) $category->commission_rate];
        $category->update($validated);

        ActivityLog::adminLog('Affiliate category updated', $category, auth()->user(), [
            'from' => $from,
            'to' => ['status' => $category->status, 'commission_rate' => (float) $category->commission_rate],
        ]);

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

        $name = $category->name;
        $category->delete();

        ActivityLog::adminLog('Affiliate category deleted', null, auth()->user(), [
            'category_id' => $id,
            'name' => $name,
        ]);

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
            'ids' => 'required',
        ]);

        // The page posts JSON.stringify([...]); accept a real array too.
        $raw = $request->input('ids');
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : explode(',', $raw);
        }
        $ids = is_array($raw) ? array_values(array_filter(array_map('intval', $raw))) : [];
        $action = $request->action;

        ActivityLog::adminLog('Affiliate categories bulk action', null, auth()->user(), [
            'action' => $action,
            'ids' => $ids,
        ]);
        
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
