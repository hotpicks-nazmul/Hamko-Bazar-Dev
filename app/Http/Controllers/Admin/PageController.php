<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;

class PageController extends Controller
{
    use SortsLists;

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
        
        // Sorting — the whitelist matters: this screen has no sort control, so `?sort=` was a free
        // SQLSTATE 42S22 for anyone who typed one.
        $query->orderBy(
            $this->sortColumn($request, ['title', 'slug', 'status', 'meta_title', 'created_at', 'updated_at']),
            $this->sortDirection($request)
        );

        $pages = $query->paginate($this->perPage($request, 15));
        
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
        $data['slug'] = $this->uniqueSlug(Str::slug($request->title));
        
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

    /**
     * A slug that cannot collide. The old `- time()` suffix produced the same slug for two saves in the
     * same second, and two pages with one slug means one of them is unreachable on the storefront.
     */
    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = $base !== '' ? $base : 'page';
        $slug = $base;
        $i = 1;

        while (Page::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function show(Page $page)
    {
        // There is no `admin.pages.show` view, so this route answered 500 for every page: the index's
        // view button was dead. The edit screen shows every field (content, image, SEO, status), so send
        // the operator there instead of pretending a detail page exists.
        return redirect()->route('admin.pages.edit', $page->id);
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
            $data['slug'] = $this->uniqueSlug(Str::slug($request->title), $page->id);
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
