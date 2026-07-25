@extends('admin.layouts.app')

@section('title', 'Chat Widget Settings')

@section('content')
@php
    // Load all settings once for use throughout the view
    $widgetPosition = \App\Models\Setting::get('chat_widget_position', 'bottom-right');
    $widgetColor = \App\Models\Setting::get('chat_widget_color', '#0d6efd');
    $widgetButtonText = \App\Models\Setting::get('chat_widget_button_text', 'Chat');
    $enableSound = \App\Models\Setting::get('chat_enable_sound', '1');
    $enableDesktopNotify = \App\Models\Setting::get('chat_enable_desktop_notify', '1');
    $showOnlineStatus = \App\Models\Setting::get('chat_show_online_status', '1');
    $enableCannedResponses = \App\Models\Setting::get('chat_enable_canned_responses', '1');
    $welcomeMessage = \App\Models\Setting::get('chat_welcome_message', 'Hello! How can I help you today?');
    $welcomeSubtitle = \App\Models\Setting::get('chat_welcome_subtitle', 'Our team typically replies within minutes');
    $offlineMessage = \App\Models\Setting::get('chat_offline_message', 'We are currently offline. Leave us a message!');
    $replyGreeting = \App\Models\Setting::get('chat_reply_greeting', 'Wa Alaikum Assalam! Welcome to Halal Food Store. How can I assist you today?');
    $replyDelivery = \App\Models\Setting::get('chat_reply_delivery', 'We deliver across Bangladesh! Dhaka: Same day delivery. Other areas: 1-3 business days. Free delivery on orders over ৳500!');
    $replyPayment = \App\Models\Setting::get('chat_reply_payment', 'We accept multiple payment methods: bKash, Nagad, Rocket, Credit/Debit Cards, and Cash on Delivery.');
    $replyTrackOrder = \App\Models\Setting::get('chat_reply_track_order', 'To track your order, please. You can also provide your order number check your order status in My Orders section.');
    $replyReturn = \App\Models\Setting::get('chat_reply_return', 'We have a hassle-free return policy! If not satisfied, contact us within 24 hours for refund or replacement.');
    $replyHalal = \App\Models\Setting::get('chat_reply_halal', 'All our products are 100% Halal certified! We source from trusted suppliers and maintain strict quality standards.');
    $replyPrice = \App\Models\Setting::get('chat_reply_price', 'Our prices are competitive and transparent. Check our Deals section for special discounts!');
    $replyContact = \App\Models\Setting::get('chat_reply_contact', 'You can reach us at: Phone: 019XX-XXXXXX, Email: support@halalfoodstore.com');
@endphp

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Chat Widget Settings</h4>
    <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Live Chat
    </a>
</div>

