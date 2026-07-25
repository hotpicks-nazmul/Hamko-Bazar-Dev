<!-- Chat Widget -->
@php
$chatReplies = [
    'track_order' => \App\Models\Setting::get('chat_reply_track_order', 'To track your order, please provide your order number. You can also check your order status in My Orders section after logging in. 📦'),
    'delivery' => \App\Models\Setting::get('chat_reply_delivery', 'We deliver across Bangladesh! 🇧🇩 Dhaka: Same day delivery. Other areas: 1-3 business days. Free delivery on orders over ৳500!'),
    'payment' => \App\Models\Setting::get('chat_reply_payment', 'We accept multiple payment methods: 💳 bKash, Nagad, Rocket, Credit/Debit Cards (Visa, Mastercard), and Cash on Delivery (COD).'),
    'halal' => \App\Models\Setting::get('chat_reply_halal', 'All our products are 100% Halal certified! ✅ We source from trusted suppliers and maintain strict quality standards.'),
    'return_refund' => \App\Models\Setting::get('chat_reply_return', 'We have a hassle-free return policy! If you are not satisfied, contact us within 24 hours of delivery for a refund or replacement. 🔄'),
    'price' => \App\Models\Setting::get('chat_reply_price', 'Our prices are competitive and transparent. Check our Deals section for special discounts! 💰 Free delivery on orders over ৳500.'),
    'greeting' => \App\Models\Setting::get('chat_reply_greeting', 'Wa Alaikum Assalam! 👋 Welcome to Halal Food Store. How can I assist you today?'),
    'default' => \App\Models\Setting::get('chat_reply_default', 'Thank you for your message! Our support team has received it and will respond shortly. For urgent queries, call +880 1700-000000. 😊'),
    '_welcome_message' => \App\Models\Setting::get('chat_welcome_message', 'Hello! How can I help you today?'),
    '_welcome_subtitle' => \App\Models\Setting::get('chat_welcome_subtitle', 'Our team typically replies within minutes'),
];
@endphp
<div class="fixed bottom-6 right-6 z-40">
    <!-- Chat Button -->
    <div class="relative">
        <button id="chatToggle" onclick="toggleChat()" class="w-14 h-14 bg-halal-green text-white rounded-full shadow-lg hover:text-amber-400 transition-all duration-300 flex items-center justify-center group">
            <i class="bi bi-chat-dots-fill text-xl group-hover:scale-110 transition-transform"></i>
        </button>
        <span id="chatNotification" style="position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; background-color: #dc2626; color: white; font-size: 11px; font-weight: bold; border-radius: 50%; border: 2px solid white; display: none; text-align: center; line-height: 16px; z-index: 60; box-shadow: 0 2px 4px rgba(0,0,0,0.2); pointer-events: none;">0</span>
    </div>
    
    <!-- Chat Window -->
    <div id="chatWindow" class="hidden absolute bottom-16 right-0 w-80 md:w-96 bg-white rounded-2xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="gradient-halal p-4 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="bi bi-headset text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-medium">Customer Support</h4>
                        <span class="text-xs text-green-100 flex items-center">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span>
                            Online
                        </span>
                    </div>
                </div>
                <button onclick="toggleChat()" class="text-white/80 hover:text-white">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Messages -->
        <div id="chatMessages" class="h-80 overflow-y-auto p-4 space-y-4 bg-gray-50">
            <!-- Welcome Message -->
            <div class="flex items-start space-x-2">
                <div class="w-8 h-8 bg-halal-green rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-headset text-white text-sm"></i>
                </div>
                <div class="bg-white p-3 rounded-lg rounded-tl-none shadow-sm max-w-[80%]">
                    <p class="text-sm text-gray-700">{{ \App\Models\Setting::get('chat_welcome_message', 'Hello! How can I help you today?') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ \App\Models\Setting::get('chat_welcome_subtitle', 'Our team typically replies within minutes') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Typing Indicator -->
        <div id="typingIndicator" class="hidden px-4 py-2 bg-gray-50">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-halal-green rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-headset text-white text-sm"></i>
                </div>
                <div class="bg-white px-4 py-2 rounded-lg rounded-tl-none shadow-sm">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-halal-green rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                        <div class="w-2 h-2 bg-halal-green rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                        <div class="w-2 h-2 bg-halal-green rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Guest Registration Form -->
        <div id="guestForm" class="p-4 border-t bg-white" style="display: none;">
            <form onsubmit="registerGuest(event)" class="space-y-3">
                <p class="text-sm text-gray-600">Please provide your details to start chatting:</p>
                <input type="text" id="guestName" placeholder="Your Name" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:border-halal-green focus:outline-none text-sm">
                <input type="tel" id="guestPhone" placeholder="Mobile Number (11 digits)" required maxlength="11"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:border-halal-green focus:outline-none text-sm">
                <button type="submit" class="w-full bg-halal-green text-white py-2 rounded-lg hover:text-amber-400 transition-colors text-sm font-medium">
                    Start Chat
                </button>
            </form>
        </div>
        
        <!-- Input -->
        <div class="p-4 border-t bg-white" id="chatInputContainer" style="display: none;">
            <form id="chatForm" onsubmit="checkGuestAndSend(event)" class="flex items-center space-x-2">
                <input type="hidden" id="conversationId" value="">
                <input type="text" id="chatInput" placeholder="Type your message..." 
                    class="flex-1 px-4 py-2 border border-gray-200 rounded-full focus:border-halal-green focus:outline-none text-sm">
                <button type="submit" class="w-10 h-10 bg-halal-green text-white rounded-full flex items-center justify-center hover:bg-halal-dark transition-colors">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
// Pusher Configuration
const PUSHER_APP_KEY = @json(config("broadcasting.connections.pusher.key"));
const PUSHER_CLUSTER = @json(config("broadcasting.connections.pusher.options.cluster"));

// Chat Welcome Messages (from settings)
const welcomeMessage = @json($chatReplies['_welcome_message'] ?? 'Hello! How can I help you today?');
const welcomeSubtitle = @json($chatReplies['_welcome_subtitle'] ?? 'Our team typically replies within minutes');

// Initialize Pusher for real-time updates
let pusher = null;
let channel = null;

function initPusher(convId) {
    try {
        if (PUSHER_APP_KEY && !pusher) {
            pusher = new Pusher(PUSHER_APP_KEY, {
                cluster: PUSHER_CLUSTER,
                encrypted: true
            });
        }
        
        if (pusher && convId) {
            // Unsubscribe from previous channel if exists
            if (channel) {
                pusher.unsubscribe(channel);
            }
            // Subscribe to conversation-specific channel
            channel = pusher.subscribe('chat.' + convId);
            channel.bind('message.sent', function(data) {
                console.log('Received message:', data);
                // Add admin message to chat
                if (data.message && data.sender_type === 'admin') {
                    addMessage(data.message, 'bot');
                }
                // Show notification
                showChatNotification();
            });
        }
    } catch (e) {
        console.log('Pusher initialization failed:', e);
    }
    
    // Always start polling for typing indicator (database-based, doesn't require Pusher)
    startTypingPolling();
}

// Polling for typing indicator (database-based)
let typingPollInterval = null;

function startTypingPolling() {
    if (typingPollInterval) return;
    
    console.log('Starting typing polling');
    typingPollInterval = setInterval(() => {
        checkAdminTypingStatus();
    }, 1000); // Check every 1 second
}

function checkAdminTypingStatus() {
    if (!conversationId) return;
    
    fetch('/chat/check-typing?conversation_id=' + conversationId)
        .then(res => res.json())
        .then(data => {
            if (data.admin_is_typing) {
                showTypingIndicator();
            } else {
                hideTypingIndicator();
            }
        })
        .catch(err => console.log('Error checking typing status:', err));
}

function showChatNotification() {
    const notification = document.getElementById('chatNotification');
    if (notification) {
        // Increment unread count
        window.unreadCount = (window.unreadCount || 0) + 1;
        notification.textContent = window.unreadCount;
        notification.style.display = 'block';
        console.log('Chat notification shown, count:', window.unreadCount);
    }
    
    // Blink page title when chat is not open
    if (!chatOpen) {
        let originalTitle = document.title;
        let blinkInterval = setInterval(() => {
            if (chatOpen) {
                document.title = originalTitle;
                clearInterval(blinkInterval);
                return;
            }
            document.title = document.title === originalTitle ? '💬 New Message!' : originalTitle;
        }, 1000);
        
        // Store interval ID for cleanup
        window.chatTitleBlink = blinkInterval;
    }
}

let chatOpen = false;
let conversationId = localStorage.getItem('chat_conversation_id') || '';
let messagePollingInterval = null;

// Initialize unread count
if (typeof window.unreadCount === 'undefined') {
    window.unreadCount = 0;
}

// Check if guest already exists on page load
function checkExistingGuest() {
    fetch('/chat/check-guest', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        console.log('Check guest response:', data);
        
        if (data.is_logged_in) {
            // User is logged in
            if (data.exists && data.conversation_id) {
                // User has existing conversation - load it
                conversationId = data.conversation_id;
                document.getElementById('conversationId').value = conversationId;
                localStorage.setItem('chat_conversation_id', conversationId);
                
                // Hide guest form, show input
                document.getElementById('guestForm').style.display = 'none';
                document.getElementById('chatInputContainer').style.display = 'flex';
                
                // Initialize Pusher
                initPusher(conversationId);
                startMessagePolling();
                setupTypingListener();
                
                console.log('Logged in user with existing conversation:', data.user_name);
            } else {
                // Logged in user but no conversation - create one automatically
                createLoggedInConversation();
            }
        } else if (data.exists && data.conversation_id) {
            // Guest exists, set conversation ID
            conversationId = data.conversation_id;
            document.getElementById('conversationId').value = conversationId;
            localStorage.setItem('chat_conversation_id', conversationId);
            
            // Show input, hide guest form
            document.getElementById('guestForm').style.display = 'none';
            document.getElementById('chatInputContainer').style.display = 'flex';
            
            // Initialize Pusher
            initPusher(conversationId);
            startMessagePolling();
            setupTypingListener();
            
            console.log('Returning guest detected:', data.guest_name);
        } else if (conversationId) {
            // Has old conversation ID but no guest info - need to re-register
            // Clear localStorage to force new registration
            localStorage.removeItem('chat_conversation_id');
            conversationId = '';
        }
    })
    .catch(err => console.log('Error checking guest:', err));
}

// Create conversation for logged in user
function createLoggedInConversation() {
    fetch('/chat/register-logged-in', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token())
        }
    })
    .then(res => res.json())
    .then(data => {
        console.log('Logged in conversation response:', data);
        if (data.success) {
            conversationId = data.conversation_id;
            document.getElementById('conversationId').value = conversationId;
            localStorage.setItem('chat_conversation_id', conversationId);
            
            // Hide guest form, show input
            document.getElementById('guestForm').style.display = 'none';
            document.getElementById('chatInputContainer').style.display = 'flex';
            
            // Initialize Pusher
            initPusher(conversationId);
            startMessagePolling();
            setupTypingListener();
            
            // Show welcome message
            addMessage('Welcome back! How can I help you today?', 'bot');
        }
    })
    .catch(err => console.log('Error creating logged in conversation:', err));
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    checkExistingGuest();
});

