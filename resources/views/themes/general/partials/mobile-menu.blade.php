<!-- Mobile Menu Overlay -->
<div id="mobileMenuOverlay" class="fixed inset-0 bg-black/50 z-50 hidden" onclick="closeMobileMenu()"></div>

<!-- Mobile Menu -->
<div id="mobileMenu" class="fixed top-0 left-0 w-80 h-full bg-white z-50 transform -translate-x-full transition-transform duration-300 overflow-y-auto">
    <div class="p-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 gradient-halal rounded-full flex items-center justify-center">
                    <i class="bi bi-shop text-white"></i>
                </div>
                <span class="font-poppins text-xl font-bold text-halal-green">Halal Food</span>
            </div>
            <button onclick="closeMobileMenu()" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <!-- Search -->
        <form action="{{ route('products.index') }}" method="GET" class="mb-6">
            <div class="relative" id="mobileSearchContainer">
                <input type="text" name="search" id="mobileSearchInput" placeholder="Search products..." 
                    class="w-full pl-4 pr-14 py-2 border border-gray-300 rounded-full focus:border-halal-green focus:outline-none"
                    autocomplete="off">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-halal-green text-white w-10 h-10 rounded-full hover:text-amber-400 transition-colors flex items-center justify-center">
                    <i class="bi bi-search"></i>
                </button>
                
                <!-- Live Search Results Dropdown -->
                <div id="mobileSearchResults" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-100 max-h-80 overflow-y-auto z-50 hidden">
                    <!-- Results will be populated by JavaScript -->
                </div>
            </div>
        </form>
        
        <!-- User Section -->
        @auth
        <div class="bg-green-50 rounded-lg p-4 mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-halal-green rounded-full flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">{{ Auth::user()->name }}</h4>
                    <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <a href="{{ route('account.dashboard') }}" class="flex items-center text-gray-600 hover:text-halal-green py-2">
                    <i class="bi bi-grid mr-3"></i> Dashboard
                </a>
                <a href="{{ route('account.profile') }}" class="flex items-center text-gray-600 hover:text-halal-green py-2">
                    <i class="bi bi-person mr-3"></i> My Profile
                </a>
                <a href="{{ route('account.orders') }}" class="flex items-center text-gray-600 hover:text-halal-green py-2">
                    <i class="bi bi-bag mr-3"></i> My Orders
                </a>
                <a href="{{ route('account.wishlist') }}" class="flex items-center text-gray-600 hover:text-halal-green py-2">
                    <i class="bi bi-heart mr-3"></i> Wishlist
                </a>
                <a href="{{ route('account.addresses') }}" class="flex items-center text-gray-600 hover:text-halal-green py-2">
                    <i class="bi bi-geo-alt mr-3"></i> Addresses
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center text-red-600 hover:text-red-700 py-2">
                        <i class="bi bi-box-arrow-right mr-3"></i> Logout
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <p class="text-gray-600 mb-3">Login to access your account</p>
            <a href="{{ route('login') }}" class="block w-full bg-halal-green text-white text-center py-2 rounded-lg hover:text-amber-400 transition-colors">
                <i class="bi bi-box-arrow-in-right mr-2"></i> Login
            </a>
            <a href="{{ route('register') }}" class="block w-full border border-halal-green text-halal-green text-center py-2 rounded-lg mt-2 hover:bg-green-50 transition-colors">
                <i class="bi bi-person-plus mr-2"></i> Register
            </a>
        </div>
        @endauth
        
        <!-- Navigation -->
        <nav class="space-y-1">
            <a href="{{ route('home') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('home') ? 'bg-green-50 text-halal-green' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="bi bi-house-door mr-3"></i> Home
            </a>
            <a href="{{ route('products.index') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('products.*') ? 'bg-green-50 text-halal-green' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="bi bi-grid mr-3"></i> Shop
            </a>
            <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="bi bi-stars mr-3"></i> New Arrivals
            </a>
            <a href="{{ route('products.index', ['sort' => 'discount']) }}" class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="bi bi-fire mr-3"></i> Deals
            </a>
            <a href="{{ route('blogs.index') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('blogs.*') ? 'bg-green-50 text-halal-green' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="bi bi-newspaper mr-3"></i> Blog
            </a>
            <a href="{{ route('pages.contact') }}" class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="bi bi-telephone mr-3"></i> Contact
            </a>
        </nav>
        
        <!-- Categories -->
        <div class="mt-6">
            <div class="flex items-center justify-between px-4 mb-3">
                <h4 class="font-medium text-gray-800">Categories</h4>
                <a href="{{ route('categories.index') }}" class="text-halal-green text-sm font-medium">View All</a>
            </div>
            <div class="grid grid-cols-2 gap-2 px-4">
                @foreach(($categories ?? [])->take(6) as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="flex flex-col items-center p-3 rounded-xl bg-gray-50 hover:bg-halal-green/10 transition-colors">
                    <div class="w-12 h-12 rounded-full bg-halal-green/10 flex items-center justify-center mb-2">
                        @if($category->image)
                            <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <i class="{{ $category->icon ?? 'bi bi-folder-fill' }} text-xl text-halal-green"></i>
                        @endif
                    </div>
                    <span class="text-sm font-medium text-gray-700 text-center">{{ $category->name }}</span>
                    <span class="text-xs text-gray-400">{{ $category->total_products_count ?? 0 }}</span>
                </a>
                @endforeach
            </div>
            @if(($categories->count() ?? 0) > 6)
            <a href="{{ route('categories.index') }}" class="flex items-center justify-center mx-4 mt-3 py-2 bg-halal-green/10 text-halal-green rounded-lg text-sm font-medium">
                View All Categories <i class="bi bi-arrow-right ml-2"></i>
            </a>
            @endif
        </div>
        
        <!-- Contact Info -->
        <div class="mt-6 p-4 bg-halal-dark rounded-lg text-white">
            <h4 class="font-medium mb-3">Need Help?</h4>
            <div class="space-y-2 text-sm">
                <a href="tel:+8801700000000" class="flex items-center text-green-100 hover:text-white">
                    <i class="bi bi-telephone mr-2"></i> +880 1700-000000
                </a>
                <a href="mailto:info@halalfoodstore.com" class="flex items-center text-green-100 hover:text-white">
                    <i class="bi bi-envelope mr-2"></i> info@halalfoodstore.com
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMobileMenu() {
    document.getElementById('mobileMenu').classList.remove('-translate-x-full');
    document.getElementById('mobileMenuOverlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    document.getElementById('mobileMenu').classList.add('-translate-x-full');
    document.getElementById('mobileMenuOverlay').classList.add('hidden');
    document.body.style.overflow = '';
}

// Mobile Live Search
let mobileSearchTimeout;
const mobileSearchInput = document.getElementById('mobileSearchInput');
const mobileSearchResults = document.getElementById('mobileSearchResults');

if (mobileSearchInput) {
    mobileSearchInput.addEventListener('input', function(e) {
        clearTimeout(mobileSearchTimeout);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            mobileSearchResults.classList.add('hidden');
            mobileSearchResults.innerHTML = '';
            return;
        }
        
        // Show loading state
        mobileSearchResults.classList.remove('hidden');
        mobileSearchResults.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <i class="bi bi-arrow-repeat animate-spin text-xl"></i>
                <p class="mt-1 text-sm">Searching...</p>
            </div>
        `;
        
        // Debounce search
        mobileSearchTimeout = setTimeout(() => {
            fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    renderMobileSearchResults(data, query);
                })
                .catch(error => {
                    console.error('Search error:', error);
                    mobileSearchResults.innerHTML = `
                        <div class="p-4 text-center text-red-500">
                            <i class="bi bi-exclamation-circle text-xl"></i>
                            <p class="mt-1 text-sm">Error loading results</p>
                        </div>
                    `;
                });
        }, 300);
    });
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function renderMobileSearchResults(data, query) {
    const { products, categories } = data;
    
    if (products.length === 0 && categories.length === 0) {
        mobileSearchResults.innerHTML = `
            <div class="p-4 text-center">
                <i class="bi bi-search text-3xl text-gray-300"></i>
                <p class="mt-1 text-gray-500 text-sm">No results found</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    // Categories section
    if (categories.length > 0) {
        html += `<div class="p-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">Categories</div>`;
        categories.forEach(category => {
            html += `
                <a href="{{ route('products.index') }}?category=${escapeHtml(category.slug)}" class="flex items-center p-2 hover:bg-green-50 border-b border-gray-50" onclick="closeMobileMenu()">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-folder text-gray-400"></i>
                    </div>
                    <span class="ml-2 text-sm text-gray-700">${escapeHtml(category.name)}</span>
                </a>
            `;
        });
    }
    
    // Products section
    if (products.length > 0) {
        html += `<div class="p-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">Products</div>`;
        products.forEach(product => {
            const price = product.sale_price || product.price;
            let imageUrl = product.featured_image || 'https://placehold.co/50x50?text=P';
            if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('/storage/') && !imageUrl.startsWith('/uploads/')) {
                imageUrl = '/storage/' + imageUrl;
            }
            
            html += `
                <a href="{{ route('products.show', '') }}/${escapeHtml(product.slug)}" class="flex items-center p-2 hover:bg-green-50 border-b border-gray-50" onclick="closeMobileMenu()">
                    <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(product.name)}" class="w-12 h-12 rounded-lg object-cover bg-gray-100">
                    <div class="ml-2 flex-1">
                        <p class="text-sm font-medium text-gray-800 line-clamp-1">${escapeHtml(product.name)}</p>
                        <span class="text-halal-green text-sm font-semibold">৳${Number(price).toLocaleString()}</span>
                    </div>
                </a>
            `;
        });
    }
    
    // View all link
    html += `
        <a href="{{ route('products.index') }}?search=${encodeURIComponent(query)}" class="block p-3 text-center bg-halal-green text-white text-sm" onclick="closeMobileMenu()">
            View all results
        </a>
    `;
    
    mobileSearchResults.innerHTML = html;
}
</script>
