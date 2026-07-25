<!-- WhatsApp Chat Widget -->
@php
    $whatsappEnabled = \App\Models\Setting::get('whatsapp_enabled', '0');
    $whatsappPhone = \App\Models\Setting::get('whatsapp_phone_number', '');
    $whatsappDisplayName = \App\Models\Setting::get('whatsapp_display_name', 'Customer Support');
    $whatsappWelcomeMessage = \App\Models\Setting::get('whatsapp_welcome_message', 'Hello! How can I help you today?');
    $whatsappPosition = \App\Models\Setting::get('whatsapp_position', 'bottom-right');
    $whatsappButtonColor = \App\Models\Setting::get('whatsapp_button_color', '#25D366');
    $whatsappPredefinedMessages = \App\Models\Setting::get('whatsapp_predefined_messages', '');
    $whatsappShowOnMobile = \App\Models\Setting::get('whatsapp_show_on_mobile', '1');
    $whatsappShowOnDesktop = \App\Models\Setting::get('whatsapp_show_on_desktop', '1');
    
    // Parse predefined messages
    $predefinedMessages = array_filter(array_map('trim', explode("\n", $whatsappPredefinedMessages)));
    
    // Determine position classes
    // WhatsApp sits above the Live Chat widget (which is at bottom-6 right-6)
    $positionClasses = '';
    $windowPositionClasses = '';
    switch ($whatsappPosition) {
        case 'bottom-left':
            $positionClasses = 'bottom-6 left-6';
            $windowPositionClasses = 'bottom-16 left-0';
            break;
        case 'top-right':
            $positionClasses = 'top-6 right-6';
            $windowPositionClasses = 'top-16 right-0';
            break;
        case 'top-left':
            $positionClasses = 'top-6 left-6';
            $windowPositionClasses = 'top-16 left-0';
            break;
        default: // bottom-right - position above the Live Chat widget
            $positionClasses = 'bottom-24 right-6';
            $windowPositionClasses = 'bottom-36 right-0';
    }
    
    // Device visibility
    $deviceClass = '';
    if ($whatsappShowOnMobile === '1' && $whatsappShowOnDesktop !== '1') {
        $deviceClass = 'md:hidden';
    } elseif ($whatsappShowOnDesktop === '1' && $whatsappShowOnMobile !== '1') {
        $deviceClass = 'hidden md:block';
    }
@endphp

@if($whatsappEnabled === '1' && !empty($whatsappPhone))
<div class="fixed {{ $positionClasses }} z-50 {{ $deviceClass }}" id="whatsapp-widget">
    <!-- WhatsApp Button -->
    <button id="whatsappToggle" onclick="toggleWhatsApp()" 
        class="w-14 h-14 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center group animate-bounce-slow"
        style="background-color: {{ $whatsappButtonColor }};"
        title="Chat on WhatsApp"
        aria-label="Chat with us on WhatsApp">
        <!-- WhatsApp Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-8 h-8 group-hover:scale-110 transition-transform">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full animate-pulse" id="whatsapp-notification"></span>
    </button>
    
    <!-- WhatsApp Popup Window -->
    <div id="whatsappWindow" class="hidden absolute {{ $windowPositionClasses }} w-80 bg-white rounded-2xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="p-4 text-white" style="background-color: {{ $whatsappButtonColor }};">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-6 h-6">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold">{{ $whatsappDisplayName }}</h4>
                        <span class="text-xs opacity-90 flex items-center">
                            <span class="w-2 h-2 bg-green-300 rounded-full mr-1 animate-pulse"></span>
                            Typically replies within an hour
                        </span>
                    </div>
                </div>
                <button onclick="toggleWhatsApp()" class="text-white/80 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Chat Body -->
        <div class="p-4 bg-gray-50">
            <!-- Welcome Message -->
            <div class="bg-white p-3 rounded-lg shadow-sm mb-4">
                <p class="text-sm text-gray-700">{{ $whatsappWelcomeMessage }}</p>
                <span class="text-xs text-gray-400 mt-1 block">Just now</span>
            </div>
            
            <!-- Predefined Messages -->
            @if(!empty($predefinedMessages))
            <div class="space-y-2 mb-4">
                <p class="text-xs text-gray-500 font-medium">Quick Messages:</p>
                @foreach($predefinedMessages as $message)
                <button onclick="sendWhatsAppMessage(@json($message))" 
                    class="w-full text-left text-sm bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    {{ $message }}
                </button>
                @endforeach
            </div>
            @endif
            
            <!-- Custom Message Input -->
            <div class="flex items-center space-x-2">
                <input type="text" id="whatsappMessageInput" placeholder="Type a message..." 
                    class="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-green-500 text-sm">
                <button onclick="sendCustomWhatsAppMessage()" 
                    class="px-4 py-2 text-white rounded-lg transition-colors hover:opacity-90"
                    style="background-color: {{ $whatsappButtonColor }};">
                    Send
                </button>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="px-4 py-2 bg-white border-t text-center">
            <a href="https://wa.me/{{ $whatsappPhone }}" target="_blank" rel="noopener noreferrer" 
               class="text-xs text-gray-500 hover:text-gray-700 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-1">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Open in WhatsApp
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes bounce-slow {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
    }
    .animate-bounce-slow {
        animation: bounce-slow 2s ease-in-out infinite;
    }
    #whatsappToggle:hover {
        animation: none;
        transform: scale(1.1);
    }
</style>

<script>
let whatsappOpen = false;
const whatsappPhone = @json($whatsappPhone);
const whatsappWelcomeMessage = @json($whatsappWelcomeMessage);

function toggleWhatsApp() {
    whatsappOpen = !whatsappOpen;
    const whatsappWindow = document.getElementById('whatsappWindow');
    const whatsappNotification = document.getElementById('whatsapp-notification');
    const chatWidget = document.querySelector('.fixed.bottom-6.right-6.z-40');
    
    if (whatsappOpen) {
        whatsappWindow.classList.remove('hidden');
        whatsappNotification.classList.add('hidden');
        // Hide Live Chat widget when WhatsApp is open
        if (chatWidget) {
            chatWidget.classList.add('hidden');
        }
    } else {
        whatsappWindow.classList.add('hidden');
        // Show Live Chat widget when WhatsApp is closed
        if (chatWidget) {
            chatWidget.classList.remove('hidden');
        }
    }
}

function sendWhatsAppMessage(message) {
    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = `https://wa.me/${whatsappPhone}?text=${encodedMessage}`;
    window.open(whatsappUrl, '_blank');
}

function sendCustomWhatsAppMessage() {
    const input = document.getElementById('whatsappMessageInput');
    const message = input.value.trim();
    
    if (message) {
        sendWhatsAppMessage(message);
        input.value = '';
    } else {
        // Send default welcome message if no custom message
        sendWhatsAppMessage(whatsappWelcomeMessage);
    }
}

// Allow Enter key to send message
document.getElementById('whatsappMessageInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendCustomWhatsAppMessage();
    }
});

// Close popup when clicking outside
document.addEventListener('click', function(e) {
    const widget = document.getElementById('whatsapp-widget');
    if (whatsappOpen && widget && !widget.contains(e.target)) {
        toggleWhatsApp();
    }
});
</script>
@endif
