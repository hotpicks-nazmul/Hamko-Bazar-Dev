@extends('themes.general.layouts.app')

@section('title', 'About Us')

@push('styles')
<style>
.page-hero {
    background: linear-gradient(135deg, #2D5A27 0%, #4A7C43 100%);
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    z-index: 0;
}
.page-hero .container { position: relative; z-index: 1; }
.feature-card { border: none; border-radius: 16px; padding: 32px 24px; transition: all 0.3s ease; background: #fff; height: 100%; }
.feature-card:hover { transform: translateY(-6px); box-shadow: 0 20px 50px rgba(0,0,0,0.1); }
.feature-icon { width: 72px; height: 72px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 20px; }
.section-badge { display: inline-block; padding: 6px 20px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; background: rgba(45,90,39,0.1); color: #2D5A27; margin-bottom: 12px; }
.stat-number { font-size: 2.5rem; font-weight: 800; line-height: 1.1; }
.stat-label { font-size: 0.9rem; color: #6b7280; font-weight: 500; }
.value-card { border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px 24px; background: #fff; transition: all 0.3s ease; height: 100%; text-align: center; }
.value-card:hover { border-color: #2D5A27; box-shadow: 0 8px 30px rgba(45,90,39,0.1); }
.value-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin: 0 auto 16px; }
.story-card { border: none; border-radius: 16px; background: #fff; box-shadow: 0 4px 24px rgba(0,0,0,0.06); }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="page-hero text-white text-center">
    <div class="max-w-7xl px-4" style="margin: 0 auto;">
        <span class="section-badge" style="background: rgba(255,255,255,0.15); color: #fff;">About Company</span>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-3">About Hamko Bazar</h1>
        <p class="text-lg opacity-75 mx-auto" style="max-width: 600px;">
            Discover the story behind Bangladesh's trusted destination for premium houseware, cookware, furniture and consumer goods.
        </p>
    </div>
</section>

<!-- Our Story -->
<section class="py-16" style="background: #fff;">
    <div class="max-w-7xl px-4" style="margin: 0 auto;">
        <div class="flex flex-wrap items-center" style="gap: 60px;">
            <div class="w-full lg:w-5/12">
                <div class="relative flex items-center justify-center h-full" style="min-height: 420px;">
                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=700&q=80"
                         alt="Hamko Bazar" class="w-full rounded-2xl shadow-lg" style="object-fit: cover; min-height: 420px;">
                    <div class="hidden lg:block absolute" style="bottom: -10px; right: -10px;">
                        <div class="bg-white rounded-xl shadow-lg p-4" style="border-left: 4px solid #2D5A27;">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-buildings text-green-600 text-2xl"></i>
                                <div>
                                    <div class="font-bold text-gray-900">HAMKO INDUSTRIES LTD</div>
                                    <small class="text-gray-500">Since 2024</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-6/12">
                <span class="section-badge">Our Story</span>
                <h2 class="font-bold mb-4 text-3xl">A Trusted Name in <span style="color: #2D5A27;">Quality Products</span></h2>
                @if($page)
                    <div class="text-gray-500 leading-relaxed" style="font-size: 1rem; line-height: 1.8;">
                        {!! class_exists('Purifier') ? Purifier::clean($page->content) : $page->content !!}
                    </div>
                @else
                    <p class="text-gray-500 mb-4 leading-relaxed" style="font-size: 1rem; line-height: 1.8;">
                        Hamko Bazar — the concern of <strong>HAMKO INDUSTRIES LTD (HIL)</strong> — is a service-oriented
                        e-commerce business platform where quality meets convenience. We bring together a carefully curated
                        selection of houseware, cookware, furniture, and essential consumer items, all in one place.
                    </p>
                    <p class="text-gray-500 mb-4 leading-relaxed" style="line-height: 1.8;">
                        Our journey began with a simple vision: to make premium home and kitchen products accessible to
                        every family in Bangladesh. From sturdy cookware that lasts generations to elegant furniture
                        that transforms living spaces, every product in our catalog is chosen for its quality, durability, and value.
                    </p>
                    <p class="text-gray-500 mb-4 leading-relaxed" style="line-height: 1.8;">
                        We work directly with trusted manufacturers and suppliers to ensure that every item meets our
                        rigorous quality standards. From our warehouse to your doorstep, we maintain careful handling
                        and efficient delivery to bring you the best shopping experience.
                    </p>
                    <div class="flex flex-wrap gap-x-6 gap-y-3 mt-6">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-circle-fill text-green-600"></i>
                            <span class="text-gray-900 font-medium">Certified Quality</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-circle-fill text-green-600"></i>
                            <span class="text-gray-900 font-medium">Best Price Guarantee</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-circle-fill text-green-600"></i>
                            <span class="text-gray-900 font-medium">24/7 Customer Support</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Stats Counter -->
<section class="py-12" style="background: linear-gradient(135deg, #2D5A27 0%, #4A7C43 100%);">
    <div class="max-w-7xl px-4" style="margin: 0 auto;">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center text-white">
            <div><div class="stat-number">163+</div><div class="text-white/60 text-sm font-medium">Houseware Items</div></div>
            <div><div class="stat-number">235+</div><div class="text-white/60 text-sm font-medium">Cookware Products</div></div>
            <div><div class="stat-number">40+</div><div class="text-white/60 text-sm font-medium">Furniture Collection</div></div>
            <div><div class="stat-number">100%</div><div class="text-white/60 text-sm font-medium">Satisfaction</div></div>
        </div>
    </div>
</section>

<!-- Core Values -->
<section class="py-16" style="background: #f8faf8;">
    <div class="max-w-7xl px-4" style="margin: 0 auto;">
        <div class="text-center mb-10">
            <span class="section-badge">Our Values</span>
            <h2 class="font-bold text-3xl mb-3">What Sets Us Apart</h2>
            <p class="text-gray-500 mx-auto" style="max-width: 550px;">We believe in quality, trust, and exceptional service. These core values drive everything we do.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="value-card">
                <div class="value-icon" style="background: rgba(45,90,39,0.1); color: #2D5A27;"><i class="bi bi-shield-check"></i></div>
                <h6 class="font-bold mb-2">Quality Assurance</h6>
                <p class="text-gray-500 text-sm mb-0">Every product is carefully inspected before reaching you</p>
            </div>
            <div class="value-card">
                <div class="value-icon" style="background: rgba(212,175,55,0.15); color: #b8962e;"><i class="bi bi-truck"></i></div>
                <h6 class="font-bold mb-2">Fast Delivery</h6>
                <p class="text-gray-500 text-sm mb-0">Quick and reliable delivery across Bangladesh</p>
            </div>
            <div class="value-card">
                <div class="value-icon" style="background: rgba(45,90,39,0.1); color: #2D5A27;"><i class="bi bi-currency-dollar"></i></div>
                <h6 class="font-bold mb-2">Best Prices</h6>
                <p class="text-gray-500 text-sm mb-0">Competitive pricing with regular deals and offers</p>
            </div>
            <div class="value-card">
                <div class="value-icon" style="background: rgba(212,175,55,0.15); color: #b8962e;"><i class="bi bi-headset"></i></div>
                <h6 class="font-bold mb-2">24/7 Support</h6>
                <p class="text-gray-500 text-sm mb-0">We're always here to help with any questions</p>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-16" style="background: #fff;">
    <div class="max-w-7xl px-4" style="margin: 0 auto;">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="feature-card text-center">
                <div class="feature-icon mx-auto" style="background: rgba(45,90,39,0.1);"><i class="bi bi-patch-check-fill" style="color: #2D5A27;"></i></div>
                <h5 class="font-bold mb-3">100% Quality Certified</h5>
                <p class="text-gray-500 mb-0">All products meet our stringent quality standards before reaching our customers.</p>
            </div>
            <div class="feature-card text-center">
                <div class="feature-icon mx-auto" style="background: rgba(212,175,55,0.15);"><i class="bi bi-box-seam-fill" style="color: #b8962e;"></i></div>
                <h5 class="font-bold mb-3">Secure Packaging</h5>
                <p class="text-gray-500 mb-0">Every order is carefully packed to ensure safe delivery in perfect condition.</p>
            </div>
            <div class="feature-card text-center">
                <div class="feature-icon mx-auto" style="background: rgba(45,90,39,0.1);"><i class="bi bi-arrow-repeat" style="color: #2D5A27;"></i></div>
                <h5 class="font-bold mb-3">Easy Returns</h5>
                <p class="text-gray-500 mb-0">Not satisfied? We offer hassle-free returns and exchanges within the policy period.</p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-16" style="background: #f8faf8;">
    <div class="max-w-7xl px-4" style="margin: 0 auto;">
        <div class="flex flex-wrap justify-center gap-6">
            <div class="w-full lg:w-5/12">
                <div class="story-card p-6 md:p-10 h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(45,90,39,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-bullseye" style="color: #2D5A27; font-size: 1.4rem;"></i>
                        </div>
                        <h4 class="font-bold mb-0 text-xl">Our Mission</h4>
                    </div>
                    <p class="text-gray-500 mb-0 leading-relaxed">
                        To provide Bangladeshi families with premium-quality houseware, cookware, furniture and
                        consumer essentials at the best values, delivered with care and backed by exceptional
                        customer service. We strive to make every home more beautiful and functional.
                    </p>
                </div>
            </div>
            <div class="w-full lg:w-5/12">
                <div class="story-card p-6 md:p-10 h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212,175,55,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-eye" style="color: #b8962e; font-size: 1.4rem;"></i>
                        </div>
                        <h4 class="font-bold mb-0 text-xl">Our Vision</h4>
                    </div>
                    <p class="text-gray-500 mb-0 leading-relaxed">
                        To become Bangladesh's most trusted online destination for home and kitchen products,
                        known for uncompromising quality, reliable service, and a seamless shopping experience
                        that customers can depend on every day.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
