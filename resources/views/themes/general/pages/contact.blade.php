@extends('themes.general.layouts.app')

@section('title', 'Contact Us')

@push('styles')
<style>
/* Hero Section */
.contact-hero {
    background: linear-gradient(135deg, var(--hardcoded-green) 0%, var(--hardcoded-green-light) 30%, var(--hardcoded-green-light) 70%, var(--hardcoded-green-light) 100%);
    padding: 120px 0 140px;
    position: relative;
    overflow: hidden;
    width: 100%;
}
.contact-hero .container {
    max-width: 1320px;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    margin-left: auto;
    margin-right: auto;
    width: 100%;
}
.contact-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
    animation: patternMove 30s linear infinite;
}
@keyframes patternMove {
    0% { background-position: 0 0; }
    100% { background-position: 100px 100px; }
}
.hero-floating-shapes {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    pointer-events: none;
}
.hero-floating-shapes .shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    animation: float 15s ease-in-out infinite;
}
.hero-floating-shapes .shape:nth-child(1) {
    width: 200px;
    height: 200px;
    top: -50px;
    right: -50px;
    animation-delay: 0s;
}
.hero-floating-shapes .shape:nth-child(2) {
    width: 150px;
    height: 150px;
    bottom: 20%;
    left: -30px;
    animation-delay: -5s;
}
.hero-floating-shapes .shape:nth-child(3) {
    width: 100px;
    height: 100px;
    top: 30%;
    right: 10%;
    animation-delay: -10s;
}
@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(10deg); }
}
.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 1320px;
    margin: 0 auto;
}
.hero-icon-wrapper {
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 28px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.2);
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
    50% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(255,255,255,0); }
}
.hero-icon-wrapper i {
    font-size: 2.8rem;
    color: #fff;
}
.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 16px;
    text-shadow: 0 4px 20px rgba(0,0,0,0.2);
}
.hero-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    max-width: 500px;
    margin: 0 auto;
}
.hero-stats {
    display: flex;
    justify-content: center;
    gap: 50px;
    margin-top: 40px;
}
.hero-stat {
    text-align: center;
}
.hero-stat-value {
    font-size: 2rem;
    font-weight: 700;
    display: block;
}
.hero-stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Contact Section */
.contact-section {
    margin-top: -80px;
    position: relative;
    z-index: 10;
    padding-bottom: 80px;
}

/* Ensure Bootstrap container is properly centered */
.contact-hero > .container,
.contact-section > .container,
.hours-section > .container,
.newsletter-section > .container {
    max-width: 1320px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    width: 100%;
}

/* Info Cards */
.info-cards-wrapper .row {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
}
.info-cards-wrapper .row > [class*="col-"] {
    flex: 0 0 calc(33.333% - 1.35rem);
    max-width: calc(33.333% - 1.35rem);
}
@media (max-width: 991px) {
    .info-cards-wrapper .row > [class*="col-"] {
        flex: 0 0 calc(50% - 1rem);
        max-width: calc(50% - 1rem);
    }
}
@media (max-width: 767px) {
    .info-cards-wrapper .row > [class*="col-"] {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
.info-cards-wrapper {
    margin-bottom: 50px;
}
.info-card {
    background: #fff;
    border-radius: 24px;
    padding: 36px 28px;
    height: 100%;
    box-shadow: 0 10px 40px rgba(0,0,0,0.06);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(0,0,0,0.04);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.info-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}
.info-card .card-body p {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    margin-bottom: 8px;
    min-height: 48px;
}
.info-card .action-btn {
    margin-top: auto;
}
.info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--hardcoded-green), var(--hardcoded-green-light));
    transform: scaleX(0);
    transition: transform 0.4s ease;
}
.info-card:hover::before {
    transform: scaleX(1);
}
.info-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 25px 60px rgba(45, 90, 39, 0.12);
}
.info-card .icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    transition: all 0.4s ease;
    position: relative;
}
.info-card .icon-wrapper::after {
    content: '';
    position: absolute;
    inset: -5px;
    border-radius: 25px;
    border: 2px dashed currentColor;
    opacity: 0.2;
    transition: all 0.4s ease;
}
.info-card:hover .icon-wrapper {
    transform: scale(1.1) rotate(5deg);
}
.info-card:hover .icon-wrapper::after {
    inset: -10px;
    opacity: 0.4;
}
.info-card .icon-wrapper i {
    font-size: 2rem;
}
.info-card h5 {
    font-weight: 700;
    margin-bottom: 12px;
    color: #1a1a2e;
}
.info-card p {
    color: #6b7280;
    margin-bottom: 4px;
    line-height: 1.5;
}
.info-card .info-subtitle {
    color: #9ca3af;
    font-size: 0.85rem;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.info-card .info-subtitle i {
    font-size: 0.8rem;
}
.info-card .action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 28px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-top: 8px;
    transition: all 0.3s ease;
    width: auto;
    min-width: 160px;
}
.info-card .action-btn:hover {
    transform: translateY(-2px);
}