// Reset chat on logout - clear localStorage and stop polling
function resetChatOnLogout() {
    // Clear localStorage
    localStorage.removeItem('chat_conversation_id');

    // Reset variables
    conversationId = '';
    document.getElementById('conversationId').value = '';

    // Reset unread count and hide notification
    window.unreadCount = 0;
    const notification = document.getElementById('chatNotification');
    if (notification) {
        notification.style.display = 'none';
        notification.textContent = '0';
    }

    // Stop polling
    stopMessagePolling();

    // Unsubscribe from Pusher channel
    if (channel && pusher) {
        pusher.unsubscribe(channel);
        channel = null;
    }

    // Reset UI - show welcome message
    const messagesContainer = document.getElementById('chatMessages');
    if (messagesContainer) {
        messagesContainer.innerHTML = `
            <div class="flex items-start space-x-2">
                <div class="w-8 h-8 bg-halal-green rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-headset text-white text-sm"></i>
                </div>
                <div class="bg-white p-3 rounded-lg rounded-tl-none shadow-sm max-w-[80%]">
                    <p class="text-sm text-gray-700">${welcomeMessage || 'Hello! How can I help you today?'}</p>
                    <p class="text-xs text-gray-400 mt-1">${welcomeSubtitle || 'Our team typically replies within minutes'}</p>
                </div>
            </div>
        `;
    }

    // Close chat window if open
    const chatWindow = document.getElementById('chatWindow');
    if (chatWindow) {
        chatWindow.classList.add('hidden');
    }

    // Reset chat toggle button
    const chatToggle = document.getElementById('chatToggle');
    if (chatToggle) {
        chatToggle.innerHTML = '<i class="bi bi-chat-dots-fill text-xl group-hover:scale-110 transition-transform"></i>';
    }

    chatOpen = false;

    console.log('Chat reset on logout');
}

