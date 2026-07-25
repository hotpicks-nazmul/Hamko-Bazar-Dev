<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $query = Page::query();
        
        // Search functionality
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%")
                  ->orWhere('meta_title', 'like', "%{$request->search}%")
                  ->orWhere('meta_description', 'like', "%{$request->search}%");
            });
        }
        
        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);
        
        $pages = $query->paginate(15);
        
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->except('is_active', 'featured_image');
        $data['slug'] = Str::slug($request->title);
        
        // Ensure unique slug
        $count = Page::where('slug', $data['slug'])->count();
        if ($count > 0) {
            $data['slug'] = $data['slug'] . '-' . time();
        }
        
        $data['status'] = $request->has('is_active') && $request->is_active ? 'published' : 'draft';
        $data['created_by'] = auth()->id();

        // Handle featured image upload with WebP conversion
        if ($request->hasFile('featured_image')) {
            if (ImageHelper::isValidImage($request->file('featured_image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('featured_image'),
                    'pages',         // directory
                    1200,            // max width
                    0,               // no thumbnail
                    80               // quality
                );
                $data['featured_image'] = $imageResult['path'];
            }
        }

        $page = Page::create($data);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function show(Page $page)
    {
        return view('admin.pages.show', compact('page'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->except('is_active', 'featured_image', 'remove_image');
        
        // Only update slug if title changed
        if ($page->title !== $request->title) {
            $data['slug'] = Str::slug($request->title);
            
            // Ensure unique slug
            $count = Page::where('slug', $data['slug'])->where('id', '!=', $page->id)->count();
            if ($count > 0) {
                $data['slug'] = $data['slug'] . '-' . time();
            }
        }
        
        $data['status'] = $request->has('is_active') && $request->is_active ? 'published' : 'draft';

        // Handle featured image upload with WebP conversion
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($page->featured_image) {
                ImageHelper::deleteImage($page->featured_image);
            }
            if (ImageHelper::isValidImage($request->file('featured_image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('featured_image'),
                    'pages',         // directory
                    1200,            // max width
                    0,               // no thumbnail
                    80               // quality
                );
                $data['featured_image'] = $imageResult['path'];
            }
        }
        
        // Handle image removal
        if ($request->remove_image == '1' && $page->featured_image) {
            ImageHelper::deleteImage($page->featured_image);
            $data['featured_image'] = null;
        }

        $page->update($data);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        // Delete featured image if exists
        if ($page->featured_image) {
            ImageHelper::deleteImage($page->featured_image);
        }
        
        $page->delete();
        return back()->with('success', 'Page deleted successfully.');
    }

    public function toggle(Page $page)
    {
        $page->update(['status' => $page->status === 'published' ? 'draft' : 'published']);
        return back()->with('success', 'Page status updated.');
    }
}
