<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRTL ?? false ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Prevent browser caching during development -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <script>if('scrollRestoration'in history)history.scrollRestoration='manual';window.scrollTo(0,0);</script>
    
    <?php
    // Get SEO settings from middleware
    $seoSettings = $seoSettings ?? [];
    $siteMetaTitle = $seoSettings['site_meta_title'] ?? '';
    $siteMetaDescription = $seoSettings['site_meta_description'] ?? '';
    $siteMetaKeywords = $seoSettings['site_meta_keywords'] ?? '';
    $googleAnalyticsId = $seoSettings['google_analytics_id'] ?? '';
    $ogTitle = $seoSettings['og_title'] ?? ($siteMetaTitle ?: config('app.name'));
    $ogDescription = $seoSettings['og_description'] ?? ($siteMetaDescription ?: config('app.name') . ' - Premium Quality Store');
    $ogImage = $seoSettings['og_image'] ?? '';
    $twitterCardType = $seoSettings['twitter_card_type'] ?? 'summary_large_image';
    ?>
    
    <!-- SEO Meta Tags -->
    <title>{{ $seoSettings['site_name'] ?? config('app.name') }}@hasSection('title') - @yield('title')@endif</title>
    <link rel="icon" type="image/png" href="{{ asset($seoSettings['site_favicon'] ?? '') }}">
    <meta name="description" content="@yield('meta_description', $siteMetaDescription ?: config('app.name') . ' - Premium Quality Store. Shop online for the best products.')">
    <meta name="keywords" content="@yield('meta_keywords', $siteMetaKeywords ?: config('app.name') . ', online store, Bangladesh, ecommerce')">
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Open Graph -->
    <meta property="og:title" content="@yield('og_title', $ogTitle)">
    <meta property="og:description" content="@yield('og_description', $ogDescription)">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?: asset('storage/settings/og-default.jpg') ?: url('https://placehold.co/1200x630/2D5A27/FFFFFF?text=Hamko+Bazar') }}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="{{ $twitterCardType }}">
    <meta name="twitter:title" content="@yield('og_title', $ogTitle)">
    <meta name="twitter:description" content="@yield('og_description', $ogDescription)">
    <meta name="twitter:image" content="{{ $ogImage ?: asset('storage/settings/og-default.jpg') ?: url('https://placehold.co/1200x630/2D5A27/FFFFFF?text=Hamko+Bazar') }}">
    
    <!-- Google Analytics -->
    @if($googleAnalyticsId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $googleAnalyticsId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $googleAnalyticsId }}');
    </script>
    @endif
    
    <?php
    // Get theme settings
    $themeSettings = $themeSettings ?? [];
    $primaryColor = $themeSettings['primary_color'] ?? '#2D5A27';
    $secondaryColor = $themeSettings['secondary_color'] ?? '#7c3aed';
    $accentColor = $themeSettings['accent_color'] ?? '#10b981';
    $goldColor = $themeSettings['gold_color'] ?? '#d4af37';
    $headingFont = $themeSettings['heading_font'] ?? 'Inter';
    $bodyFont = $themeSettings['body_font'] ?? 'Inter';
    
    // Convert font names for Google Fonts
    $headingFontLink = str_replace(' ', '+', $headingFont);
    $bodyFontLink = str_replace(' ', '+', $bodyFont);
    $fontsToLoad = [];
    if (!in_array($headingFont, $fontsToLoad)) $fontsToLoad[] = $headingFontLink.':wght@300;400;500;600;700;800';
    if (!in_array($bodyFont, $fontsToLoad) && $bodyFont !== $headingFont) $fontsToLoad[] = $bodyFontLink.':wght@300;400;500;600;700;800';
    ?>
    
    <!-- Google Fonts - Dynamic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ implode('&family=', $fontsToLoad) }}&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'heading': ['{{ $headingFont }}', 'sans-serif'],
                        'body': ['{{ $bodyFont }}', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '{{ $primaryColor }}20',
                            100: '{{ $primaryColor }}30',
                            200: '{{ $primaryColor }}40',
                            300: '{{ $primaryColor }}50',
                            400: '{{ $primaryColor }}60',
                            500: '{{ $primaryColor }}',
                            600: '{{ $primaryColor }}',
                            700: '{{ $primaryColor }}',
                            800: '{{ $primaryColor }}',
                            900: '{{ $primaryColor }}',
                            DEFAULT: '{{ $primaryColor }}',
                        },
                        secondary: {
                            DEFAULT: '{{ $secondaryColor }}',
                        },
                        accent: {
                            DEFAULT: '{{ $accentColor }}',
                        },
                        halal: {
                            green: '#2D5A27',
                            light: '#4A7C43',
                            dark: '#1E3D1A',
                            gold: '#D4AF37',
                            cream: '#FDF8F0',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        html {
            overflow-x: hidden;
        }
        body {
            width: 100%;
        }
        :root {
            /* Theme Colors */
            --theme-primary: {{ $primaryColor }};
            --theme-secondary: {{ $secondaryColor }};
            --theme-accent: {{ $accentColor }};
            --theme-gold: {{ $goldColor }};
            
            /* Hardcoded Colors Override - Map to Theme Colors */
            --hardcoded-green: #2D5A27;
            --hardcoded-green-light: #4A7C43;
            
            /* Font Families */
            --theme-heading-font: '{{ $headingFont }}', sans-serif;
            --theme-body-font: '{{ $bodyFont }}', sans-serif;
            
            /* Extended Color Palette */
            --theme-success: #10b981;
            --theme-warning: #f59e0b;
            --theme-danger: #ef4444;
            --theme-info: #3b82f6;
            --theme-dark: #1f2937;
            --theme-light: #f9fafb;
        }
        body {
            font-family: var(--theme-body-font);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--theme-heading-font);
        }
        
        /* Theme Color Classes */
        .theme-primary { color: var(--theme-primary); }
        .theme-secondary { color: var(--theme-secondary); }
        .theme-accent { color: var(--theme-accent); }
        .theme-gold { color: var(--theme-gold); }
        .theme-success { color: var(--theme-success); }
        .theme-warning { color: var(--theme-warning); }
        .theme-danger { color: var(--theme-danger); }
        .theme-dark { color: var(--theme-dark); }
        
        /* Background Classes */
        .bg-theme-primary { background-color: var(--theme-primary); }
        .bg-theme-secondary { background-color: var(--theme-secondary); }
        .bg-theme-accent { background-color: var(--theme-accent); }
        .bg-theme-gold { background-color: var(--theme-gold); }
        .bg-theme-success { background-color: var(--theme-success); }
        .bg-theme-warning { background-color: var(--theme-warning); }
        .bg-theme-danger { background-color: var(--theme-danger); }
        .bg-theme-dark { background-color: var(--theme-dark); }
        
        /* Border Classes */
        .border-theme-primary { border-color: var(--theme-primary); }
        .border-theme-secondary { border-color: var(--theme-secondary); }
        
        /* Bootstrap Override - Primary Button */
        .btn-primary {
            background-color: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
        }
        .btn-primary:hover {
            background-color: var(--theme-secondary) !important;
            border-color: var(--theme-secondary) !important;
        }
        .btn-outline-primary {
            color: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
        }
        .btn-outline-primary:hover {
            background-color: var(--theme-primary) !important;
            color: white !important;
        }
        
        /* Links */
        a { color: var(--theme-primary); }
        a:hover { color: var(--theme-secondary); }
        
        /* Halal Theme Colors Override - Use Theme Primary */
        .bg-halal-green { background-color: var(--theme-primary) !important; }
        .hover\:bg-halal-green:hover { background-color: var(--theme-secondary) !important; }
        .bg-halal-dark { background-color: var(--theme-dark); }
        .text-halal-green { color: var(--theme-primary) !important; }
        
        /* Gold Theme Color Override */
        .bg-halal-gold { background-color: var(--theme-gold) !important; }
        .text-halal-gold { color: var(--theme-gold) !important; }
        
        /* Override Hardcoded Colors Throughout Theme */
        /* Green colors (#2D5A27, #4A7C43) */
        [style*="#2D5A27"] { color: var(--theme-primary) !important; }
        [style*="#4A7C43"] { color: var(--theme-secondary) !important; }
        /* Note: linear-gradient attribute selectors with * wildcard don't work in CSS - removed */
        [style*="background-color: #2D5A27"] { background-color: var(--theme-primary) !important; }
        [style*="background-color: #4A7C43"] { background-color: var(--theme-secondary) !important; }
        [style*="border-color: #2D5A27"] { border-color: var(--theme-primary) !important; }
        
        /* Gold color (#D4AF37) */
        [style*="#D4AF37"] { color: var(--theme-gold) !important; }
        [style*="background-color: #D4AF37"] { background-color: var(--theme-gold) !important; }
        [style*="border-color: #D4AF37"] { border-color: var(--theme-gold) !important; }
        
        /* Gradient Classes */
        .gradient-halal {
            background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-secondary) 100%);
        }
        .gradient-gold {
            background: linear-gradient(135deg, var(--theme-gold) 0%, #F4D03F 100%);
        }
        
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        .scroll-smooth {
            scroll-behavior: smooth;
        }
    </style>
    
    @include('partials.global-styles')
    @stack('styles')
</head>
<body class="bg-halal-cream scroll-smooth font-body">
    <!-- Header -->
    @include('themes.general.partials.header')
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('themes.general.partials.footer')
    
    <!-- Cart Sidebar -->
    @include('themes.general.partials.cart-sidebar')
    
    <!-- Wishlist Sidebar -->
    @include('themes.general.partials.wishlist-sidebar')
    
    <!-- Quick View Modal -->
    <div id="quick-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Quick View</h3>
                    <button onclick="$('#quick-view-modal').addClass('hidden').removeClass('flex').removeAttr('style');$('#quick-view-loading').addClass('hidden');$('#quick-view-content').html('');$('#quick-view-error').addClass('hidden');" class="text-gray-500 hover:text-gray-700">
                        <i class="bi bi-x-lg text-2xl"></i>
                    </button>
                </div>
                <div id="quick-view-loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-primary"></div>
                    <p class="text-gray-500 mt-3">Loading product...</p>
                </div>
                <div id="quick-view-error" class="hidden text-center py-12">
                    <i class="bi bi-exclamation-triangle text-4xl text-red-400"></i>
                    <p class="text-gray-600 mt-3">Failed to load product. Please try again.</p>
                </div>
                <div id="quick-view-content">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chat Widget -->
    @include('themes.general.partials.chat-widget')
    
    <!-- WhatsApp Widget -->
    @include('themes.general.partials.whatsapp-widget')
    
    <!-- Mobile Menu -->
    @include('themes.general.partials.mobile-menu')
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Disable browser scroll restoration to prevent auto-scrolling on page nav
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        
        // Cart functionality
        function updateCartCount() {
            $.get('{{ route("cart.count") }}', function(data) {
                $('.cart-count').text(Number(data.count) || 0);
            });
        }
        
        // Quick View function
        function quickView(productId) {
            $('#quick-view-loading').removeClass('hidden');
            $('#quick-view-content').html('');
            $('#quick-view-error').addClass('hidden');
            $('#quick-view-modal').removeClass('hidden').addClass('flex');
            $.ajax({
                url: '/api/products/quick-view/' + productId,
                method: 'GET',
                success: function(response) {
                    $('#quick-view-loading').addClass('hidden');
                    $('#quick-view-content').html(response);
                },
                error: function() {
                    $('#quick-view-loading').addClass('hidden');
                    $('#quick-view-error').removeClass('hidden');
                    if (typeof showToast === 'function') {
                        showToast('Failed to load product', 'error');
                    }
                }
            });
        }
        
        // Add to Wishlist function
        function addToWishlist(productId) {
            $.ajax({
                url: '/api/wishlist/toggle',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        // Update wishlist count
                        updateWishlistCount(response.added ? 1 : -1);
                        // Update button state if exists
                        var btn = $('.wishlist-btn-' + productId);
                        if (btn.length) {
                            if (response.added) {
                                btn.addClass('bg-red-500 text-white').removeClass('text-gray-900 bg-white');
                                btn.find('svg').attr('fill', 'currentColor');
                            } else {
                                btn.removeClass('bg-red-500 text-white').addClass('text-gray-900 bg-white');
                                btn.find('svg').attr('fill', 'none');
                            }
                        }
                    } else if (response.login_required) {
                        showToast(response.message, 'error');
                        setTimeout(function() {
                            window.location.href = '{{ route("login") }}';
                        }, 1500);
                    }
                },
                error: function() {
                    showToast('Please login to add to wishlist', 'error');
                }
            });
        }
        
        // Update wishlist count in header
        function updateWishlistCount(change) {
            var countEl = $('.wishlist-count');
            var currentCount = parseInt(countEl.first().text()) || 0;
            var newCount = currentCount + change;
            countEl.text(newCount);
            if (newCount <= 0) {
                countEl.addClass('hidden');
            } else {
                countEl.removeClass('hidden');
            }
        }
        
        $(document).ready(function() {
            updateCartCount();
            
            // Toast notification
            window.showToast = function(message, type = 'info') {
                var toast = $('<div class="fixed bottom-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white ' + 
                    (type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600') + 
                    ' transform transition-all duration-300 translate-x-full">' + message + '</div>');
                $('body').append(toast);
                setTimeout(() => toast.removeClass('translate-x-full'), 100);
                setTimeout(() => {
                    toast.addClass('translate-x-full');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            };

            // Newsletter AJAX handler for global forms (footer + widget)
            $(document).on('submit', '.newsletter-form:not(#contactNewsletterForm)', function(e) {
                e.preventDefault();
                var form = $(this);
                var email = form.find('input[name="email"]').val().trim();
                
                // Client-side email validation (must have a TLD like .com, .org)
                if (!/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/.test(email)) {
                    showToast('Please enter a valid email address (e.g., name@example.com)', 'error');
                    return;
                }
                
                var btn = form.find('button[type="submit"]');
                var originalText = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Subscribing...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    success: function(data) {
                        showToast(data.message, data.type || 'success');
                        form.find('input[name="email"]').val('');
                    },
                    error: function() {
                        showToast('Something went wrong. Please try again.', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('scripts')
</body>
</html>