/* Main Content Area */
.main-content-wrapper {
    display: grid;
    grid-template-columns: 1fr 1.4fr;
    gap: 30px;
    max-width: 1320px;
    margin: 0 auto;
}
@media (max-width: 991px) {
    .main-content-wrapper {
        grid-template-columns: 1fr;
    }
}

/* Form Card */
.form-card {
    background: #fff;
    border-radius: 28px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.06);
    height: fit-content;
    position: relative;
    overflow: hidden;
}
.form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, var(--hardcoded-green), var(--hardcoded-green-light));
}
.form-header {
    margin-bottom: 32px;
}
.form-header h4 {
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 8px;
}
.form-header p {
    color: #6b7280;
    margin: 0;
}
.form-floating {
    margin-bottom: 20px;
}
.form-floating > .form-control {
    border: 2px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px 20px;
    height: auto;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #fafafa;
}
.form-floating > .form-control:focus {
    border-color: var(--hardcoded-green);
    box-shadow: 0 0 0 4px rgba(45, 90, 39, 0.08);
    background: #fff;
}
.form-floating > label {
    padding: 18px 20px;
    color: #9ca3af;
    font-weight: 500;
}
.form-floating > .form-control:focus ~ label {
    color: var(--hardcoded-green);
}
.btn-submit {
    background: linear-gradient(135deg, var(--hardcoded-green) 0%, var(--hardcoded-green-light) 100%);
    border: none;
    border-radius: 14px;
    padding: 18px 40px;
    font-weight: 600;
    font-size: 1.05rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    letter-spacing: 0.5px;
    color: #fff;
}
.btn-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
    transition: left 0.6s ease;
}
.btn-submit:hover::before {
    left: 100%;
}
.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(45, 90, 39, 0.35);
}
.btn-submit:active {
    transform: translateY(-1px);
}

/* Social Links */
.social-section {
    margin-top: 32px;
    padding-top: 28px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}
.social-section p {
    color: #6b7280;
    margin-bottom: 16px;
    font-size: 0.95rem;
}
.social-links {
    display: flex;
    justify-content: center;
    gap: 12px;
}
.social-links a {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid;
}
.social-links a:hover {
    transform: translateY(-6px) scale(1.1);
}
.social-links a.facebook {
    border-color: #1877f2;
    color: #1877f2;
}
.social-links a.facebook:hover {
    background: #1877f2;
    color: #fff;
}
.social-links a.instagram {
    border-color: #e4405f;
    color: #e4405f;
}
.social-links a.instagram:hover {
    background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
    border-color: transparent;
    color: #fff;
}
.social-links a.twitter {
    border-color: #1da1f2;
    color: #1da1f2;
}
.social-links a.twitter:hover {
    background: #1da1f2;
    color: #fff;
}
.social-links a.whatsapp {
    border-color: #25d366;
    color: #25d366;
}
.social-links a.whatsapp:hover {
    background: #25d366;
    color: #fff;
}

