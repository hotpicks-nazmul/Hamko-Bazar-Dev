<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;

class BlogCategoryController extends Controller
{
    /**
     * Display blog categories list with statistics.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        
        // Statistics
        $stats = [
            'total' => BlogCategory::count(),
            'active' => BlogCategory::where('status', 'active')->count(),
            'inactive' => BlogCategory::where('status', 'inactive')->count(),
        ];
        
        // Build query
        $query = BlogCategory::query();
        
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Get categories ordered
        $categories = $query->withCount('blogs')
            ->ordered()
            ->paginate(25)
            ->appends($request->query());
        
        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.blog-categories.partials.table-rows', compact('categories'))->render();
            
            $pagination = '';
            if (method_exists($categories, 'hasPages') && $categories->hasPages()) {
                $pagination = '<div class="d-flex justify-content-center mt-3">' . $categories->links()->toHtml() . '</div>';
            }
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $pagination,
                'total' => $categories->total()
            ]);
        }
        
        return view('admin.blog-categories.index', compact('categories', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.blog-categories.create');
    }

    /**
     * Store new blog category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->except(['image']);
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->name);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle image upload with WebP conversion
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'blog-categories', // directory
                    800,               // max width
                    200,               // thumbnail width
                    80                 // quality
                );
                $data['image'] = $imageResult['path'];
            }
        }

        // Set sort order
        if (empty($data['sort_order'])) {
            $data['sort_order'] = BlogCategory::max('sort_order') + 1;
        }

        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(BlogCategory $blogCategory)
    {
        return view('admin.blog-categories.edit', compact('blogCategory'));
    }

    /**
     * Update blog category.
     */
    public function update(Request $request, BlogCategory $blogCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $blogCategory->id,
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug,' . $blogCategory->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->except(['image', 'remove_image']);
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->name);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Handle image removal
        if ($request->has('remove_image') && $request->remove_image) {
            if ($blogCategory->image) {
                ImageHelper::deleteImage($blogCategory->image);
            }
            $data['image'] = null;
        }

        // Handle image upload with WebP conversion
        if ($request->hasFile('image')) {
            // Delete old image
            if ($blogCategory->image) {
                ImageHelper::deleteImage($blogCategory->image);
            }
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'blog-categories', // directory
                    800,               // max width
                    200,               // thumbnail width
                    80                 // quality
                );
                $data['image'] = $imageResult['path'];
            }
        }

        $blogCategory->update($data);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category updated successfully.');
    }

    /**
     * Delete blog category.
     */
    public function destroy(BlogCategory $blogCategory)
    {
        // Check if category has blogs
        if ($blogCategory->blogs()->count() > 0) {
            $message = 'Cannot delete category with blog posts. Please move or delete blog posts first.';
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            
            return back()->with('error', $message);
        }

        // Delete image
        if ($blogCategory->image) {
            ImageHelper::deleteImage($blogCategory->image);
        }
        
        $blogCategory->delete();
        
        $message = 'Blog category deleted successfully.';
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        
        return back()->with('success', $message);
    }

    /**
     * Toggle category status (AJAX).
     */
    public function toggleStatus(Request $request, BlogCategory $blogCategory)
    {
        $newStatus = $blogCategory->status === 'active' ? 'inactive' : 'active';
        $blogCategory->update(['status' => $newStatus]);
        
        // Generate the new badge HTML
        $badge = $newStatus === 'active' 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status' => $newStatus,
            'badge' => $badge
        ]);
    }

    /**
     * Bulk actions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'exists:blog_categories,id',
        ]);

        $action = $request->action;
        $ids = $request->ids;
        $count = count($ids);

        switch ($action) {
            case 'delete':
                $categories = BlogCategory::whereIn('id', $ids)->get();
                $cannotDelete = [];
                
                foreach ($categories as $category) {
                    if ($category->blogs()->count() > 0) {
                        $cannotDelete[] = $category->name;
                    } else {
                        // Delete image
                        if ($category->image) {
                            ImageHelper::deleteImage($category->image);
                        }
                        $category->delete();
                    }
                }
                
                $deletedCount = $count - count($cannotDelete);
                $message = "{$deletedCount} category(s) deleted successfully.";
                
                if (count($cannotDelete) > 0) {
                    $message .= " Could not delete: " . implode(', ', $cannotDelete) . " (has blog posts).";
                }
                break;

            case 'activate':
                BlogCategory::whereIn('id', $ids)->update(['status' => 'active']);
                $message = "{$count} category(s) activated successfully.";
                break;

            case 'deactivate':
                BlogCategory::whereIn('id', $ids)->update(['status' => 'inactive']);
                $message = "{$count} category(s) deactivated successfully.";
                break;

            default:
                return back()->with('error', 'Invalid action selected.');
        }

        return back()->with('success', $message);
    }
}
