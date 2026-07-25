@extends('themes.general.layouts.app')

@section('title', $blog->title)

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
    overflow-x: hidden;
}

/* Article Container */
.article-container {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

/* Featured Image */
.article-featured-image {
    width: 100%;
    max-height: 450px;
    overflow: hidden;
}

.article-featured-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Article Header */
.article-header {
    padding: 30px 30px 20px;
    border-bottom: 1px solid #f0f0f0;
}

.article-header .category-badge {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 16px;
}

.article-header .article-title {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 20px;
    line-height: 1.3;
}

.article-header .article-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 24px;
    color: #666;
    font-size: 14px;
}

.article-header .article-meta .meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.article-header .article-meta .author-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.article-header .article-meta .author-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.article-header .article-meta .author-info .author-name {
    font-weight: 600;
    color: #333;
}

/* Article Content */
.article-content {
    padding: 30px;
    font-size: 17px;
    line-height: 1.9;
    color: #444;
}

.article-content p {
    margin-bottom: 1.8rem;
}

.article-content h2 {
    font-size: 26px;
    font-weight: 700;
    color: #1a1a1a;
    margin-top: 2.5rem;
    margin-bottom: 1.2rem;
}

.article-content h3 {
    font-size: 22px;
    font-weight: 600;
    color: #1a1a1a;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.article-content h4 {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-top: 1.8rem;
    margin-bottom: 0.8rem;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 2rem 0;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1.8rem;
    padding-left: 2rem;
}

.article-content li {
    margin-bottom: 0.6rem;
}

.article-content blockquote {
    border-left: 4px solid #1976d2;
    padding: 20px 24px;
    margin: 2rem 0;
    font-style: italic;
    color: #555;
    background: #f8f9fa;
    border-radius: 0 8px 8px 0;
}

.article-content a {
    color: #1976d2;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.article-content a:hover {
    color: #1565c0;
}

.article-content code {
    background: #f5f5f5;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.9em;
    color: #d32f2f;
}

.article-content pre {
    background: #2d3748;
    color: #e2e8f0;
    padding: 20px;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.article-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
}

.article-content th,
.article-content td {
    padding: 12px 16px;
    border: 1px solid #e0e0e0;
    text-align: left;
}

.article-content th {
    background: #f5f5f5;
    font-weight: 600;
}

/* Article Footer */
.article-footer {
    padding: 24px 30px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
}

.article-footer .footer-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

/* Tags */
.article-tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}

.article-tags .tag-label {
    font-weight: 600;
    color: #333;
    margin-right: 8px;
}

.article-tags .tag {
    display: inline-block;
    padding: 6px 14px;
    background: #f0f0f0;
    color: #555;
    border-radius: 20px;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.3s;
}

.article-tags .tag:hover {
    background: #1976d2;
    color: #fff;
}

/* Share Buttons */
.article-share {
    display: flex;
    align-items: center;
    gap: 10px;
}

.article-share .share-label {
    font-weight: 600;
    color: #333;
}

.article-share .share-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s;
}

.article-share .share-btn:hover {
    transform: translateY(-2px);
}