/* Map Card */
.map-card {
    background: #fff;
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    height: 100%;
}
.map-header {
    padding: 24px 28px;
    background: rgba(45, 90, 39, 0.03);
    border-bottom: 1px solid #e5e7eb;
}
.map-header h5 {
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.map-header h5 i {
    color: var(--hardcoded-green);
}
.map-header p {
    color: #6b7280;
    margin: 0;
    font-size: 0.95rem;
}
.map-wrapper {
    flex: 1;
    min-height: 400px;
}
.map-wrapper iframe {
    width: 100%;
    height: 100%;
    min-height: 400px;
    border: none;
}

/* Quick Actions Bar */
.quick-actions {
    display: flex;
    gap: 12px;
    padding: 16px 28px;
    background: #fafafa;
    border-top: 1px solid #e5e7eb;
}
.quick-action-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
}
.quick-action-btn i {
    font-size: 1.1rem;
}
.quick-action-btn.call {
    background: var(--hardcoded-green);
    color: #fff;
}
.quick-action-btn.call:hover {
    background: var(--hardcoded-green);
    transform: scale(1.02);
}
.quick-action-btn.directions {
    background: #fff;
    color: var(--hardcoded-green);
    border: 2px solid var(--hardcoded-green);
}
.quick-action-btn.directions:hover {
    background: var(--hardcoded-green);
    color: #fff;
}

/* Business Hours Section */
.hours-section {
    margin-top: 50px;
}
.hours-card {
    background: rgba(45, 90, 39, 0.04);
    border-radius: 24px;
    padding: 36px;
    border: 1px solid rgba(45, 90, 39, 0.1);
    position: relative;
    overflow: hidden;
}
.hours-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(45, 90, 39, 0.05) 0%, transparent 70%);
}
.hours-header {
    text-align: center;
    margin-bottom: 28px;
    position: relative;
}
.hours-icon {
    width: 70px;
    height: 70px;
    background: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    box-shadow: 0 8px 25px rgba(45, 90, 39, 0.15);
}
.hours-icon i {
    font-size: 1.8rem;
    color: var(--hardcoded-green);
}
.hours-header h5 {
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 4px;
}
.hours-header p {
    color: #6b7280;
    margin: 0;
    font-size: 0.95rem;
}
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 12px;
}
.status-badge.open {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}
.status-badge.open::before {
    content: '';
    width: 8px;
    height: 8px;
    background: #16a34a;
    border-radius: 50%;
    animation: blink 1.5s ease-in-out infinite;
}
@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
.hours-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px 24px;
    position: relative;
}
@media (max-width: 575px) {
    .hours-grid {
        grid-template-columns: 1fr;
    }
}
.hours-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: #fff;
    border-radius: 12px;
    transition: all 0.3s ease;
}
.hours-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.hours-item .day {
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}
.hours-item .day i {
    color: #9ca3af;
    font-size: 0.9rem;
}
.hours-item .time {
    font-weight: 500;
    color: var(--hardcoded-green);
}
.hours-item .time.closed {
    color: #dc2626;
    font-weight: 600;
}
.hours-item.today {
    background: linear-gradient(135deg, var(--hardcoded-green) 0%, var(--hardcoded-green-light) 100%);
}
.hours-item.today .day,
.hours-item.today .time {
    color: #fff;
}
.hours-item.today .day i {
    color: rgba(255,255,255,0.7);
}