// Register guest user
function registerGuest(event) {
    event.preventDefault();
    
    const name = document.getElementById('guestName').value.trim();
    const phone = document.getElementById('guestPhone').value.trim();
    const existingConvId = document.getElementById('conversationId').value;
    
    if (!name || !phone) {
        alert('Please enter your name and mobile number');
        return;
    }
    
    fetch('/chat/register-guest', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token())
        },
        body: JSON.stringify({
            name: name,
            phone: phone
        })
    })
    .then(res => res.json())
    .then(data => {
        console.log('Logged in conversation response:', data);
        if (data.success) {
            conversationId = data.conversation_id;
            document.getElementById('conversationId').value = conversationId;
            localStorage.setItem('chat_conversation_id', conversationId);
            
            // Hide guest form, show input
            document.getElementById('guestForm').style.display = 'none';
            document.getElementById('chatInput').parentElement.style.display = 'flex';
            
            // Initialize Pusher
            initPusher(conversationId);
            startMessagePolling();
            
            // Show appropriate message
            if (data.restored) {
                addMessage('Welcome back, ' + data.guest_name + '! Your previous conversation has been restored.', 'bot');
            } else {
                addMessage('Thank you! Your information has been saved. You can now start chatting.', 'bot');
            }
        }
    })
    .catch(err => {
        console.error('Error registering guest:', err);
        alert('Failed to register. Please try again. Check console for details.');
    });
}