.article-share .btn-facebook { background: #1877f2; }
.article-share .btn-twitter { background: #1da1f2; }
.article-share .btn-linkedin { background: #0077b5; }
.article-share .btn-whatsapp { background: #25d366; }

/* Author Box */
.author-box {
    margin-top: 30px;
    padding: 24px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.author-box .author-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.author-box .author-info .author-name {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 4px;
}

.author-box .author-info .author-role {
    color: #1976d2;
    font-size: 14px;
    margin-bottom: 10px;
}

.author-box .author-info .author-bio {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 0;
}

/* Related Posts Section */
.related-posts-section {
    margin-top: 40px;
}

.related-posts-section .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.related-posts-section .section-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 10px;
}

.related-posts-section .section-title i {
    color: #1976d2;
}

/* Carousel Navigation */
.carousel-nav {
    display: flex;
    gap: 8px;
}

.carousel-nav .nav-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid #e0e0e0;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.carousel-nav .nav-btn:hover {
    background: #1976d2;
    border-color: #1976d2;
    color: #fff;
}

/* Related Posts Carousel */
.related-posts-carousel {
    position: relative;
    overflow: hidden;
}

.carousel-track {
    display: flex;
    gap: 20px;
    transition: transform 0.5s ease;
}

/* Related Post Card */
.related-post-card {
    min-width: calc(25% - 15px);
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s;
}

.related-post-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.related-post-card .card-image {
    height: 150px;
    overflow: hidden;
}

.related-post-card .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.related-post-card:hover .card-image img {
    transform: scale(1.08);
}

.related-post-card .card-image .placeholder-image {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.related-post-card .card-image .placeholder-image i {
    font-size: 2rem;
    color: rgba(255,255,255,0.4);
}

.related-post-card .card-content {
    padding: 16px;
}

.related-post-card .card-category {
    font-size: 11px;
    font-weight: 600;
    color: #1976d2;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.related-post-card .card-title {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 8px;
    line-height: 1.4;
}

.related-post-card .card-title a {
    color: inherit;
    text-decoration: none;
}

.related-post-card .card-title a:hover {
    color: #1976d2;
}

.related-post-card .card-date {
    font-size: 12px;
    color: #888;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Responsive */
@media (max-width: 1200px) {
    .related-post-card {
        min-width: calc(33.333% - 14px);
    }
}

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
    
    .related-post-card {
        min-width: calc(50% - 10px);
    }
}

@media (max-width: 768px) {
    .article-header .article-title {
        font-size: 24px;
    }
    
    .article-content {
        padding: 20px;
    }
    
    .article-footer .footer-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .author-box {
        flex-direction: column;
        text-align: center;
        align-items: center;
    }
    
    .related-post-card {
        min-width: calc(100% - 10px);
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
                <input type="text" class="search-input" name="search" placeholder="Search posts...">
                <button type="submit" class="search-btn"><i class="bi bi-search"></i></button>
            </form>
        </div>
        
        <!-- Categories -->
        <div class="sidebar-categories">
            <h5 class="category-title">Categories</h5>
            <ul class="category-list">
                <li>
                    <a href="{{ route('blogs.index') }}">
                        <span>All Posts</span>
                        <span class="count">{{ \Cache::remember('blog_published_count', 3600, function() { return \App\Models\Blog::published()->count(); }) }}</span>
                    </a>
                </li>
                @foreach(\App\Models\Category::whereNull('parent_id')->where('status', 'active')->orderBy('name')->get() as $category)
                    <li>
                        <a href="{{ route('blogs.index', ['category' => $category->slug]) }}" 
                           class="{{ $blog->category && $blog->category->slug == $category->slug ? 'active' : '' }}">
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
        <!-- Article Container -->
        <article class="article-container">
            <!-- Featured Image -->
            @if($blog->featured_image)
                <div class="article-featured-image">
                    <img src="{{ asset('storage/' . $blog->featured_image) }}" alt="{{ $blog->title }}" loading="lazy">
                </div>
            @endif

            <!-- Article Header -->
            <header class="article-header">
                @if($blog->category)
                    <span class="category-badge">{{ $blog->category->name }}</span>
                @endif
                <h1 class="article-title">{{ $blog->title }}</h1>
                <div class="article-meta">
                    @if($blog->author)
                        <div class="author-info">
                            <img src="{{ $blog->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($blog->author->name ?? 'Admin') . '&background=1976d2&color=fff' }}" 
                                 alt="{{ $blog->author->name ?? 'Admin' }}">
                            <span class="author-name">{{ $blog->author->name ?? 'Admin' }}</span>
                        </div>
                    @endif
                    <div class="meta-item">
                        <i class="bi bi-calendar3"></i>
                        <span>{{ $blog->published_at ? $blog->published_at->format('F d, Y') : '' }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-clock"></i>
                        <span>{{ ceil(str_word_count(strip_tags($blog->content)) / 200) }} min read</span>
                    </div>
                </div>
            </header>

            <!-- Article Content -->
            <div class="article-content">
                {{-- Content is expected to be HTML from admin editor. Ensure admin input is sanitized. --}}
                {!! class_exists('Purifier') ? Purifier::clean($blog->content) : $blog->content !!}
            </div>

            <!-- Article Footer -->
            <footer class="article-footer">
                <div class="footer-row">
                    <!-- Tags -->
                    @if($blog->tags)
                        <div class="article-tags">
                            <span class="tag-label"><i class="bi bi-tags me-1"></i> Tags:</span>
                            @foreach(is_array($blog->tags) ? $blog->tags : json_decode($blog->tags, true) ?? [] as $tag)
                                <a href="{{ route('blogs.index', ['search' => $tag]) }}" class="tag">{{ $tag }}</a>
                            @endforeach
                        </div>
                    @endif

                    <!-- Share -->
                    <div class="article-share">
                        <span class="share-label">Share:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                           target="_blank" class="share-btn btn-facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($blog->title) }}" 
                           target="_blank" class="share-btn btn-twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($blog->title) }}" 
                           target="_blank" class="share-btn btn-linkedin">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($blog->title . ' - ' . url()->current()) }}" 
                           target="_blank" class="share-btn btn-whatsapp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </footer>
        </article>

        <!-- Author Box -->
        @if($blog->author)
            <div class="author-box">
                <img src="{{ $blog->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($blog->author->name ?? 'Admin') . '&background=1976d2&color=fff&size=160' }}" 
                     alt="{{ $blog->author->name ?? 'Admin' }}"
                     class="author-avatar">
                <div class="author-info">
                    <h4 class="author-name">{{ $blog->author->name ?? 'Admin' }}</h4>
                    <p class="author-role">Content Writer</p>
                    <p class="author-bio">{{ $blog->author->bio ?? 'Passionate about sharing knowledge and insights through engaging content.' }}</p>
                </div>
            </div>
        @endif

        <!-- Related Posts Section -->
        @if($relatedBlogs->count() > 0)
            <section class="related-posts-section">
                <div class="section-header">
                    <h3 class="section-title"><i class="bi bi-journal-text"></i> Related Posts</h3>
                    <div class="carousel-nav">
                        <button class="nav-btn" onclick="scrollCarousel(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button class="nav-btn" onclick="scrollCarousel(1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="related-posts-carousel">
                    <div class="carousel-track" id="carouselTrack">
                        @foreach($relatedBlogs as $related)
                            <article class="related-post-card">
                                <div class="card-image">
                                    @if($related->featured_image)
                                        <a href="{{ route('blogs.show', $related->slug) }}">
                                            <img src="{{ asset('storage/' . $related->featured_image) }}" alt="{{ $related->title }}" loading="lazy">
                                        </a>
                                    @else
                                        <div class="placeholder-image">
                                            <i class="bi bi-journal-text"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-content">
                                    @if($related->category)
                                        <span class="card-category">{{ $related->category->name }}</span>
                                    @endif
                                    <h4 class="card-title">
                                        <a href="{{ route('blogs.show', $related->slug) }}">{{ Str::limit($related->title, 50) }}</a>
                                    </h4>
                                    <span class="card-date">
                                        <i class="bi bi-calendar3"></i>
                                        {{ $related->published_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>
</div>

@push('scripts')
<script>
let carouselPosition = 0;
const cardWidth = 280; // card width + gap

function scrollCarousel(direction) {
    const track = document.getElementById('carouselTrack');
    const cards = track.querySelectorAll('.related-post-card');
    const visibleCards = Math.floor(track.parentElement.offsetWidth / cardWidth);
    const maxPosition = Math.max(0, cards.length - visibleCards);
    
    carouselPosition += direction;
    carouselPosition = Math.max(0, Math.min(carouselPosition, maxPosition));
    
    track.style.transform = `translateX(-${carouselPosition * cardWidth}px)`;
}
</script>
@endpush
@endsection
