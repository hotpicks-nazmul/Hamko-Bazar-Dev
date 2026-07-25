<!-- Footer -->
@php
    // Footer Settings - get all footer settings at once for efficiency
    $footerSettings = \App\Models\Setting::where('group', 'footer')->pluck('value', 'key')->toArray();
    
    $footerAbout = $footerSettings['footer_about_text'] ?? 'Your trusted source for premium quality halal meat, poultry, seafood, and groceries. We deliver fresh across Bangladesh.';
    $footerFacebook = $footerSettings['footer_facebook_url'] ?? '';
    $footerInstagram = $footerSettings['footer_instagram_url'] ?? '';
    $footerYoutube = $footerSettings['footer_youtube_url'] ?? '';
    $footerTwitter = $footerSettings['footer_twitter_url'] ?? '';
    $footerLinkedin = $footerSettings['footer_linkedin_url'] ?? '';
    $footerAddress = $footerSettings['footer_address'] ?? '123 Green Market Road, Dhaka-1205, Bangladesh';
    $footerPhone = $footerSettings['footer_phone'] ?? '+880 1700-000000';
    $footerEmail = $footerSettings['footer_email'] ?? 'info@halalfoodstore.com';
    $footerHours = $footerSettings['footer_business_hours'] ?? 'Sat - Thu: 8AM - 10PM';
    $footerCopyright = $footerSettings['footer_copyright_text'] ?? '';
    $newsletterEnabled = $footerSettings['footer_newsletter_enabled'] ?? '1';
    $newsletterTitle = $footerSettings['footer_newsletter_title'] ?? 'Subscribe to Our Newsletter';
    $newsletterSubtitle = $footerSettings['footer_newsletter_subtitle'] ?? 'Get updates on new products, special offers, and halal food tips!';
    $column1Title = $footerSettings['footer_column1_title'] ?? 'Quick Links';
    $column2Title = $footerSettings['footer_column2_title'] ?? 'Customer Service';
    $column3Title = $footerSettings['footer_column3_title'] ?? 'Contact Us';
    $paymentMethods = $footerSettings['footer_payment_methods'] ?? 'bkash,nagad,rocket,visa,mastercard';
    $showPaymentIcons = $footerSettings['footer_show_payment_icons'] ?? '1';
    
    $siteName = \App\Models\Setting::where('key', 'site_name')->value('value') ?? 'Halal Food Store';
    $siteLogoImage = \App\Models\Setting::where('group', 'homepage')->where('key', 'site_logo')->value('value') ?? '';
    $siteLogoIcon = \App\Models\Setting::where('group', 'homepage')->where('key', 'site_logo_icon')->value('value') ?? 'bi bi-shop';
    
    // Check if any social link is set
    $hasSocialLinks = !empty($footerFacebook) || !empty($footerInstagram) || !empty($footerYoutube) || !empty($footerTwitter) || !empty($footerLinkedin);
@endphp

