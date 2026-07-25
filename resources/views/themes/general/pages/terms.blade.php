@extends('themes.general.layouts.app')

@section('title', 'Terms of Service')

@push('styles')
<style>
.page-hero {
    background: linear-gradient(135deg, #2D5A27 0%, #4A7C43 100%);
    padding: 80px 0; position: relative; overflow: hidden;
}
.page-hero::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); z-index: 0;
}
.terms-card { background: #fff; border-radius: 20px; padding: 48px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); }
.terms-card h5 { color: #2D5A27; margin-top: 2.5rem; margin-bottom: 1rem; font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
.terms-card h5:first-child { margin-top: 0; }
.terms-card p { color: #4b5563; line-height: 1.9; margin-bottom: 1rem; font-size: 0.95rem; }
.terms-card ul { padding-left: 1.5rem; margin-bottom: 1.2rem; }
.terms-card ul li { color: #4b5563; line-height: 1.8; margin-bottom: 6px; font-size: 0.95rem; padding-left: 8px; }
.terms-card ul li::marker { color: #2D5A27; }
.section-number { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: rgba(45,90,39,0.1); color: #2D5A27; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; }
.toc-card { background: #f0f7ef; border-radius: 14px; padding: 24px 28px; border: 1px solid rgba(45,90,39,0.15); margin-bottom: 32px; }
.toc-card a { color: #2D5A27; text-decoration: none; font-weight: 500; }
.toc-card a:hover { text-decoration: underline; }
.toc-card ul { list-style: none; padding: 0; margin: 0; }
.toc-card ul li { padding: 6px 0; border-bottom: 1px solid rgba(45,90,39,0.08); }
.toc-card ul li:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
<section class="page-hero text-white text-center">
    <div class="max-w-7xl px-4" style="margin: 0 auto;">
        <p class="mb-2 text-sm">
            <a href="{{ url('/') }}" class="text-white/70 hover:text-white no-underline">Home</a>
            <span class="mx-2 text-white/40">/</span>
            <span class="text-white">Terms of Service</span>
        </p>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-3">Terms of Service</h1>
        <p class="text-lg opacity-75 mx-auto" style="max-width: 550px;">Please read these terms carefully</p>
    </div>
</section>

<section class="py-16" style="background: #f8faf8;">
    <div class="max-w-4xl px-4" style="margin: 0 auto;">
        <div class="terms-card">

            <div class="toc-card">
                <div class="flex items-center gap-2 mb-3">
                    <i class="bi bi-list-columns-reverse" style="color: #2D5A27;"></i>
                    <h6 class="font-bold mb-0" style="color: #2D5A27;">On this page</h6>
                </div>
                <ul>
                    <li><a href="#section1">1. Introduction</a></li>
                    <li><a href="#section2">2. Products &amp; Services</a></li>
                    <li><a href="#section3">3. Orders &amp; Payments</a></li>
                    <li><a href="#section4">4. Delivery &amp; Shipping</a></li>
                    <li><a href="#section5">5. Returns &amp; Refunds</a></li>
                    <li><a href="#section6">6. Account Security</a></li>
                    <li><a href="#section7">7. Intellectual Property</a></li>
                    <li><a href="#section8">8. Limitation of Liability</a></li>
                    <li><a href="#section9">9. Privacy</a></li>
                    <li><a href="#section10">10. Changes to Terms</a></li>
                    <li><a href="#section11">11. Contact</a></li>
                </ul>
            </div>

            @if($page)
                {!! class_exists('Purifier') ? Purifier::clean($page->content) : $page->content !!}
            @else
                <h5 id="section1"><span class="section-number">1</span> Introduction</h5>
                <p>Welcome to <strong>Hamko Bazar</strong> (the concern of <strong>HAMKO INDUSTRIES LTD (HIL)</strong>). By accessing and using our website at <strong>hamkobazar.com</strong>, you agree to be bound by these Terms of Service.</p>
                <p>These terms govern your use of our e-commerce platform, including browsing products, placing orders, making payments, and receiving deliveries. We reserve the right to update these terms at any time.</p>

                <h5 id="section2"><span class="section-number">2</span> Products &amp; Services</h5>
                <p>Hamko Bazar offers a wide range of products including <strong>houseware, cookware, furniture, and consumer goods</strong> for online purchase and delivery across Bangladesh.</p>

                <h5 id="section3"><span class="section-number">3</span> Orders &amp; Payments</h5>
                <ul>
                    <li>All orders are subject to product availability and acceptance by Hamko Bazar</li>
                    <li>Prices are listed in Bangladeshi Taka (&#2547;) and may change without notice</li>
                    <li>We accept bKash, Nagad, Rocket, credit/debit cards, and Cash on Delivery (where available)</li>
                </ul>

                <h5 id="section4"><span class="section-number">4</span> Delivery &amp; Shipping</h5>
                <ul>
                    <li>Delivery times are estimates — factors like weather and traffic may cause delays</li>
                    <li>Free delivery for orders over &#2547;1,000 within Dhaka</li>
                    <li>A responsible person must be available at the delivery address to receive the order</li>
                </ul>

                <h5 id="section5"><span class="section-number">5</span> Returns &amp; Refunds</h5>
                <p>If you receive a <strong>damaged, defective, or incorrect product</strong>, please contact us within <strong>2 hours of delivery</strong>. We will arrange for a replacement or full refund.</p>

                <h5 id="section6"><span class="section-number">6</span> Account Security</h5>
                <p>You are responsible for maintaining the confidentiality of your account login credentials and for all activities that occur under your account.</p>

                <h5 id="section7"><span class="section-number">7</span> Intellectual Property</h5>
                <p>All content on this website is the property of <strong>HAMKO INDUSTRIES LTD</strong> or its licensors and is protected by applicable intellectual property laws.</p>

                <h5 id="section8"><span class="section-number">8</span> Limitation of Liability</h5>
                <p>Hamko Bazar and HAMKO INDUSTRIES LTD shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of our services.</p>

                <h5 id="section9"><span class="section-number">9</span> Privacy</h5>
                <p>Your privacy is important to us. Please review our <a href="{{ route('privacy') }}" style="color: #2D5A27; text-decoration: underline;">Privacy Policy</a> to understand how we collect, use, and protect your personal information.</p>

                <h5 id="section10"><span class="section-number">10</span> Changes to Terms</h5>
                <p>We reserve the right to modify or update these Terms of Service at any time without prior notice. Changes become effective immediately upon posting.</p>

                <h5 id="section11"><span class="section-number">11</span> Contact</h5>
                <p>For questions regarding these Terms of Service, please reach out:</p>
                <div style="background: #f9fafb; border-radius: 12px; padding: 20px 24px; margin-top: 8px;">
                    <div class="flex items-center gap-3 mb-2"><i class="bi bi-envelope" style="color: #2D5A27;"></i><span>hamkobazar@gmail.com</span></div>
                    <div class="flex items-center gap-3 mb-2"><i class="bi bi-telephone" style="color: #2D5A27;"></i><span>+880 01766-664488</span></div>
                    <div class="flex items-center gap-3"><i class="bi bi-building" style="color: #2D5A27;"></i><span>HAMKO INDUSTRIES LTD, Dhaka, Bangladesh</span></div>
                </div>
            @endif

            <div class="text-center mt-10 pt-5 border-t border-gray-200">
                <p class="text-gray-500 text-sm"><i class="bi bi-calendar-event me-1"></i>Last updated: {{ date('F d, Y') }}</p>
            </div>
        </div>
    </div>
</section>
@endsection