// Check if guest registration is required before sending message
function checkGuestAndSend(event) {
    event.preventDefault();
    
    const message = document.getElementById('chatInput').value.trim();
    const convId = document.getElementById('conversationId').value;
    
    // If no conversation, show guest form
    if (!convId) {
        document.getElementById('guestForm').style.display = 'block';
        document.getElementById('chatInput').parentElement.style.display = 'none';
        return;
    }
    
    // Send the message
    sendMessage(event);
}

// Listen for logout events
document.addEventListener('click', function(e) {
    // Check if logout link was clicked
    const logoutLink = e.target.closest('[href*="logout"], [href*="logout"]');
    if (logoutLink) {
        // Delay slightly to allow logout to process
        setTimeout(resetChatOnLogout, 100);
    }
});

// Also listen for Laravel Fortify or other SPA logout forms
document.addEventListener('submit', function(e) {
    if (e.target.action && (e.target.action.includes('logout') || e.target.id === 'logout-form')) {
        setTimeout(resetChatOnLogout, 100);
    }
});

// Initialize Pusher if conversation ID exists
if (conversationId) {
    initPusher(conversationId);
    startMessagePolling();
}

// Poll for new messages every 3 seconds
function startMessagePolling() {
    console.log('Starting message polling...');
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    messagePollingInterval = setInterval(() => {
        const convId = document.getElementById('conversationId').value;
        console.log('Polling for messages, conversation ID:', convId);
        if (convId) {
            loadChatHistory();
        }
    }, 1000); // Poll every 1 second for more real-time feel
}

function stopMessagePolling() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
        messagePollingInterval = null;
    }
}

// Set initial conversation ID if exists
if (conversationId) {
    document.getElementById('conversationId').value = conversationId;
}

