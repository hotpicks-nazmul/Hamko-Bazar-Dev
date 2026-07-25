@extends('themes.general.layouts.app')

@section('title', 'FAQ')

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
.accordion-item {
    border: none;
    margin-bottom: 14px;
    border-radius: 14px !important;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    background: #fff;
}
.accordion-button {
    font-weight: 600;
    padding: 20px 24px;
    background: #fff;
    font-size: 1.02rem;
    color: #1f2937;
    width: 100%;
    text-align: left;
    border: none;
    display: flex;
    align-items: center;
    gap: 12px;
}
.accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, #2D5A27 0%, #4A7C43 100%);
    color: #fff;
}
.accordion-button:focus { box-shadow: none; outline: none; }
.accordion-body {
    padding: 20px 24px;
    background: #fff;
    font-size: 0.95rem;
    line-height: 1.8;
    color: #4b5563;
}
.cta-card {
    border: none;
    border-radius: 20px;
    background: linear-gradient(135deg, #2D5A27 0%, #4A7C43 100%);
    padding: 50px 40px;
    text-align: center;
    color: #fff;
}
.search-box {
    border-radius: 50px;
    border: 2px solid #e5e7eb;
    padding: 12px 20px 12px 48px;
    width: 100%;
    font-size: 0.95rem;
    transition: all 0.3s;
    background: #fff;
}
.search-box:focus { border-color: #2D5A27; outline: none; box-shadow: 0 0 0 3px rgba(45,90,39,0.1); }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="page-hero text-white text-center">
    <div class="max-w-7xl px-4" style="margin: 0 auto;">
        <p class="mb-2 text-sm">
            <a href="{{ url('/') }}" class="text-white/70 hover:text-white no-underline">Home</a>
            <span class="mx-2 text-white/40">/</span>
            <span class="text-white">FAQ</span>
        </p>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-3">Frequently Asked Questions</h1>
        <p class="text-lg opacity-75 mx-auto" style="max-width: 550px;">
            Everything you need to know about shopping at Hamko Bazar
</p>
    </div>
</section>

<section class="py-16" style="background: #f8faf8;">
    <div class="max-w-4xl px-4" style="margin: 0 auto;">

        {{-- Search Bar --}}
        <div class="mb-10">
            <div class="relative max-w-lg mx-auto">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="faqSearch" class="search-box pl-12" placeholder="Search for questions..." onkeyup="filterFAQs()">
            </div>
        </div>

        @if($page)
            <div class="bg-white rounded-2xl p-6 md:p-10 shadow-sm mb-8">
                {!! class_exists('Purifier') ? Purifier::clean($page->content) : $page->content !!}
            </div>
        @endif

        @if($faqs->count() > 0)
        <div id="faqAccordion">
            @foreach($faqs as $index => $faq)
            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" onclick="toggleFaq(this, 'faq{{ $faq->id }}')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: {{ $index === 0 ? '#fff' : '#2D5A27' }}; font-size: 1.1rem;"></i>
                        {{ $faq->question }}
                    </button>
                </h2>
                <div id="faq{{ $faq->id }}" class="{{ $index === 0 ? '' : 'hidden' }}">
                    <div class="accordion-body">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- Static FAQs --}}
        <div id="faqAccordion">

            {{-- Orders & Shopping --}}
            <div class="flex items-center gap-3 mb-4 mt-2">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(45,90,39,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-cart3" style="color: #2D5A27;"></i>
                </div>
                <h5 class="font-bold mb-0" style="color: #2D5A27;">Orders &amp; Shopping</h5>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button" type="button" onclick="toggleFaq(this, 'faq1')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        How do I place an order?
                    </button>
                </h2>
                <div id="faq1">
                    <div class="accordion-body">
                        Placing an order at Hamko Bazar is simple! Browse our categories — Houseware, Cookware, Furniture — and click on any product to view details. Select the quantity you need and click <strong>"Add to Cart"</strong>. Once you're done shopping, go to your cart, review your items, and click <strong>"Checkout"</strong>. Enter your delivery details and choose a payment method to complete your order.
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button collapsed" type="button" onclick="toggleFaq(this, 'faq2')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        Do I need to create an account to shop?
                    </button>
                </h2>
                <div id="faq2" class="hidden">
                    <div class="accordion-body">
                        While you can browse our catalog without an account, you'll need to <strong>register</strong> or <strong>log in</strong> to place an order. Creating an account is free and gives you access to order tracking, wishlists, and exclusive offers.
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button collapsed" type="button" onclick="toggleFaq(this, 'faq3')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        Can I modify or cancel my order after placing it?
                    </button>
                </h2>
                <div id="faq3" class="hidden">
                    <div class="accordion-body">
                        Orders can be modified or cancelled within <strong>1 hour</strong> of placement, provided the order hasn't been shipped yet. Please contact our customer support team as soon as possible with your order number, and we'll assist you.
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            <div class="flex items-center gap-3 mb-4 mt-8">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(212,175,55,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-credit-card" style="color: #b8962e;"></i>
                </div>
                <h5 class="font-bold mb-0" style="color: #b8962e;">Payment</h5>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button collapsed" type="button" onclick="toggleFaq(this, 'faq4')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        What payment methods do you accept?
                    </button>
                </h2>
                <div id="faq4" class="hidden">
                    <div class="accordion-body">
                        We accept a variety of payment methods including <strong>bKash, Nagad, Rocket</strong>, credit/debit cards (Visa, Mastercard), and <strong>Cash on Delivery</strong> in selected areas. All payments are processed securely.
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button collapsed" type="button" onclick="toggleFaq(this, 'faq5')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        Is it safe to pay online?
                    </button>
                </h2>
                <div id="faq5" class="hidden">
                    <div class="accordion-body">
                        Absolutely! We use industry-standard encryption to protect your payment information. Your card details and transaction data are handled securely through trusted payment gateways. We never store your full payment information on our servers.
                    </div>
                </div>
            </div>

            {{-- Delivery --}}
            <div class="flex items-center gap-3 mb-4 mt-8">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(45,90,39,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-truck" style="color: #2D5A27;"></i>
                </div>
                <h5 class="font-bold mb-0" style="color: #2D5A27;">Delivery &amp; Shipping</h5>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button collapsed" type="button" onclick="toggleFaq(this, 'faq6')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        What areas do you deliver to?
                    </button>
                </h2>
                <div id="faq6" class="hidden">
                    <div class="accordion-body">
                        We currently deliver to all major areas in <strong>Dhaka</strong> and are actively expanding to other cities across Bangladesh. Enter your delivery address at checkout to check availability in your area.
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button collapsed" type="button" onclick="toggleFaq(this, 'faq7')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        How long does delivery take?
                    </button>
                </h2>
                <div id="faq7" class="hidden">
                    <div class="accordion-body">
                        Delivery typically takes <strong>1-3 business days</strong> within Dhaka metropolitan area, and <strong>3-7 business days</strong> for other regions. You'll receive a confirmation with tracking details once your order ships.
                    </div>
                </div>
            </div>

            {{-- Returns --}}
            <div class="flex items-center gap-3 mb-4 mt-8">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(212,175,55,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-arrow-return-left" style="color: #b8962e;"></i>
                </div>
                <h5 class="font-bold mb-0" style="color: #b8962e;">Returns &amp; Refunds</h5>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button collapsed" type="button" onclick="toggleFaq(this, 'faq9')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        What is your return policy?
                    </button>
                </h2>
                <div id="faq9" class="hidden">
                    <div class="accordion-body">
                        If you receive a <strong>damaged, defective, or incorrect</strong> product, please contact us within <strong>2 hours</strong> of delivery. We'll arrange for a replacement or refund. Products must be in their original packaging and condition.
                    </div>
                </div>
            </div>

            {{-- Account --}}
            <div class="flex items-center gap-3 mb-4 mt-8">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(45,90,39,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="bi bi-person-circle" style="color: #2D5A27;"></i>
                </div>
                <h5 class="font-bold mb-0" style="color: #2D5A27;">Account &amp; Support</h5>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button collapsed" type="button" onclick="toggleFaq(this, 'faq13')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        How do I track my order?
                    </button>
                </h2>
                <div id="faq13" class="hidden">
                    <div class="accordion-body">
                        Once your order is confirmed and shipped, you'll receive an <strong>SMS notification</strong> with tracking information. You can also track your order anytime by logging into your account and visiting the <strong>"My Orders"</strong> section.
                    </div>
                </div>
            </div>

            <div class="accordion-item faq-item">
                <h2>
                    <button class="accordion-button collapsed" type="button" onclick="toggleFaq(this, 'faq14')">
                        <i class="bi bi-question-circle flex-shrink-0" style="color: #2D5A27; font-size: 1.1rem;"></i>
                        How can I contact customer support?
                    </button>
                </h2>
                <div id="faq14" class="hidden">
                    <div class="accordion-body">
                        We're here to help! Reach us via <strong>phone at +880 01766-664488</strong>, email at <strong>hamkobazar@gmail.com</strong>, or through the WhatsApp chat widget on our website. Our support team is available <strong>Saturday to Thursday, 9:00 AM - 5:00 PM</strong>.
                    </div>
                </div>
            </div>

        </div>
        @endif

        <!-- Contact CTA -->
        <div class="cta-card mt-10">
            <i class="bi bi-headset mb-3" style="font-size: 2.5rem; opacity: 0.9;"></i>
            <h3 class="font-bold text-2xl mb-2">Still Have Questions?</h3>
            <p class="mb-6 opacity-75 mx-auto" style="max-width: 450px;">
                Our support team is happy to help you. Reach out to us anytime!
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ url('/contact') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-green-800 font-semibold rounded-lg hover:bg-gray-100 no-underline">
                    <i class="bi bi-chat-dots"></i>Contact Us
                </a>
                <a href="tel:+8801766664488" class="inline-flex items-center gap-2 px-6 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-green-800 no-underline">
                    <i class="bi bi-telephone"></i>Call Us
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function filterFAQs() {
    var input = document.getElementById('faqSearch').value.toLowerCase();
    var items = document.querySelectorAll('.faq-item');
    items.forEach(function(item) {
        var text = item.textContent.toLowerCase();
        item.style.display = text.includes(input) ? '' : 'none';
    });
}

function toggleFaq(btn, targetId) {
    var content = document.getElementById(targetId);
    var isCollapsed = content.classList.contains('hidden');
    // Close all other open items
    document.querySelectorAll('#faqAccordion .accordion-body').forEach(function(b) {
        var parent = b.closest('.accordion-item');
        if (parent) {
            var btnEl = parent.querySelector('.accordion-button');
            var bodyWrap = parent.querySelector('[id^="faq"]');
            if (bodyWrap && bodyWrap.id !== targetId) {
                bodyWrap.classList.add('hidden');
                if (btnEl) {
                    btnEl.classList.add('collapsed');
                    btnEl.style.background = '';
                    btnEl.style.color = '#1f2937';
                    var icon = btnEl.querySelector('.bi-question-circle');
                    if (icon) icon.style.color = '#2D5A27';
                }
            }
        }
    });
    if (isCollapsed) {
        content.classList.remove('hidden');
        btn.classList.remove('collapsed');
        btn.style.background = 'linear-gradient(135deg, #2D5A27 0%, #4A7C43 100%)';
        btn.style.color = '#fff';
        var icon = btn.querySelector('.bi-question-circle');
        if (icon) icon.style.color = '#fff';
    } else {
        content.classList.add('hidden');
        btn.classList.add('collapsed');
        btn.style.background = '';
        btn.style.color = '#1f2937';
        var icon = btn.querySelector('.bi-question-circle');
        if (icon) icon.style.color = '#2D5A27';
    }
}
</script>
@endpush
