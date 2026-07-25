@extends('themes.general.layouts.app')

@section('title', 'Privacy Policy')

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
.policy-card { background: #fff; border-radius: 20px; padding: 48px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); }
.policy-card h5 { color: #2D5A27; margin-top: 2.5rem; margin-bottom: 1rem; font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
.policy-card h5:first-child { margin-top: 0; }
.policy-card p { color: #4b5563; line-height: 1.9; margin-bottom: 1rem; font-size: 0.95rem; }
.policy-card ul { padding-left: 1.5rem; margin-bottom: 1.2rem; }
.policy-card ul li { color: #4b5563; line-height: 1.8; margin-bottom: 6px; font-size: 0.95rem; padding-left: 8px; }
.policy-card ul li::marker { color: #2D5A27; }
.section-number { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: rgba(45,90,39,0.1); color: #2D5A27; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; }
.toc-card { background: #f0f7ef; border-radius: 14px; padding: 24px 28px; border: 1px solid rgba(45,90,39,0.15); margin-bottom: 32px; }
.toc-card a { color: #2D5A27; text-decoration: none; font-weight: 500; }
.toc-card a:hover { text-decoration: underline; }
.toc-card ul { list-style: none; padding: 0; margin: 0; }
.toc-card ul li { padding: 6px 0; border-bottom: 1px solid rgba(45,90,39,0.08); }
.toc-card ul li:last-child { border-bottom: none; }
.highlight-box { background: #f0f7ef; border-radius: 12px; padding: 20px 24px; border-left: 4px solid #2D5A27; margin-bottom: 1.5rem; }
</style>
@endpush

@section('content')
<section class="page-hero text-white text-center">
    <div class="max-w-7xl px-4" style="margin: 0 auto;">
        <p class="mb-2 text-sm">
            <a href="{{ url('/') }}" class="text-white/70 hover:text-white no-underline">Home</a>
            <span class="mx-2 text-white/40">/</span>
            <span class="text-white">Privacy Policy</span>
        </p>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-3">Privacy Policy</h1>
        <p class="text-lg opacity-75 mx-auto" style="max-width: 550px;">How we handle your information</p>
    </div>
</section>

<section class="py-16" style="background: #f8faf8;">
    <div class="max-w-4xl px-4" style="margin: 0 auto;">
        <div class="policy-card">

            <div class="toc-card">
                <div class="flex items-center gap-2 mb-3">
                    <i class="bi bi-shield-check" style="color: #2D5A27;"></i>
                    <h6 class="font-bold mb-0" style="color: #2D5A27;">On this page</h6>
                </div>
                <ul>
                    <li><a href="#section1">1. Information We Collect</a></li>
                    <li><a href="#section2">2. How We Use Your Information</a></li>
                    <li><a href="#section3">3. Information Sharing</a></li>
                    <li><a href="#section4">4. Data Security</a></li>
                    <li><a href="#section5">5. Cookies &amp; Tracking</a></li>
                    <li><a href="#section6">6. Your Rights</a></li>
                    <li><a href="#section7">7. Children's Privacy</a></li>
                    <li><a href="#section8">8. Third-Party Links</a></li>
                    <li><a href="#section9">9. Changes to Policy</a></li>
                    <li><a href="#section10">10. Contact Us</a></li>
                </ul>
            </div>

            <div class="highlight-box">
                <div class="flex items-center gap-2 mb-1">
                    <i class="bi bi-info-circle-fill" style="color: #2D5A27;"></i>
                    <strong style="color: #2D5A27;">Our Commitment</strong>
                </div>
                <p class="mb-0 text-sm">At Hamko Bazar, your privacy matters to us. We are committed to protecting your data and being transparent about our practices.</p>
            </div>

            @if($page)
                {!! class_exists('Purifier') ? Purifier::clean($page->content) : $page->content !!}
            @else
                <h5 id="section1"><span class="section-number">1</span> Information We Collect</h5>
                <p>We collect information that you provide directly to us:</p>
                <ul>
                    <li><strong>Account Information:</strong> Your name, email address, phone number, and delivery address</li>
                    <li><strong>Payment Information:</strong> Transaction details (full payment details are handled by secure gateways)</li>
                    <li><strong>Order History:</strong> Records of products purchased and preferences</li>
                    <li><strong>Device Information:</strong> IP address, browser type, and operating system for analytics</li>
                </ul>

                <h5 id="section2"><span class="section-number">2</span> How We Use Your Information</h5>
                <ul>
                    <li><strong>Order Processing:</strong> To process and deliver your orders and provide updates</li>
                    <li><strong>Customer Support:</strong> To respond to inquiries and resolve issues</li>
                    <li><strong>Service Improvement:</strong> To analyze usage and improve our website and products</li>
                    <li><strong>Communications:</strong> To send order updates and promotional offers (with your consent)</li>
                    <li><strong>Security:</strong> To detect and prevent fraudulent transactions</li>
                </ul>

                <h5 id="section3"><span class="section-number">3</span> Information Sharing</h5>
                <p>We <strong>do not sell</strong> your personal information. We may share with:</p>
                <ul>
                    <li>Delivery partners for order fulfillment</li>
                    <li>Payment processors for secure transactions</li>
                    <li>Legal authorities when required by law</li>
                    <li>Trusted service providers bound by data protection agreements</li>
                </ul>

                <h5 id="section4"><span class="section-number">4</span> Data Security</h5>
                <ul>
                    <li>SSL/TLS encryption for all data transmitted</li>
                    <li>Strict access controls for authorized personnel only</li>
                    <li>Secure servers with industry-standard protection</li>
                    <li>Regular security audits to maintain high standards</li>
                </ul>

                <h5 id="section5"><span class="section-number">5</span> Cookies &amp; Tracking</h5>
                <p>We use cookies to enhance your experience. Cookies help us remember your preferences, analyze traffic, and personalize content. You can control cookie settings in your browser.</p>

                <h5 id="section6"><span class="section-number">6</span> Your Rights</h5>
                <ul>
                    <li><strong>Access</strong> — Request a copy of your personal data</li>
                    <li><strong>Correction</strong> — Request correction of inaccurate information</li>
                    <li><strong>Deletion</strong> — Request deletion of your data (subject to legal obligations)</li>
                    <li><strong>Opt-Out</strong> — Unsubscribe from marketing communications at any time</li>
                </ul>

                <h5 id="section7"><span class="section-number">7</span> Children's Privacy</h5>
                <p>Our services are not intended for children under <strong>13</strong>. We do not knowingly collect information from children. If we become aware of such data, we will delete it promptly.</p>

                <h5 id="section8"><span class="section-number">8</span> Third-Party Links</h5>
                <p>Our website may contain links to third-party sites. We are not responsible for their privacy practices. Please review their policies before providing personal information.</p>

                <h5 id="section9"><span class="section-number">9</span> Changes to Policy</h5>
                <p>We may update this Privacy Policy periodically. Changes will be posted on this page with an updated "Last updated" date.</p>

                <h5 id="section10"><span class="section-number">10</span> Contact Us</h5>
                <p>For questions or concerns about this policy:</p>
                <div class="bg-gray-50 rounded-xl p-5 mt-2">
                    <div class="flex items-center gap-3 mb-2"><i class="bi bi-envelope-fill" style="color: #2D5A27;"></i><strong>Email:</strong> hamkobazar@gmail.com</div>
                    <div class="flex items-center gap-3 mb-2"><i class="bi bi-telephone-fill" style="color: #2D5A27;"></i><strong>Phone:</strong> +880 01766-664488</div>
                    <div class="flex items-center gap-3"><i class="bi bi-building-fill" style="color: #2D5A27;"></i><strong>Office:</strong> HAMKO INDUSTRIES LTD, Dhaka, Bangladesh</div>
                </div>
            @endif

            <div class="text-center mt-10 pt-5 border-t border-gray-200">
                <p class="text-gray-500 text-sm"><i class="bi bi-calendar-event me-1"></i>Last updated: {{ date('F d, Y') }}</p>
            </div>
        </div>
    </div>
</section>
@endsection