function toggleChat() {
    chatOpen = !chatOpen;
    const chatWindow = document.getElementById('chatWindow');
    const chatToggle = document.getElementById('chatToggle');
    const chatNotification = document.getElementById('chatNotification');
    const whatsappWidget = document.getElementById('whatsapp-widget');
    const guestForm = document.getElementById('guestForm');
    const chatInputContainer = document.getElementById('chatInputContainer');
    const convId = document.getElementById('conversationId').value;
    
    if (chatOpen) {
        // Stop title blinking
        if (window.chatTitleBlink) {
            clearInterval(window.chatTitleBlink);
            window.chatTitleBlink = null;
        }
        // Restore original title
        document.title = 'E-Commerce';
        
        // Reset unread count and hide notification badge
        window.unreadCount = 0;
        if (chatNotification) {
            chatNotification.style.display = 'none';
            chatNotification.textContent = '0';
        }
        
        chatWindow.classList.remove('hidden');
        chatToggle.innerHTML = '<i class="bi bi-x-lg text-xl"></i>';
        // Hide WhatsApp widget when chat is open
        if (whatsappWidget) {
            whatsappWidget.classList.add('hidden');
        }
        
        // Show guest form if no conversation, otherwise show input
        if (!convId) {
            if (guestForm) guestForm.style.display = 'block';
            if (chatInputContainer) chatInputContainer.style.display = 'none';
        } else {
            if (guestForm) guestForm.style.display = 'none';
            if (chatInputContainer) chatInputContainer.style.display = 'flex';
        }
        
        // Reset loaded flag to show welcome message
        const messagesContainer = document.getElementById('chatMessages');
        if (messagesContainer) {
            delete messagesContainer.dataset.loaded;
        }
        // Load chat history
        loadChatHistory();
    } else {
        chatWindow.classList.add('hidden');
        chatToggle.innerHTML = '<i class="bi bi-chat-dots-fill text-xl group-hover:scale-110 transition-transform"></i>';
        // Show WhatsApp widget when chat is closed
        if (whatsappWidget) {
            whatsappWidget.classList.remove('hidden');
        }
    }
}