@media (max-width: 767px) {
    .contact-hero {
        padding: 80px 0 100px;
    }
    .hero-title {
        font-size: 2.5rem;
    }
    .hero-stats {
        gap: 30px;
    }
    .hero-stat-value {
        font-size: 1.5rem;
    }
    .form-card {
        padding: 28px;
    }
    .hours-card {
        padding: 24px;
    }
}
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="contact-hero text-white text-center">
    <div class="hero-floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <div class="container position-relative">
        <div class="hero-content">
            <div class="hero-icon-wrapper">
                <i class="bi bi-chat-heart-fill"></i>
            </div>
            <h1 class="hero-title">Get In Touch</h1>
            <p class="hero-subtitle">We're here to help and answer any question you might have. We look forward to hearing from you!</p>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-value">24/7</span>
                    <span class="hero-stat-label">Support Available</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">&lt;1hr</span>
                    <span class="hero-stat-label">Response Time</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">99%</span>
                    <span class="hero-stat-label">Happy Customers</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <!-- Info Cards -->
        <div class="info-cards-wrapper">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="info-card">
                        <div class="icon-wrapper bg-success bg-opacity-10 text-success">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="card-body">
                            <h5>Visit Our Store</h5>
                            <p class="fw-medium text-dark">{{ $settings['contact_address'] ?? '123 Green Market Road, Dhaka-1205, Bangladesh' }}</p>
                            <span class="info-subtitle"><i class="bi bi-clock"></i>Open 7 days a week</span>
                            <a href="https://maps.google.com" target="_blank" class="action-btn btn btn-outline-success">
                                <i class="bi bi-map"></i>Get Directions
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="info-card">
                        <div class="icon-wrapper bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div class="card-body">
                            <h5>Call Us Directly</h5>
                            <p class="fw-semibold">{{ $settings['top_bar_phone'] ?? $settings['footer_phone'] ?? '+88 01766 66 44 88' }}</p>
                            <span class="info-subtitle"><i class="bi bi-clock"></i>Sat - Thu: 8AM - 10PM</span>
                            <a href="tel:{{ $settings['top_bar_phone'] ?? $settings['footer_phone'] ?? '+8801766664488' }}" class="action-btn btn btn-outline-primary">
                                <i class="bi bi-telephone"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="info-card">
                        <div class="icon-wrapper bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div class="card-body">
                            <h5>Email Support</h5>
                            <p class="fw-medium">{{ $settings['top_bar_email'] ?? $settings['footer_email'] ?? 'hamkobazar@gmail.com' }}</p>
                            <span class="info-subtitle"><i class="bi bi-clock"></i>We reply within 24 hours</span>
                            <a href="mailto:{{ $settings['top_bar_email'] ?? $settings['footer_email'] ?? 'hamkobazar@gmail.com' }}" class="action-btn btn btn-outline-warning">
                                <i class="bi bi-envelope"></i>Send Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content: Form + Map -->
        <div class="main-content-wrapper">
            <!-- Contact Form -->
            <div class="form-card">
                <div class="form-header">
                    <h4><i class="bi bi-send-check text-success me-2"></i>Send us a Message</h4>
                    <p>Fill out the form below and our team will get back to you within 24 hours.</p>
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <form action="{{ route('contact.send') }}" method="POST" id="contactForm">
                    @csrf
                    
                    <div class="form-floating">
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Your Name" required>
                        <label for="name"><i class="bi bi-person me-2"></i>Your Name</label>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-floating">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Email Address" required>
                        <label for="email"><i class="bi bi-envelope me-2"></i>Email Address</label>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-floating">
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Phone Number"
                            pattern="(01[0-9]{9}|\+[1-9][0-9]{7,14})"
                            title="BD: 01XXXXXXXXX or International: +XXXXXXXXX">
                        <label for="phone"><i class="bi bi-telephone me-2"></i>Phone Number (Optional)</label>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-floating">
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Subject" required>
                        <label for="subject"><i class="bi bi-chat-left-text me-2"></i>Subject</label>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-floating mb-4">
                        <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" placeholder="Your Message" style="height: 140px" required>{{ old('message') }}</textarea>
                        <label for="message"><i class="bi bi-pencil me-2"></i>Your Message</label>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-submit text-white w-100">
                        <i class="bi bi-send me-2"></i>Send Message
                    </button>
                </form>
                
                <!-- Social Links -->
                <div class="social-section">
                    <p>Or connect with us on social media</p>
                    <div class="social-links">
                        <a href="https://facebook.com/hamkobazar" class="facebook" title="Facebook" target="_blank">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="instagram" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="twitter" title="Twitter">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        <a href="#" class="whatsapp" title="WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Map Card -->
            <div class="map-card">
                <div class="map-header">
                    <h5><i class="bi bi-geo-alt-fill"></i>Find Us on Map</h5>
                    <p>Visit our store for fresh halal products and groceries</p>
                </div>
                <div class="map-wrapper">
                    @php
                        // Place-ID embed shows the labeled HAMKO Industries (J & J Tower) pin.
                        // Backend lat/lng (default store, Admin → Stores) matches this location:
                        // 22.81920141, 89.55057871 (64 KDA Ave, Khulna 9100).
                    @endphp
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3677.548137724079!2d89.55057870666296!3d22.81920141159502!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39ff91184efada9f%3A0x62c81bf9e9e89d57!2sHAMKO%20Industries%20Ltd.%20(J%20%26%20J%20Tower)!5e0!3m2!1sen!2sbd!4v1786249069317!5m2!1sen!2sbd" 
                        width="600" height="450" style="border:0;"
                        allowfullscreen="" 
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin">
                    </iframe>
                </div>
                <div class="quick-actions">
                    <a href="tel:{{ $settings['top_bar_phone'] ?? $settings['footer_phone'] ?? '+8801766664488' }}" class="quick-action-btn call">
                        <i class="bi bi-telephone-fill"></i>Call Now
                    </a>
                    <a href="https://maps.google.com" target="_blank" class="quick-action-btn directions">
                        <i class="bi bi-signpost-2"></i>Get Directions
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Business Hours -->
        <div class="hours-section">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="hours-card">
                        <div class="hours-header">
                            <div class="hours-icon">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <h5>Business Hours</h5>
                            <p>We're here to serve you with the freshest products</p>
                            <span class="status-badge open">
                                <span>Currently Open</span>
                            </span>
                        </div>
                        <div class="hours-grid">
                            <div class="hours-item">
                                <span class="day"><i class="bi bi-calendar3"></i>Saturday</span>
                                <span class="time">8:00 AM - 10:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span class="day"><i class="bi bi-calendar3"></i>Sunday</span>
                                <span class="time">8:00 AM - 10:00 PM</span>
                            </div>
                            <div class="hours-item today">
                                <span class="day"><i class="bi bi-star-fill"></i>Monday</span>
                                <span class="time">8:00 AM - 10:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span class="day"><i class="bi bi-calendar3"></i>Tuesday</span>
                                <span class="time">8:00 AM - 10:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span class="day"><i class="bi bi-calendar3"></i>Wednesday</span>
                                <span class="time">8:00 AM - 10:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span class="day"><i class="bi bi-calendar3"></i>Thursday</span>
                                <span class="time">8:00 AM - 10:00 PM</span>
                            </div>
                            <div class="hours-item">
                                <span class="day"><i class="bi bi-calendar3"></i>Friday</span>
                                <span class="time closed">Closed</span>
                            </div>
                        </div>
                    </div>
</div>
        </div>
        
    </div>
</section>

@push('scripts')
<script>
// Highlight current day in business hours
document.addEventListener('DOMContentLoaded', function() {
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const today = days[new Date().getDay()];
    
    document.querySelectorAll('.hours-item').forEach(item => {
        const dayText = item.querySelector('.day').textContent.trim();
        if (dayText.includes(today)) {
            item.classList.add('today');
        } else {
            item.classList.remove('today');
        }
    });
    
    // Form submission animation
    const form = document.getElementById('contactForm');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('.btn-submit');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            btn.disabled = true;
        });
    }
    
});
</script>
@endpush
@endsection
