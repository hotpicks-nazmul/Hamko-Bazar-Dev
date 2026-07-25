<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalCategory;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DigitalCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:digital-categories');
    }
    /**
     * Display digital categories list.
     */
    public function index(Request $request)
    {
        $query = DigitalCategory::with(['parent', 'children']);

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by parent
        if ($request->parent !== null && $request->parent !== '') {
            if ($request->parent === 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent);
            }
        }

        // Sorting
        $sort = $request->sort ?? 'order';
        $direction = $request->direction ?? 'asc';
        
        if ($sort === 'order') {
            $query->orderBy('order', $direction);
        } elseif ($sort === 'name') {
            $query->orderBy('name', $direction);
        } elseif ($sort === 'created_at') {
            $query->orderBy('created_at', $direction);
        } else {
            $query->ordered();
        }

        // Pagination
        $perPage = $request->per_page ?? 25;
        $categories = $query->paginate($perPage)->appends($request->query());
        
        // Get all categories for parent filter dropdown
        $allCategories = DigitalCategory::ordered()->get();

        // Statistics
        $stats = [
            'total' => DigitalCategory::count(),
            'active' => DigitalCategory::active()->count(),
            'inactive' => DigitalCategory::where('status', 'inactive')->count(),
            'root' => DigitalCategory::root()->count(),
        ];

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.digital-categories.partials.table-rows', compact('categories'))->render(),
                'pagination' => $categories->links()->toHtml(),
                'pagination_info' => [
                    'first_item' => $categories->firstItem(),
                    'last_item' => $categories->lastItem(),
                    'total' => $categories->total(),
                    'has_pages' => $categories->hasPages(),
                ],
                'stats' => $stats
            ]);
        }

        return view('admin.digital-categories.index', compact('categories', 'allCategories', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $categories = DigitalCategory::getFlattenedTree();
        return view('admin.digital-categories.create', compact('categories'));
    }

    /**
     * Store new digital category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:digital_categories,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'parent_id' => 'nullable|exists:digital_categories,id',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->except(['image', '_token']);
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->name);
            
            // Ensure unique slug
            $count = DigitalCategory::where('slug', 'like', $data['slug'] . '%')->count();
            if ($count > 0) {
                $data['slug'] .= '-' . ($count + 1);
            }
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                if (ImageHelper::isValidImage($request->file('image'))) {
                    $imageResult = ImageHelper::processImage(
                        $request->file('image'),
                        'digital-categories',
                        1920,
                        300,
                        85
                    );
                    $data['image'] = ltrim($imageResult['path'], '/');
                }
            } catch (\Exception $e) {
                Log::error('Digital category image upload failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'Failed to upload image. Please try again.');
            }
        }

        $category = DigitalCategory::create($data);

        return redirect()
            ->route('admin.digital-categories.index')
            ->with('success', 'Digital category created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(DigitalCategory $digitalCategory)
    {
        $categories = DigitalCategory::getFlattenedTree();
        
        // Remove current category and its descendants from parent options
        $descendantIds = $digitalCategory->getDescendantIds();
        $categories = collect($categories)->except($descendantIds);

        return view('admin.digital-categories.edit', compact('digitalCategory', 'categories'));
    }

    /**
     * Update digital category.
     */
    public function update(Request $request, DigitalCategory $digitalCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:digital_categories,slug,' . $digitalCategory->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'parent_id' => 'nullable|exists:digital_categories,id',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->except(['image', '_token', '_method']);

        // Prevent setting parent to self or descendant
        $descendantIds = $digitalCategory->getDescendantIds();
        if (in_array($data['parent_id'], $descendantIds)) {
            return back()->withErrors(['parent_id' => 'Cannot set parent to self or a descendant.']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                if (ImageHelper::isValidImage($request->file('image'))) {
                    // Delete old image
                    if ($digitalCategory->image) {
                        ImageHelper::deleteImage($digitalCategory->image);
                    }
                    $imageResult = ImageHelper::processImage(
                        $request->file('image'),
                        'digital-categories',
                        1920,
                        300,
                        85
                    );
                    $data['image'] = ltrim($imageResult['path'], '/');
                }
            } catch (\Exception $e) {
                Log::error('Digital category image update failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'Failed to update image. Please try again.');
            }
        }

        $digitalCategory->update($data);

        return redirect()
            ->route('admin.digital-categories.index')
            ->with('success', 'Digital category updated successfully.');
    }

    /**
     * Delete digital category.
     */
    public function destroy(DigitalCategory $digitalCategory)
    {
        // Check if category has products
        if ($digitalCategory->products()->exists()) {
            return back()->with('error', 'Cannot delete category with products. Move or delete products first.');
        }

        // Move children to parent or root
        foreach ($digitalCategory->children as $child) {
            $child->update(['parent_id' => $digitalCategory->parent_id]);
        }

        // Delete image
        if ($digitalCategory->image) {
            ImageHelper::deleteImage($digitalCategory->image, $digitalCategory->thumbnail ?? null);
        }

        $digitalCategory->delete();

        return back()->with('success', 'Digital category deleted successfully.');
    }

    /**
     * Toggle status.
     */
    public function toggleStatus(DigitalCategory $digitalCategory)
    {
        try {
            $digitalCategory->status = $digitalCategory->status === 'active' ? 'inactive' : 'active';
            $digitalCategory->save();

            return response()->json([
                'success' => true,
                'status' => $digitalCategory->status,
                'message' => 'Status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete category image.
     */
    public function deleteImage(Request $request, DigitalCategory $digitalCategory)
    {
        if ($digitalCategory->image) {
            ImageHelper::deleteImage($digitalCategory->image);
            $digitalCategory->update([
                'image' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.'
        ]);
    }

    /**
     * Update order.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:digital_categories,id',
            'orders.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->orders as $item) {
            DigitalCategory::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.'
        ]);
    }

    /**
     * Get categories as JSON for API/AJAX.
     */
    public function getCategories(Request $request)
    {
        $query = DigitalCategory::active();

        if ($request->parent_id) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->root();
        }

        $categories = $query->ordered()->get(['id', 'name', 'icon', 'image']);

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }
}
