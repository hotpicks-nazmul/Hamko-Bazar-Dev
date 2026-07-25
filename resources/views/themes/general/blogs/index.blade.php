@extends('themes.general.layouts.app')

@section('title', 'Blog')

@push('styles')
<style>
/* Blog Page Layout */
.blog-page {
    display: flex;
    min-height: 100vh;
    background: #f5f5f5;
}

/* Fixed Left Sidebar */
.blog-sidebar {
    width: 280px;
    min-width: 280px;
    background: #fff;
    border-right: 1px solid #e0e0e0;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    flex-shrink: 0;
}

.blog-sidebar::-webkit-scrollbar {
    width: 4px;
}

.blog-sidebar::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 4px;
}

.sidebar-header {
    padding: 24px 20px;
    border-bottom: 1px solid #e0e0e0;
    background: #fafafa;
}

.sidebar-header h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-header h4 i {
    color: #1976d2;
}

/* Search in Sidebar */
.sidebar-search {
    padding: 16px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.sidebar-search .search-form {
    display: flex;
    gap: 8px;
}

.sidebar-search .search-input {
    flex: 1;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    transition: border-color 0.3s;
    min-width: 0;
    box-sizing: border-box;
}

.sidebar-search .search-input:focus {
    outline: none;
    border-color: #1976d2;
}

.sidebar-search .search-btn {
    background: #1976d2;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 14px;
    cursor: pointer;
    transition: background 0.3s;
}

.sidebar-search .search-btn:hover {
    background: #1565c0;
}

/* Categories in Sidebar */
.sidebar-categories {
    padding: 16px 0;
}

.sidebar-categories .category-title {
    padding: 0 20px 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #888;
    letter-spacing: 0.5px;
}

.sidebar-categories .category-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-categories .category-list li a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    color: #555;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.sidebar-categories .category-list li a:hover,
.sidebar-categories .category-list li a.active {
    background: #e3f2fd;
    color: #1976d2;
    border-left-color: #1976d2;
}

.sidebar-categories .category-list li a .count {
    background: #f0f0f0;
    color: #888;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 12px;
}

.sidebar-categories .category-list li a:hover .count,
.sidebar-categories .category-list li a.active .count {
    background: #1976d2;
    color: #fff;
}

/* Main Content Area */
.blog-main {
    flex: 1;
    padding: 30px;
    max-width: calc(100% - 280px);
}

/* Page Header */
.blog-page-header {
    margin-bottom: 30px;
}

.blog-page-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.blog-page-header p {
    color: #666;
    font-size: 15px;
}

/* Blog Posts Grid */
.blog-posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
}

/* Blog Card */
.blog-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}

.blog-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.blog-card .card-image {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.blog-card .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.blog-card:hover .card-image img {
    transform: scale(1.08);
}

.blog-card .card-image .placeholder-image {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.blog-card .card-image .placeholder-image i {
    font-size: 3rem;
    color: rgba(255,255,255,0.4);
}

.blog-card .card-image .category-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(255,255,255,0.95);
    color: #1976d2;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.blog-card .card-content {
    padding: 20px;
}

.blog-card .card-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 10px;
    line-height: 1.4;
}

.blog-card .card-title a {
    color: inherit;
    text-decoration: none;
}

.blog-card .card-title a:hover {
    color: #1976d2;
}

.blog-card .card-excerpt {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.blog-card .card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}

.blog-card .card-meta .author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.blog-card .card-meta .author img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.blog-card .card-meta .author .name {
    font-size: 13px;
    font-weight: 500;
    color: #333;
}