<!-- Success Message -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <!-- Settings Form -->
    <div class="col-lg-8">
        <form action="{{ route('admin.chat.widget-settings') }}" method="POST" id="widget-settings-form">
            @csrf
            
            <!-- Widget Appearance -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-palette me-2"></i>Widget Appearance</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Widget Position</label>
                            <select class="form-select" name="widget_position" id="widgetPosition">
                                <option value="bottom-right" {{ $widgetPosition == 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                                <option value="bottom-left" {{ $widgetPosition == 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                                <option value="top-right" {{ $widgetPosition == 'top-right' ? 'selected' : '' }}>Top Right</option>
                                <option value="top-left" {{ $widgetPosition == 'top-left' ? 'selected' : '' }}>Top Left</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Widget Theme Color</label>
                            <div class="d-flex align-items-center">
                                <input type="color" class="form-control form-control-color me-2" name="widget_color" id="widgetColor" value="{{ $widgetColor }}">
                                <span class="text-muted small">Primary color for the widget</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Floating Button Text</label>
                        <input type="text" class="form-control" name="widget_button_text" id="widgetButtonText" value="{{ $widgetButtonText }}" placeholder="Chat">
                        <div class="form-text">Text label on the floating chat button</div>
                    </div>
                </div>
            </div>

            <!-- Welcome Messages -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i>Welcome Messages</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Welcome Message (Customer Chat)</label>
                        <input type="text" name="chat_welcome_message" id="chatWelcomeMessage" class="form-control" value="{{ $welcomeMessage }}">
                        <div class="form-text">Message shown when customer opens chat widget</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Welcome Subtitle</label>
                        <input type="text" name="chat_welcome_subtitle" id="chatWelcomeSubtitle" class="form-control" value="{{ $welcomeSubtitle }}">
                        <div class="form-text">Subtitle shown below the welcome message</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Offline Message</label>
                        <input type="text" name="chat_offline_message" id="chatOfflineMessage" class="form-control" value="{{ $offlineMessage }}">
                        <div class="form-text">Message shown when no agents are online</div>
                    </div>
                </div>
            </div>

            <!-- Auto-Reply Messages -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-chat-heart me-2"></i>Auto-Reply Messages (Chatbot)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Configure automatic replies for common customer queries. These replies are triggered by specific keywords.
                    </p>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <span class="badge bg-info me-1">Greeting</span> 
                            Trigger: hello, hi, salam, hey
                        </label>
                        <textarea class="form-control" name="chat_reply_greeting" id="chatReplyGreeting" rows="2">{{ $replyGreeting }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <span class="badge bg-primary me-1">Delivery</span> 
                            Trigger: delivery, area, shipping, send, poriman
                        </label>
                        <textarea class="form-control" name="chat_reply_delivery" id="chatReplyDelivery" rows="2">{{ $replyDelivery }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <span class="badge bg-success me-1">Payment</span> 
                            Trigger: payment, bkash, nagad, cash, method
                        </label>
                        <textarea class="form-control" name="chat_reply_payment" id="chatReplyPayment" rows="2">{{ $replyPayment }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <span class="badge bg-warning me-1">Order</span> 
                            Trigger: order, track, delivery status, kon din asbe
                        </label>
                        <textarea class="form-control" name="chat_reply_track_order" id="chatReplyTrackOrder" rows="2">{{ $replyTrackOrder }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <span class="badge bg-danger me-1">Return</span> 
                            Trigger: return, refund, change, bad, problem
                        </label>
                        <textarea class="form-control" name="chat_reply_return" id="chatReplyReturn" rows="2">{{ $replyReturn }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <span class="badge bg-secondary me-1">Quality</span> 
                            Trigger: halal, quality, masala, pure, taqwa
                        </label>
                        <textarea class="form-control" name="chat_reply_halal" id="chatReplyHalal" rows="2">{{ $replyHalal }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <span class="badge bg-dark me-1">Price</span> 
                            Trigger: price, cost, offer, discount, deal
                        </label>
                        <textarea class="form-control" name="chat_reply_price" id="chatReplyPrice" rows="2">{{ $replyPrice }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <span class="badge bg-light text-dark me-1">Contact</span> 
                            Trigger: contact, phone, number, call, email
                        </label>
                        <textarea class="form-control" name="chat_reply_contact" id="chatReplyContact" rows="2">{{ $replyContact }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Advanced Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="enableSound" name="enable_sound" {{ $enableSound == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enableSound">Enable Sound Notifications</label>
                            </div>
                            <div class="form-text">Play sound when new message arrives</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="enableDesktopNotify" name="enable_desktop_notify" {{ $enableDesktopNotify == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enableDesktopNotify">Desktop Notifications</label>
                            </div>
                            <div class="form-text">Show browser notifications for new messages</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="showOnlineStatus" name="show_online_status" {{ $showOnlineStatus == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="showOnlineStatus">Show Online Status</label>
                            </div>
                            <div class="form-text">Display online/offline status to customers</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="enableCannedResponses" name="enable_canned_responses" {{ $enableCannedResponses == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enableCannedResponses">Enable Quick Replies</label>
                            </div>
                            <div class="form-text">Show quick reply buttons to customers</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Preview Panel -->
    <div class="col-lg-4">
        <!-- Widget Preview -->
        <div class="card border-0 shadow-sm mb-3" id="widgetPreviewCard">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Widget Preview</h6>
            </div>
            <div class="card-body p-0">
                <div class="bg-light p-3" style="min-height: 350px; position: relative;">
                    <!-- Floating Button -->
                    <div id="previewFloatingButton" style="position: absolute; bottom: 10px; right: 10px;">
                        <button class="btn rounded-circle p-3 shadow" id="previewChatButton" style="background-color: {{ $widgetColor }};">
                            <i class="bi bi-chat-dots fs-5"></i>
                        </button>
                    </div>
                    
                    <!-- Chat Window Preview -->
                    <div class="bg-white rounded shadow-sm" id="previewChatWindow" style="position: absolute; bottom: 70px; right: 10px; width: 280px;">
                        <!-- Header -->
                        <div class="text-white p-3 rounded-top" id="previewChatHeader" style="background-color: {{ $widgetColor }}; position: relative;">
                            <div class="d-flex align-items-center">
                                <div class="bg-white rounded-circle p-2 me-2">
                                    <i class="bi bi-headset" id="previewHeaderIcon" style="color: {{ $widgetColor }};"></i>
                                </div>
                                <div>
                                    <strong>Customer Support</strong>
                                    <div class="small opacity-75">Online</div>
                                </div>
                            </div>
                        </div>
                        <!-- Messages -->
                        <div class="p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="mb-2">
                                <div class="bg-light rounded p-2">
                                    <small><strong>Welcome!</strong></small><br>
                                    <small id="previewWelcomeMessage">{{ $welcomeMessage }}</small>
                                </div>
                                <small class="text-muted">Just now</small>
                            </div>
                            <div class="mb-2 text-end">
                                <div class="text-white rounded p-2" id="previewUserMessage" style="background-color: {{ $widgetColor }};">
                                    <small>I want to know about delivery</small>
                                </div>
                                <small class="text-muted">Just now</small>
                            </div>
                            <div class="mb-2">
                                <div class="bg-light rounded p-2">
                                    <small id="previewDeliveryMessage">{{ $replyDelivery }}</small>
                                </div>
                                <small class="text-muted">Just now</small>
                            </div>
                        </div>
                        <!-- Input -->
                        <div class="p-2 border-top">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" placeholder="Type a message...">
                                <button class="btn" id="previewSendButton" style="background-color: {{ $widgetColor }};"><i class="bi bi-send text-white"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Replies Preview -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Reply Triggers</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="badge bg-secondary">hello</span>
                    <span class="badge bg-secondary">delivery</span>
                    <span class="badge bg-secondary">payment</span>
                    <span class="badge bg-secondary">order</span>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge bg-secondary">return</span>
                    <span class="badge bg-secondary">halal</span>
                    <span class="badge bg-secondary">price</span>
                    <span class="badge bg-secondary">contact</span>
                </div>
                <hr>
                <small class="text-muted">Customers can type these keywords to get instant auto-replies</small>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Help & Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small text-muted mb-0 ps-3">
                    <li class="mb-2">Use keywords that customers commonly use</li>
                    <li class="mb-2">Keep auto-replies short and clear</li>
                    <li class="mb-2">Include call-to-action in replies</li>
                    <li class="mb-2">Test the widget after making changes</li>
                    <li>Monitor chat analytics to improve responses</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Container (Preference.md compliant) -->
<div class="floating-save-container">
    <a href="{{ route('admin.chat.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="widget-settings-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Settings
    </button>
</div>
@endsection

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Position mapping for preview
    const positionMap = {
        'bottom-right': { button: { bottom: '10px', right: '10px', top: 'auto', left: 'auto' }, chat: { bottom: '70px', right: '10px', top: 'auto', left: 'auto' } },
        'bottom-left': { button: { bottom: '10px', right: 'auto', top: 'auto', left: '10px' }, chat: { bottom: '70px', right: 'auto', top: 'auto', left: '10px' } },
        'top-right': { button: { bottom: 'auto', right: '10px', top: '10px', left: 'auto' }, chat: { bottom: 'auto', right: '10px', top: '70px', left: 'auto' } },
        'top-left': { button: { bottom: 'auto', right: 'auto', top: '10px', left: '10px' }, chat: { bottom: 'auto', right: 'auto', top: '70px', left: '10px' } }
    };

    // Update preview on form changes
    function updatePreview() {
        const position = document.getElementById('widgetPosition').value;
        const color = document.getElementById('widgetColor').value;
        const buttonText = document.getElementById('widgetButtonText').value;
        const welcomeMessage = document.getElementById('chatWelcomeMessage').value;
        const welcomeSubtitle = document.getElementById('chatWelcomeSubtitle').value;
        const offlineMessage = document.getElementById('chatOfflineMessage').value;
        const replyDelivery = document.getElementById('chatReplyDelivery').value;

        // Update position
        const pos = positionMap[position];
        const btn = document.getElementById('previewFloatingButton');
        const chat = document.getElementById('previewChatWindow');
        
        btn.style.bottom = pos.button.bottom;
        btn.style.right = pos.button.right;
        btn.style.top = pos.button.top;
        btn.style.left = pos.button.left;
        
        chat.style.bottom = pos.chat.bottom;
        chat.style.right = pos.chat.right;
        chat.style.top = pos.chat.top;
        chat.style.left = pos.chat.left;

        // Update color
        document.getElementById('previewChatHeader').style.backgroundColor = color;
        document.getElementById('previewChatButton').style.backgroundColor = color;
        document.getElementById('previewHeaderIcon').style.color = color;
        document.getElementById('previewUserMessage').style.backgroundColor = color;
        document.getElementById('previewSendButton').style.backgroundColor = color;

        // Update text content
        document.getElementById('previewWelcomeMessage').textContent = welcomeMessage || 'Hello! How can I help you today?';
        document.getElementById('previewDeliveryMessage').textContent = replyDelivery || 'We deliver across Bangladesh!';
    }

    // Attach event listeners to all form inputs
    const formInputs = document.querySelectorAll('#widget-settings-form input, #widget-settings-form select, #widget-settings-form textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });

    // Initial preview update
    updatePreview();
});
</script>
@endpush
