<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogTag;
use Illuminate\Support\Str;

class BlogTagController extends Controller
{
    /**
     * Display blog tags list with statistics.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        
        // Statistics
        $stats = [
            'total' => BlogTag::count(),
            'active' => BlogTag::where('status', 'active')->count(),
            'inactive' => BlogTag::where('status', 'inactive')->count(),
        ];
        
        // Build query
        $query = BlogTag::query();
        
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
        
        // Get tags ordered
        $tags = $query->withCount('blogs')
            ->ordered()
            ->paginate(25)
            ->appends($request->query());
        
        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.blog-tags.partials.table-rows', compact('tags'))->render();
            
            $pagination = '';
            if (method_exists($tags, 'hasPages') && $tags->hasPages()) {
                $pagination = '<div class="d-flex justify-content-center mt-3">' . $tags->links()->toHtml() . '</div>';
            }
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $pagination,
                'total' => $tags->total()
            ]);
        }
        
        return view('admin.blog-tags.index', compact('tags', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.blog-tags.create');
    }

    /**
     * Store new blog tag.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_tags,name',
            'slug' => 'nullable|string|max:255|unique:blog_tags,slug',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->name);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Set sort order
        if (empty($data['sort_order'])) {
            $data['sort_order'] = BlogTag::max('sort_order') + 1;
        }

        BlogTag::create($data);

        return redirect()->route('admin.blog-tags.index')
            ->with('success', 'Blog tag created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(BlogTag $blogTag)
    {
        $blogTag->loadCount('blogs');
        return view('admin.blog-tags.edit', compact('blogTag'));
    }

    /**
     * Update blog tag.
     */
    public function update(Request $request, BlogTag $blogTag)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_tags,name,' . $blogTag->id,
            'slug' => 'nullable|string|max:255|unique:blog_tags,slug,' . $blogTag->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->name);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        $blogTag->update($data);

        return redirect()->route('admin.blog-tags.index')
            ->with('success', 'Blog tag updated successfully.');
    }

    /**
     * Delete blog tag.
     */
    public function destroy(BlogTag $blogTag)
    {
        $blogTag->delete();
        
        // Check if AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog tag deleted successfully.'
            ]);
        }
        
        return back()->with('success', 'Blog tag deleted successfully.');
    }

    /**
     * Toggle tag status (AJAX).
     */
    public function toggleStatus(Request $request, BlogTag $blogTag)
    {
        $blogTag->update([
            'status' => $blogTag->status === 'active' ? 'inactive' : 'active'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status' => $blogTag->status,
            'badge' => $blogTag->status_badge
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
            'ids.*' => 'exists:blog_tags,id',
        ]);

        $action = $request->action;
        $ids = $request->ids;
        $count = count($ids);

        switch ($action) {
            case 'delete':
                BlogTag::whereIn('id', $ids)->delete();
                $message = "{$count} tag(s) deleted successfully.";
                break;

            case 'activate':
                BlogTag::whereIn('id', $ids)->update(['status' => 'active']);
                $message = "{$count} tag(s) activated successfully.";
                break;

            case 'deactivate':
                BlogTag::whereIn('id', $ids)->update(['status' => 'inactive']);
                $message = "{$count} tag(s) deactivated successfully.";
                break;

            default:
                return back()->with('error', 'Invalid action selected.');
        }

        return back()->with('success', $message);
    }
}