function loadChatHistory() {
    const convId = document.getElementById('conversationId').value;
    if (!convId) return;
    
    fetch(`/chat/messages?conversation_id=${convId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => {
        console.log('Messages response status:', res.status);
        return res.json();
    })
    .then(data => {
        console.log('Messages received:', data);
        const messagesContainer = document.getElementById('chatMessages');
        
        // Check if this is first load (no messages displayed yet)
        const isFirstLoad = !messagesContainer.dataset.loaded;
        
        if (isFirstLoad) {
            // First load - show welcome message and all messages
            messagesContainer.innerHTML = `
                <div class="flex items-start space-x-2">
                    <div class="w-8 h-8 bg-halal-green rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-headset text-white text-sm"></i>
                    </div>
                    <div class="bg-white p-3 rounded-lg rounded-tl-none shadow-sm max-w-[80%]">
                        <p class="text-sm text-gray-700">${welcomeMessage || 'Hello! How can I help you today?'}</p>
                    </div>
                </div>
            `;
            
            // Add existing messages
            data.forEach(msg => {
                addMessage(msg.message, msg.sender_type === 'admin' ? 'bot' : 'user');
            });
            
            messagesContainer.dataset.loaded = 'true';
            messagesContainer.dataset.messageCount = data.length;
            console.log('First load, message count:', data.length);
        } else {
            // Polling update - check for new messages
            const storedCount = parseInt(messagesContainer.dataset.messageCount) || 0;
            console.log('Polling - stored:', storedCount, 'server:', data.length);
            
            if (data.length > storedCount) {
                // New messages exist
                const newMessages = data.slice(storedCount);
                console.log('Adding new messages:', newMessages.length);
                newMessages.forEach(msg => {
                    addMessage(msg.message, msg.sender_type === 'admin' ? 'bot' : 'user');
                });
                messagesContainer.dataset.messageCount = data.length;
                
                // Show notification for any new message
                if (newMessages.length > 0) {
                    showChatNotification();
                }
            }
        }
    })
    .catch(err => console.log('No previous messages'));
}

function sendMessage(event) {
    event.preventDefault();
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    let convId = document.getElementById('conversationId').value;
    
    if (!message) return;
    
    // Add user message immediately with a temporary ID
    const tempId = 'temp_' + Date.now();
    addMessage(message, 'user', tempId);
    input.value = '';
    
    // Send to backend - let backend handle conversation creation
    fetch(@json(route("chat.send")), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            message: message,
            conversation_id: convId || null
        })
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Network response was not ok');
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            // Store conversation ID
            conversationId = data.conversation_id;
            document.getElementById('conversationId').value = conversationId;
            localStorage.setItem('chat_conversation_id', conversationId);
            
            // Initialize Pusher for this conversation
            initPusher(conversationId);
            
            // Start polling for new messages
            startMessagePolling();
            
            // AI auto-reply is handled by backend now
        } else {
            console.error('Server error:', data);
        }
    })
    .catch(err => {
        console.error('Error sending message:', err);
    });
}

function sendQuickMessage(message) {
    document.getElementById('chatInput').value = message;
    sendMessage(new Event('submit'));
}

// Typing indicator functions
let typingTimeout = null;

function showTypingIndicator() {
    console.log('showTypingIndicator called');
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
        typingIndicator.classList.remove('hidden');
        console.log('Typing indicator shown');
        // Auto-hide after 5 seconds
        if (typingTimeout) clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            hideTypingIndicator();
        }, 5000);
    }
}

function hideTypingIndicator() {
    console.log('hideTypingIndicator called');
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
        typingIndicator.classList.add('hidden');
    }
}

// Send typing status to server
function sendTypingStatus(isTyping) {
    const convId = document.getElementById('conversationId').value;
    if (!convId) return;
    
    fetch('/chat/typing', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token())
        },
        body: JSON.stringify({
            conversation_id: convId,
            is_typing: isTyping
        })
    }).catch(() => {});
}

// Listen for typing input
function setupTypingListener() {
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        let typingTimer = null;
        chatInput.addEventListener('input', function() {
            sendTypingStatus(true);
            // Clear typing status after user stops typing for 1 second
            if (typingTimer) clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                sendTypingStatus(false);
            }, 1000);
        });
    }
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function addMessage(text, type, tempId = null) {
    const messagesContainer = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    const escapedText = escapeHtml(text);
    
    // Skip if this exact message already exists (avoid duplication)
    if (tempId) {
        messageDiv.dataset.tempId = tempId;
    }
    const existingMessages = messagesContainer.querySelectorAll('.chat-message-text');
    for (let msg of existingMessages) {
        if (msg.textContent === text) {
            return; // Skip duplicate
        }
    }
    
    if (type === 'user') {
        messageDiv.className = 'flex items-start space-x-2 justify-end user-message';
        messageDiv.innerHTML = `
            <div class="bg-halal-green text-white p-3 rounded-lg rounded-tr-none max-w-[80%]">
                <p class="text-sm chat-message-text">${escapedText}</p>
            </div>
            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="bi bi-person-fill text-gray-600 text-sm"></i>
            </div>
        `;
    } else {
        messageDiv.className = 'flex items-start space-x-2 bot-message';
        messageDiv.innerHTML = `
            <div class="w-8 h-8 bg-halal-green rounded-full flex items-center justify-center flex-shrink-0">
                <i class="bi bi-headset text-white text-sm"></i>
            </div>
            <div class="bg-white p-3 rounded-lg rounded-tl-none shadow-sm max-w-[80%]">
                <p class="text-sm text-gray-700 chat-message-text">${escapedText}</p>
            </div>
        `;
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function getAIResponse(message) {
    const lowerMessage = message.toLowerCase();
    
    // Auto-reply responses (can be customized from admin)
    const autoReplies = @json($chatReplies);
    
    if (lowerMessage.includes('track') || lowerMessage.includes('order')) {
        return autoReplies.track_order;
    } else if (lowerMessage.includes('delivery') || lowerMessage.includes('area')) {
        return autoReplies.delivery;
    } else if (lowerMessage.includes('payment') || lowerMessage.includes('pay')) {
        return autoReplies.payment;
    } else if (lowerMessage.includes('halal') || lowerMessage.includes('quality')) {
        return autoReplies.halal;
    } else if (lowerMessage.includes('return') || lowerMessage.includes('refund')) {
        return autoReplies.return_refund;
    } else if (lowerMessage.includes('price') || lowerMessage.includes('cost')) {
        return autoReplies.price;
    } else if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('salam')) {
        return autoReplies.greeting;
    } else {
        return autoReplies.default;
    }
}
</script>
