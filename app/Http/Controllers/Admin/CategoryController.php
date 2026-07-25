<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Helpers\ImageHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display categories list with statistics and tree view.
     */
    public function index(Request $request)
    {
        $viewMode = $request->get('view', 'tree');
        $search = $request->get('search');
        $status = $request->get('status');
        $showInMenu = $request->get('show_in_menu');
        $showInHomepage = $request->get('show_in_homepage');
        
        // Statistics (always show total counts regardless of filters)
        $stats = [
            'total' => Category::count(),
            'active' => Category::where('status', 'active')->count(),
            'inactive' => Category::where('status', 'inactive')->count(),
            'parents' => Category::whereNull('parent_id')->count(),
            'with_products' => Category::has('products')->count(),
        ];
        
        // Build base query for filtered results
        $query = Category::with(['parent', 'children']);
        
        // Search (only name and slug, not description to avoid unexpected matches)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Show in menu filter
        if ($showInMenu !== null && $showInMenu !== '') {
            $query->where('show_in_menu', $showInMenu === 'yes');
        }
        
        // Show in homepage filter
        if ($showInHomepage !== null && $showInHomepage !== '') {
            $query->where('show_in_homepage', $showInHomepage === 'yes');
        }
        
        if ($viewMode === 'tree') {
            if ($search) {
                // Search mode - show full tree path for matching categories
                $matchingIds = $query->pluck('id')->toArray();

                $allAncestorIds = [];
                if (!empty($matchingIds)) {
                    $allAncestorIds = $this->getAllAncestorIds($matchingIds);
                }

                $rootParentIds = [];
                if (!empty($matchingIds)) {
                    $allIdsToCheck = array_unique(array_merge($matchingIds, $allAncestorIds));
                    $rootParentIds = Category::whereIn('id', $allIdsToCheck)
                        ->whereNull('parent_id')
                        ->pluck('id')
                        ->toArray();
                }

                $categories = Category::with(['children' => function ($q) use ($showInMenu, $showInHomepage) {
                        $q->withCount('products');
                        if ($showInMenu !== null && $showInMenu !== '') $q->where('show_in_menu', $showInMenu === 'yes');
                        if ($showInHomepage !== null && $showInHomepage !== '') $q->where('show_in_homepage', $showInHomepage === 'yes');
                    }])
                    ->withCount('products')
                    ->whereNull('parent_id');

                if (!empty($rootParentIds)) {
                    $categories->whereIn('id', $rootParentIds);
                } elseif (!empty($matchingIds)) {
                    $categories->whereIn('id', $matchingIds);
                } else {
                    $categories->whereIn('id', [0]);
                }

                if ($showInMenu !== null && $showInMenu !== '') $categories->where('show_in_menu', $showInMenu === 'yes');
                if ($showInHomepage !== null && $showInHomepage !== '') $categories->where('show_in_homepage', $showInHomepage === 'yes');

                $categories = $categories->ordered()->get();
            } else {
                // Filter mode (status, show_in_menu, show_in_homepage)
                $hasFilter = $status || ($showInMenu !== null && $showInMenu !== '') || ($showInHomepage !== null && $showInHomepage !== '');

                if (!$hasFilter) {
                    // No filter - load full tree
                    $categories = Category::with('children')
                        ->withCount('products')
                        ->whereNull('parent_id')
                        ->ordered()
                        ->get();
                } else {
                    // Find all category IDs matching the filters
                    $filteredQuery = Category::query();
                    if ($status) $filteredQuery->where('status', $status);
                    if ($showInMenu !== null && $showInMenu !== '') $filteredQuery->where('show_in_menu', $showInMenu === 'yes');
                    if ($showInHomepage !== null && $showInHomepage !== '') $filteredQuery->where('show_in_homepage', $showInHomepage === 'yes');
                    $matchingIds = $filteredQuery->pluck('id')->toArray();

                    if (empty($matchingIds)) {
                        $categories = collect([]);
                    } else {
                        // Get all ancestor IDs to show full tree path
                        $allAncestorIds = $this->getAllAncestorIds($matchingIds);
                        $allCategoryIds = array_unique(array_merge($matchingIds, $allAncestorIds));

                        // Load all relevant categories (matching + ancestors)
                        $matchingCategories = Category::withCount('products')
                            ->whereIn('id', $allCategoryIds)
                            ->ordered()
                            ->get();

                        // Build tree but only include matching categories and their matching descendants
                        $categories = $this->buildFilteredTree($matchingCategories, $matchingIds);
                    }
                }
            }
        } else {
            // Flat view - all categories paginated
            $perPage = $request->per_page ?? 10;
            $categories = $query->withCount('products')
                ->ordered()
                ->paginate($perPage)
                ->appends($request->query());
        }
        
        // AJAX response
        if ($request->ajax || $request->ajax() || $request->wantsJson()) {
            $html = view('admin.categories.partials.category-rows', compact('categories', 'viewMode'))->render();
            
            $pagination = '';
            if ($viewMode !== 'tree' && method_exists($categories, 'hasPages') && $categories->hasPages()) {
                $pagination = '<div class="d-flex justify-content-center mt-3">' . $categories->links()->toHtml() . '</div>';
            }
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $pagination,
                'total' => $viewMode === 'tree' ? count($categories) : $categories->total()
            ]);
        }
        
        return view('admin.categories.index', compact('categories', 'stats', 'viewMode', 'search'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $categories = Category::getFlattenedTree();
        return view('admin.categories.create', compact('categories'));
    }

    /**
     * Store new category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'icon' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'show_in_menu' => 'boolean',
            'show_in_homepage' => 'boolean',
        ]);

        $data = $request->except(['image']);
        $data['slug'] = Str::slug($request->name);
        $data['show_in_menu'] = $request->has('show_in_menu');
        $data['show_in_homepage'] = $request->has('show_in_homepage');

        // Handle image upload using ImageHelper
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'categories',  // Directory
                    800,           // Max width (per Preference.md)
                    200,           // Thumbnail width (per Preference.md)
                    80             // Quality (per Preference.md)
                );
                
                $data['image'] = $imageResult['path'];
                $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        // Set sort order
        if (empty($data['sort_order'])) {
            $data['sort_order'] = Category::max('sort_order') + 1;
        }

        $category = Category::create($data);

        // Redirect based on save action
        if ($request->action === 'save_and_new') {
            return redirect()->route('admin.categories.create')
                ->with('success', 'Category created successfully. Create another one.');
        } elseif ($request->action === 'save_and_edit') {
            return redirect()->route('admin.categories.edit', $category)
                ->with('success', 'Category created successfully.');
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show category details.
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products' => function ($q) {
            $q->latest()->take(10);
        }]);
        
        $productsCount = $category->products()->count();
        $activeProductsCount = $category->products()->where('is_active', true)->count();
        
        return view('admin.categories.show', compact('category', 'productsCount', 'activeProductsCount'));
    }

    /**
     * Show edit form.
     */
    public function edit(Category $category)
    {
        $categories = Category::getFlattenedTree($category->id);
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    /**
     * Update category.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'icon' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'show_in_menu' => 'boolean',
            'show_in_homepage' => 'boolean',
        ]);

        $data = $request->except(['image']);
        $data['slug'] = Str::slug($request->name);
        $data['show_in_menu'] = $request->has('show_in_menu');
        $data['show_in_homepage'] = $request->has('show_in_homepage');

        // Handle image upload using ImageHelper
        if ($request->hasFile('image')) {
            // Delete old image and thumbnail
            if ($category->image) {
                ImageHelper::deleteImage($category->image, $category->thumbnail ?? null);
            }
            
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'categories',  // Directory
                    800,           // Max width (per Preference.md)
                    200,           // Thumbnail width (per Preference.md)
                    80             // Quality (per Preference.md)
                );
                
                $data['image'] = $imageResult['path'];
                $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        // Prevent setting parent to self
        if ($request->parent_id == $category->id) {
            $data['parent_id'] = null;
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete category.
     */
    public function destroy(Category $category)
    {
        // Check if category can be deleted
        if ($category->children()->count() > 0) {
            return back()->with('error', 'Cannot delete category with subcategories. Please delete or move subcategories first.');
        }
        
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with products. Please move or delete products first.');
        }

        // Delete image and thumbnail
        if ($category->image) {
            ImageHelper::deleteImage($category->image, $category->thumbnail ?? null);
        }
        
        $category->delete();
        
        return back()->with('success', 'Category deleted successfully.');
    }

    /**
     * Toggle category status (AJAX).
     */
    public function toggleStatus(Request $request, Category $category)
    {
        $category->update([
            'status' => $category->status === 'active' ? 'inactive' : 'active'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status' => $category->status,
            'badge' => $category->status_badge
        ]);
    }

    /**
     * Toggle show in menu (AJAX).
     */
    public function toggleMenu(Request $request, Category $category)
    {
        $category->update(['show_in_menu' => !$category->show_in_menu]);
        
        return response()->json([
            'success' => true,
            'message' => 'Menu visibility updated.',
            'show_in_menu' => $category->show_in_menu
        ]);
    }

    /**
     * Toggle show in homepage (AJAX).
     */
    public function toggleHomepage(Request $request, Category $category)
    {
        $category->update(['show_in_homepage' => !$category->show_in_homepage]);
        
        return response()->json([
            'success' => true,
            'message' => 'Homepage visibility updated.',
            'show_in_homepage' => $category->show_in_homepage
        ]);
    }

    /**
     * Reorder categories (AJAX).
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:categories,id',
        ]);

        foreach ($request->order as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Categories reordered successfully.'
        ]);
    }

    /**
     * Get category tree (AJAX).
     */
    public function tree()
    {
        $categories = Category::with('children')
            ->parents()
            ->ordered()
            ->get();
        
        return response()->json($categories);
    }

    /**
     * Bulk actions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        $action = $request->action;
        $ids = $request->ids;
        $count = count($ids);

        switch ($action) {
            case 'delete':
                // Check each category
                $cannotDelete = [];
                $categories = Category::whereIn('id', $ids)->get();
                
                foreach ($categories as $category) {
                    if ($category->children()->count() > 0 || $category->products()->count() > 0) {
                        $cannotDelete[] = $category->name;
                    } else {
                        // Delete image and thumbnail
                        if ($category->image) {
                            ImageHelper::deleteImage($category->image, $category->thumbnail ?? null);
                        }
                        $category->delete();
                    }
                }
                
                $deletedCount = $count - count($cannotDelete);
                $message = "{$deletedCount} category(s) deleted successfully.";
                
                if (count($cannotDelete) > 0) {
                    $message .= " Could not delete: " . implode(', ', $cannotDelete) . " (has children or products).";
                }
                break;

            case 'activate':
                Category::whereIn('id', $ids)->update(['status' => 'active']);
                $message = "{$count} category(s) activated successfully.";
                break;

            case 'deactivate':
                Category::whereIn('id', $ids)->update(['status' => 'inactive']);
                $message = "{$count} category(s) deactivated successfully.";
                break;

            case 'show_in_menu':
                Category::whereIn('id', $ids)->update(['show_in_menu' => true]);
                $message = "{$count} category(s) will show in menu.";
                break;

            case 'hide_from_menu':
                Category::whereIn('id', $ids)->update(['show_in_menu' => false]);
                $message = "{$count} category(s) hidden from menu.";
                break;

            case 'show_in_homepage':
                Category::whereIn('id', $ids)->update(['show_in_homepage' => true]);
                $message = "{$count} category(s) will show on homepage.";
                break;

            case 'hide_from_homepage':
                Category::whereIn('id', $ids)->update(['show_in_homepage' => false]);
                $message = "{$count} category(s) hidden from homepage.";
                break;

            default:
                return back()->with('error', 'Invalid action selected.');
        }

        return back()->with('success', $message);
    }

    /**
     * Export categories to CSV.
     */
    public function export(Request $request)
    {
        $categories = Category::with('parent')->ordered()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="categories-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($categories) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Slug', 'Parent', 'Status', 'Products Count', 'Sort Order', 'In Menu', 'On Homepage', 'Created At']);

            foreach ($categories as $category) {
                fputcsv($file, [
                    $category->id,
                    $category->name,
                    $category->slug,
                    $category->parent->name ?? 'None',
                    $category->status,
                    $category->products_count,
                    $category->sort_order,
                    $category->show_in_menu ? 'Yes' : 'No',
                    $category->show_in_homepage ? 'Yes' : 'No',
                    $category->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get products for a category (AJAX).
     */
    public function getProducts(Request $request, Category $category)
    {
        $products = $category->products()
            ->with('category')
            ->latest()
            ->paginate(20);
        
        return response()->json([
            'products' => $products,
            'total' => $products->total(),
        ]);
    }

    /**
     * Move products to another category.
     */
    public function moveProducts(Request $request, Category $category)
    {
        $request->validate([
            'target_category_id' => 'required|exists:categories,id|different:' . $category->id,
        ]);

        $targetCategory = Category::findOrFail($request->target_category_id);
        
        $count = $category->products()->update(['category_id' => $targetCategory->id]);
        
        return response()->json([
            'success' => true,
            'message' => "{$count} product(s) moved to {$targetCategory->name}.",
        ]);
    }

    /**
     * Get categories for select dropdown (AJAX).
     */
    public function getSelectOptions(Request $request)
    {
        $excludeId = $request->get('exclude');
        $categories = Category::getFlattenedTree($excludeId);
        
        return response()->json($categories);
    }
    
    /**
     * Get all ancestor IDs for given category IDs (recursive).
     * This helps show the full tree path when searching for subcategories.
     *
     * @param array $categoryIds
     * @return array
     */
    private function getAllAncestorIds(array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }
        
        // Get immediate parents of these categories
        $parentIds = Category::whereIn('id', $categoryIds)
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->unique()
            ->toArray();
        
        if (empty($parentIds)) {
            return [];
        }
        
        // Recursively get all ancestors
        $ancestorIds = $this->getAllAncestorIds($parentIds);
        
        return array_unique(array_merge($parentIds, $ancestorIds));
    }

    /**
     * Recursively filter a category tree to only keep categories
     * that match the given IDs (and their ancestor paths).
     */
    private function filterCategoryTree($categories, array $matchingIds)
    {
        $result = collect();

        foreach ($categories as $category) {
            // Recursively filter children first
            if ($category->children->count() > 0) {
                $category->setRelation('children', $this->filterCategoryTree($category->children, $matchingIds));
            }

            // Keep this category if it matches, or if it has matching descendants
            if (in_array($category->id, $matchingIds) || $category->children->count() > 0) {
                $result->push($category);
            }
        }

        return $result;
    }

    /**
     * Build a filtered tree from matching categories and their ancestors.
     * Only includes categories that either:
     * - Match the filter directly (in $matchingIds)
     * - Have descendants that match the filter
     */
    private function buildFilteredTree($allCategories, array $matchingIds)
    {
        // Group categories by parent_id
        $byParent = $allCategories->groupBy('parent_id');
        $byId = $allCategories->keyBy('id');

        // Recursively build children, only including those with matching descendants
        $buildChildren = function ($parentId) use (&$buildChildren, $byParent, $byId, $matchingIds) {
            $children = $byParent->get($parentId, collect());

            return $children->filter(function ($child) use ($byId, &$buildChildren, $matchingIds) {
                // Check if this child matches
                $childMatches = in_array($child->id, $matchingIds);

                // Get children that have matching descendants
                $matchingDescendants = $buildChildren($child->id);

                // Keep if this child matches OR has matching descendants
                if ($childMatches || $matchingDescendants->count() > 0) {
                    $child->setRelation('children', $matchingDescendants);
                    return true;
                }
                return false;
            })->values();
        };

        // Start with root categories (parent_id = null)
        $rootCategories = $byParent->get(null, collect());

        return $rootCategories->filter(function ($root) use (&$buildChildren, $matchingIds) {
            // Root matches if it matches OR has matching descendants
            $rootMatches = in_array($root->id, $matchingIds);
            $matchingChildren = $buildChildren($root->id);

            if ($rootMatches || $matchingChildren->count() > 0) {
                $root->setRelation('children', $matchingChildren);
                return true;
            }
            return false;
        })->values();
    }
}