<footer class="bg-halal-dark text-white">
    <!-- Newsletter Section - Hidden on blog pages -->
    @if(!request()->routeIs('blogs.*') && $newsletterEnabled === '1')
    <div class="gradient-halal py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <h3 class="font-poppins text-2xl font-bold">{{ $newsletterTitle }}</h3>
                    <p class="text-green-100">{{ $newsletterSubtitle }}</p>
                </div>
                <form class="newsletter-form flex w-full md:w-auto" action="{{ route('newsletter.subscribe') }}" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="Enter your email" required
                        class="px-4 py-3 rounded-l-full w-full md:w-64 focus:outline-none text-gray-700">
                    <button type="submit" class="bg-halal-gold text-white px-6 py-3 rounded-r-full hover:bg-yellow-600 transition-colors font-medium">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Main Footer -->
    <div class="py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- About -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        @php
                            $footerLogoPath = trim($siteLogoImage ?? '');
                            $footerHasLogo = !empty($footerLogoPath) && $footerLogoPath !== '';
                        @endphp
                        @if($footerHasLogo)
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center overflow-hidden flex-shrink-0">
                                <img src="{{ $footerLogoPath }}" alt="{{ $siteName }}" class="h-8 w-auto object-contain">
                            </div>
                        @else
                        <div class="w-10 h-10 bg-halal-gold rounded-full flex items-center justify-center">
                            <i class="bi {{ $siteLogoIcon }} text-white"></i>
                        </div>
                        @endif
                        <span class="font-poppins text-xl font-bold">{{ $siteName }}</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        {{ $footerAbout }}
                    </p>
                    @if($hasSocialLinks)
                    <div class="flex space-x-3">
                        @if(!empty($footerFacebook))
                        <a href="{{ $footerFacebook }}" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors">
                            <i class="bi bi-facebook"></i>
                        </a>
                        @endif
                        @if(!empty($footerInstagram))
                        <a href="{{ $footerInstagram }}" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-pink-600 transition-colors">
                            <i class="bi bi-instagram"></i>
                        </a>
                        @endif
                        @if(!empty($footerYoutube))
                        <a href="{{ $footerYoutube }}" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="bi bi-youtube"></i>
                        </a>
                        @endif
                        @if(!empty($footerTwitter))
                        <a href="{{ $footerTwitter }}" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-sky-500 transition-colors">
                            <i class="bi bi-twitter"></i>
                        </a>
                        @endif
                        @if(!empty($footerLinkedin))
                        <a href="{{ $footerLinkedin }}" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="font-poppins text-lg font-bold mb-4 text-halal-gold">{{ $column1Title }}</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>Home</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>Shop</a></li>
                        <li><a href="{{ route('products.index', ['sort' => 'newest']) }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>New Arrivals</a></li>
                        <li><a href="{{ route('products.index', ['sort' => 'discount']) }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>Deals & Offers</a></li>
                        <li><a href="{{ route('blogs.index') }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>Blog</a></li>
                    </ul>
                </div>
                
                <!-- Customer Service -->
                <div>
                    <h4 class="font-poppins text-lg font-bold mb-4 text-halal-gold">{{ $column2Title }}</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('pages.contact') }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>Contact Us</a></li>
                        <li><a href="{{ route('about') }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>About Us</a></li>
                        <li><a href="{{ route('faq') }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>FAQ</a></li>
                        <li><a href="{{ route('terms') }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>Terms & Conditions</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-gray-400 hover:text-white transition-colors"><i class="bi bi-chevron-right text-halal-green mr-2"></i>Privacy Policy</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="font-poppins text-lg font-bold mb-4 text-halal-gold">{{ $column3Title }}</h4>
                    <ul class="space-y-3">
                        @if($footerAddress)
                        <li class="flex items-start">
                            <i class="bi bi-geo-alt-fill text-halal-green mt-1 mr-3"></i>
                            <span class="text-gray-400">{!! nl2br(e($footerAddress)) !!}</span>
                        </li>
                        @endif
                        @if($footerPhone)
                        <li class="flex items-center">
                            <i class="bi bi-telephone-fill text-halal-green mr-3"></i>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $footerPhone) }}" class="text-gray-400 hover:text-white transition-colors">{{ $footerPhone }}</a>
                        </li>
                        @endif
                        @if($footerEmail)
                        <li class="flex items-center">
                            <i class="bi bi-envelope-fill text-halal-green mr-3"></i>
                            <a href="mailto:{{ $footerEmail }}" class="text-gray-400 hover:text-white transition-colors">{{ $footerEmail }}</a>
                        </li>
                        @endif
                        @if($footerHours)
                        <li class="flex items-center">
                            <i class="bi bi-clock-fill text-halal-green mr-3"></i>
                            <span class="text-gray-400">{{ $footerHours }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Methods -->
    @if($showPaymentIcons === '1')
    <div class="border-t border-gray-700 py-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <span class="text-gray-400 mr-2">We Accept:</span>
                    <span class="inline-flex items-center space-x-2">
                        @php
                            $methods = array_map('trim', explode(',', $paymentMethods));
                        @endphp
                        @if(in_array('bkash', $methods))
                        <span class="bg-pink-600 text-white px-2 py-1 rounded text-xs font-bold">bKash</span>
                        @endif
                        @if(in_array('nagad', $methods))
                        <span class="bg-orange-500 text-white px-2 py-1 rounded text-xs font-bold">Nagad</span>
                        @endif
                        @if(in_array('rocket', $methods))
                        <span class="bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold">Rocket</span>
                        @endif
                        @if(in_array('visa', $methods))
                        <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs font-bold">Visa</span>
                        @endif
                        @if(in_array('mastercard', $methods))
                        <span class="bg-red-600 text-white px-2 py-1 rounded text-xs font-bold">Mastercard</span>
                        @endif
                        @if(in_array('amex', $methods))
                        <span class="bg-blue-500 text-white px-2 py-1 rounded text-xs font-bold">Amex</span>
                        @endif
                        @if(in_array('paypal', $methods))
                        <span class="bg-blue-700 text-white px-2 py-1 rounded text-xs font-bold">PayPal</span>
                        @endif
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <img src="https://img.icons8.com/color/48/verified-badge.png" alt="Verified" class="h-8 opacity-70">
                    <span class="text-gray-400 text-sm">100% Secure Payments</span>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Copyright -->
    <div class="bg-black py-4">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between text-sm text-gray-500">
                <p>
                    @if($footerCopyright)
                        {{ $footerCopyright }}
                    @else
                        &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.
                    @endif
                </p>
                <p class="mt-2 md:mt-0">
                    Made with <i class="bi bi-heart-fill text-red-500"></i> in Bangladesh
                </p>
            </div>
        </div>
    </div>
</footer>