.blog-card .card-meta .date {
    font-size: 12px;
    color: #888;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Pagination */
.blog-pagination {
    margin-top: 40px;
    display: flex;
    justify-content: center;
}

.blog-pagination .pagination {
    gap: 6px;
}

.blog-pagination .page-link {
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    color: #555;
    font-weight: 500;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    transition: all 0.3s;
}

.blog-pagination .page-link:hover {
    background: #e3f2fd;
    color: #1976d2;
}

.blog-pagination .page-item.active .page-link {
    background: #1976d2;
    color: #fff;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 12px;
}

.empty-state i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h4 {
    color: #333;
    margin-bottom: 8px;
}

.empty-state p {
    color: #888;
}

/* Responsive */
@media (max-width: 992px) {
    .blog-page {
        flex-direction: column;
    }
    
    .blog-sidebar {
        width: 100%;
        min-width: 100%;
        height: auto;
        position: relative;
    }
    
    .blog-main {
        max-width: 100%;
        padding: 20px;
    }
    
    .sidebar-categories .category-list {
        display: flex;
        flex-wrap: wrap;
        padding: 0 16px 16px;
        gap: 8px;
    }
    
    .sidebar-categories .category-list li a {
        padding: 8px 16px;
        border-radius: 20px;
        border-left: none;
        background: #f5f5f5;
    }
    
    .sidebar-categories .category-list li a:hover,
    .sidebar-categories .category-list li a.active {
        background: #1976d2;
        color: #fff;
    }
}

@media (max-width: 576px) {
    .blog-posts-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
<div class="blog-page">
    <!-- Fixed Left Sidebar -->
    <aside class="blog-sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-journal-text"></i> Blog</h4>
        </div>
        
        <!-- Search -->
        <div class="sidebar-search">
            <form action="{{ route('blogs.index') }}" method="GET" class="search-form">
                <input type="text" class="search-input" name="search" placeholder="Search posts..." value="{{ request('search') }}">
                <button type="submit" class="search-btn"><i class="bi bi-search"></i></button>
            </form>
        </div>
        
        <!-- Categories -->
        <div class="sidebar-categories">
            <h5 class="category-title">Categories</h5>
            <ul class="category-list">
                <li>
                    <a href="{{ route('blogs.index') }}" class="{{ !request('category') && !request('search') ? 'active' : '' }}">
                        <span>All Posts</span>
                        <span class="count">{{ \Cache::remember('blog_published_count', 3600, function() { return \App\Models\Blog::published()->count(); }) }}</span>
                    </a>
                </li>
                @foreach($categories as $category)
                    <li>
                        <a href="{{ route('blogs.index', ['category' => $category->slug]) }}" 
                           class="{{ request('category') == $category->slug ? 'active' : '' }}">
                            <span>{{ $category->name }}</span>
                            <span class="count">{{ \Cache::remember('blog_count_' . $category->id, 3600, function() use ($category) { return $category->blogs()->where('status', 'published')->where('published_at', '<=', now())->count(); }) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="blog-main">
        <!-- Page Header -->
        <div class="blog-page-header">
            @if(request('search'))
                <h1>Search Results for "{{ request('search') }}"</h1>
                <p>{{ $blogs->total() }} post(s) found</p>
            @elseif(request('category'))
                @php $cat = \Cache::remember('category_by_slug_' . request('category'), 3600, function() { return \App\Models\Category::where('slug', request('category'))->first(); }); @endphp
                <h1>{{ $cat ? $cat->name : 'Category' }}</h1>
                <p>Browse all posts in this category</p>
            @else
                <h1>All Posts</h1>
                <p>Discover our latest articles and insights</p>
            @endif
        </div>

        @if($blogs->count() > 0)
            <!-- Blog Posts Grid -->
            <div class="blog-posts-grid">
                @foreach($blogs as $blog)
                    <article class="blog-card">
                        <div class="card-image">
                            @if($blog->featured_image)
                                <a href="{{ route('blogs.show', $blog->slug) }}">
                                    <img src="{{ asset('storage/' . $blog->featured_image) }}" alt="{{ $blog->title }}" loading="lazy">
                                </a>
                            @else
                                <div class="placeholder-image">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                            @endif
                            @if($blog->category)
                                <span class="category-badge">{{ $blog->category->name }}</span>
                            @endif
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">
                                <a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a>
                            </h3>
                            <p class="card-excerpt">{{ Str::limit(strip_tags($blog->content), 120) }}</p>
                            <div class="card-meta">
                                @if($blog->author)
                                    <div class="author">
                                        <img src="{{ $blog->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($blog->author->name ?? 'Admin') . '&background=1976d2&color=fff' }}" 
                                             alt="{{ $blog->author->name ?? 'Admin' }}">
                                        <span class="name">{{ $blog->author->name ?? 'Admin' }}</span>
                                    </div>
                                @endif
                                <span class="date">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $blog->published_at ? $blog->published_at->format('M d, Y') : '' }}
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($blogs->hasPages())
                <div class="blog-pagination">
                    {{ $blogs->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-journal-text"></i>
                <h4>No posts found</h4>
                <p>Try adjusting your search or filter to find what you're looking for.</p>
                <a href="{{ route('blogs.index') }}" class="btn btn-primary mt-3">View All Posts</a>
            </div>
        @endif
    </main>
</div>
@endsection
