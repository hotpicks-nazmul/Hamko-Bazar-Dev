<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;

class BlogController extends Controller
{
    use SortsLists;

    public function index(Request $request)
    {
        $query = Blog::with(['author', 'category']);
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%");
            });
        }
        
        // Status filter
        if ($request->status) {
            if ($request->status === 'published') {
                $query->where('status', 'published')->where('published_at', '<=', now());
            } else {
                $query->where(function($q) {
                    $q->where('status', 'draft')
                      ->orWhere(function($q2) {
                          $q2->where('status', 'published')->where('published_at', '>', now());
                      });
                });
            }
        }
        
        // Category filter
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        
        // Sorting and page size — `?per_page=abc` reached paginate() and `?sort=` reached orderBy().
        $query->orderBy(
            $this->sortColumn($request, ['title', 'slug', 'status', 'published_at', 'category_id', 'created_at', 'updated_at']),
            $this->sortDirection($request)
        );

        $blogs = $query->paginate($this->perPage($request, 15));
        
        // AJAX response
        if ($request->ajax()) {
            $html = view('admin.blogs.partials.table-rows', compact('blogs'))->render();
            $pagination = $blogs->links('vendor.pagination.bootstrap-5')->toHtml();
            
            return response()->json([
                'html' => $html,
                'pagination' => $pagination
            ]);
        }
        
        return view('admin.blogs.index', compact('blogs'));
    }

    public function create()
    {
        // Use blog categories
        $categories = BlogCategory::active()->ordered()->get();
        return view('admin.blogs.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'content' => 'required',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:published,draft',
            'featured_image' => 'nullable|image|max:5120',
            'category_id' => 'nullable|exists:blog_categories,id',
            'tags' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => $request->slug ?: \Str::slug($request->title),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'status' => $request->status,
            'author_id' => auth()->id(),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
        ];

        // Handle category_id - only set if provided and valid
        if ($request->category_id) {
            $data['category_id'] = $request->category_id;
        }

        // Handle tags
        if ($request->tags) {
            $tags = array_map('trim', explode(',', $request->tags));
            $data['tags'] = array_filter($tags);
        }

        // Handle published_at
        if ($request->published_at) {
            $data['published_at'] = $request->published_at;
        } elseif ($request->status === 'published') {
            $data['published_at'] = now();
        }

        // Handle featured image upload with WebP conversion
        if ($request->hasFile('featured_image')) {
            if (ImageHelper::isValidImage($request->file('featured_image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('featured_image'),
                    'blogs',          // directory
                    1200,             // max width (blog image)
                    300,              // thumbnail width
                    80                // quality
                );
                $data['featured_image'] = $imageResult['path'];
            }
        }

        $blog = Blog::create($data);
        $this->syncTags($blog, $data['tags'] ?? []);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post created successfully.');
    }

    public function show(Blog $blog)
    {
        return view('admin.blogs.show', compact('blog'));
    }

    public function edit(Blog $blog)
    {
        // Use blog categories
        $categories = BlogCategory::active()->ordered()->get();
        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug,' . $blog->id,
            'content' => 'required',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:published,draft',
            'featured_image' => 'nullable|image|max:5120',
            'category_id' => 'nullable|exists:blog_categories,id',
            'tags' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => $request->slug ?: \Str::slug($request->title),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'status' => $request->status,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
        ];

        // Handle category_id - only set if provided and valid
        if ($request->category_id) {
            $data['category_id'] = $request->category_id;
        } else {
            $data['category_id'] = null;
        }

        // Handle tags
        if ($request->tags) {
            $tags = array_map('trim', explode(',', $request->tags));
            $data['tags'] = array_filter($tags);
        } else {
            $data['tags'] = null;
        }

        // Handle published_at
        if ($request->published_at) {
            $data['published_at'] = $request->published_at;
        } elseif ($request->status === 'published' && !$blog->published_at) {
            $data['published_at'] = now();
        }

        // Handle featured image upload with WebP conversion
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($blog->featured_image) {
                ImageHelper::deleteImage($blog->featured_image);
            }
            if (ImageHelper::isValidImage($request->file('featured_image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('featured_image'),
                    'blogs',          // directory
                    1200,             // max width (blog image)
                    300,              // thumbnail width
                    80                // quality
                );
                $data['featured_image'] = $imageResult['path'];
            }
        }

        $blog->update($data);
        $this->syncTags($blog, $data['tags'] ?? []);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post updated successfully.');
    }

    /**
     * Keep the `blog_tags` table in step with the tags typed on the post.
     *
     * The post stores its tags as a comma string in `blogs.tags` (cast to array) *and* has a
     * belongsToMany to `blog_tags` that nothing ever wrote to, so the whole Content → Blog Tags screen
     * managed a table no post referenced and `blog_tag` stayed empty. Both are kept: the string is what the
     * storefront renders, the relation is what makes the tag admin honest (counts, deletion).
     */
    private function syncTags(Blog $blog, array $names): void
    {
        $ids = [];

        foreach (array_unique(array_filter(array_map('trim', $names))) as $name) {
            $tag = BlogTag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'status' => 'active']
            );
            $ids[] = $tag->id;
        }

        $blog->tags()->sync($ids);
    }

    public function destroy(Blog $blog)
    {
        // Delete featured image from storage
        if ($blog->featured_image) {
            ImageHelper::deleteImage($blog->featured_image);
        }

        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Blog post deleted successfully.');
    }

    public function toggle(Blog $blog)
    {
        // Three states, not two. A post with status=published but a future published_at is scheduled:
        // the list already renders it as not-published, so flipping the status (the old behaviour) turned
        // "Mark as Published" into "unpublish". Publishing a scheduled post means publishing it NOW.
        $scheduled = $blog->status === 'published' && $blog->published_at && $blog->published_at->isFuture();
        $isPublished = $blog->status === 'published' && $blog->published_at && $blog->published_at->isPast();

        if ($scheduled) {
            $blog->update(['status' => 'published', 'published_at' => now()]);

            return back()->with('success', 'Post published now.');
        }

        if ($isPublished) {
            $blog->update(['status' => 'draft']);

            return back()->with('success', 'Post moved back to draft.');
        }

        $blog->update([
            'status' => 'published',
            'published_at' => $blog->published_at ?? now(),
        ]);

        return back()->with('success', 'Post published.');
    }

    /**
     * Display blog settings page.
     */
    public function settings()
    {
        $settings = \App\Models\Setting::where('group', 'blog')->get()->keyBy('key');
        return view('admin.blogs.settings', compact('settings'));
    }

    /**
     * Update blog settings.
     */
    public function updateSettings(Request $request)
    {
        $settings = [
            'blog_title' => $request->blog_title ?? 'Blog',
            'blog_subtitle' => $request->blog_subtitle ?? '',
            'blog_posts_per_page' => $request->blog_posts_per_page ?? 9,
            'blog_show_author' => $request->has('blog_show_author') ? 1 : 0,
            'blog_show_date' => $request->has('blog_show_date') ? 1 : 0,
            'blog_show_category' => $request->has('blog_show_category') ? 1 : 0,
            'blog_show_tags' => $request->has('blog_show_tags') ? 1 : 0,
            'blog_show_share_buttons' => $request->has('blog_show_share_buttons') ? 1 : 0,
            'blog_show_related_posts' => $request->has('blog_show_related_posts') ? 1 : 0,
            'blog_related_posts_count' => $request->blog_related_posts_count ?? 4,
            'blog_sidebar_show_search' => $request->has('blog_sidebar_show_search') ? 1 : 0,
            'blog_sidebar_show_categories' => $request->has('blog_sidebar_show_categories') ? 1 : 0,
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'blog', 'type' => is_numeric($value) && !in_array($key, ['blog_posts_per_page', 'blog_related_posts_count']) ? 'checkbox' : 'text']
            );
        }

        return redirect()->route('admin.blog-settings.index')->with('success', 'Blog settings updated successfully.');
    }
}
