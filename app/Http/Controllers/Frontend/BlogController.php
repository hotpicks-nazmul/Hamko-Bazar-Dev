<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display blog listing.
     */
    public function index(Request $request)
    {
        $query = Blog::published()
            ->with(['author', 'category']);

        // Filter by category
        if ($request->has('category')) {
            $category = BlogCategory::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $blogs = $query->latest('published_at')->paginate(9);
        $categories = BlogCategory::active()->ordered()->get();

        return view('themes.general.blogs.index', compact('blogs', 'categories'));
    }

    /**
     * Display a single blog post.
     */
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
            ->published()
            ->with(['author', 'category'])
            ->firstOrFail();

        $relatedBlogs = Blog::where('category_id', $blog->category_id)
            ->where('id', '!=', $blog->id)
            ->published()
            ->latest('published_at')
            ->with(['author', 'category'])
            ->take(3)
            ->get();

        return view('themes.general.blogs.show', compact('blog', 'relatedBlogs'));
    }
}
