<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Setting;
use App\Rules\Phone;
use App\Events\ChatMessageSent;
use App\Events\UserStatusChanged;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    /**
     * Display chat interface.
     */
    public function index()
    {
        if (auth()->check()) {
            $conversations = Chat::where('user_id', auth()->id())
                ->latest()
                ->get();
        } else {
            $conversations = collect();
        }

        return view('themes.general.chat.index', compact('conversations'));
    }

    /**
     * Send a chat message.
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Get or create conversation
        if ($request->conversation_id) {
            // Verify conversation exists, if not create new one
            $conversation = Chat::find($request->conversation_id);
            if (!$conversation) {
                $conversation = Chat::create([
                    'user_id' => auth()->id() ?? null,
                    'session_id' => session()->getId(),
                    'status' => 'pending',
                ]);
            } else {
                // If user replies to an existing conversation, change status to pending
                if (in_array($conversation->status, ['replied', 'closed'])) {
                    $conversation->status = 'pending';
                    $conversation->save();
                }
            }
        } else {
            $conversation = Chat::create([
                'user_id' => auth()->id() ?? null,
                'session_id' => session()->getId(),
                'status' => 'pending',
            ]);
        }

        // Check if guest info is required
        if (!$conversation->user_id && (!$conversation->guest_name || !$conversation->guest_phone)) {
            return response()->json([
                'success' => false,
                'error' => 'Guest information required',
                'requires_guest_info' => true,
            ], 400);
        }

        // Handle attachments
        $attachments = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $path = $attachment->store('chat-attachments', 'public');
            $attachments = [
                'filename' => $attachment->getClientOriginalName(),
                'path' => \Illuminate\Support\Facades\Storage::url($path),
                'type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
            ];
        }

        // Store user message
        $userMessage = $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_id' => auth()->id() ?? null,
            'message' => $request->message,
            'attachments' => $attachments,
        ]);

        // Broadcast message for live chat
        try {
            broadcast(new ChatMessageSent($userMessage))->toOthers();
        } catch (\Exception $e) {
            // Broadcasting failed, continue without broadcasting
        }

        // Generate and save auto-reply (Chatbot)
        $autoReply = $this->generateAutoReply($request->message);
        if ($autoReply) {
            $botMessage = $conversation->messages()->create([
                'sender_type' => 'admin',
                'sender_id' => null,
                'message' => $autoReply,
            ]);
            
            // Broadcast bot message
            try {
                broadcast(new ChatMessageSent($botMessage))->toOthers();
            } catch (\Exception $e) {
                // Broadcasting failed
            }
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'message' => $userMessage,
        ]);
    }

    /**
     * Handle typing indicator.
     */
    public function typing(Request $request)
    {
        $conversationId = $request->conversation_id;
        $isTyping = filter_var($request->is_typing, FILTER_VALIDATE_BOOLEAN);
        
        $conversation = Chat::find($conversationId);
        if ($conversation) {
            // Set typing status with 5 second expiration
            $conversation->update([
                'user_is_typing' => $isTyping,
                'typing_expires_at' => $isTyping ? now()->addSeconds(5) : null,
            ]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Check typing status (for polling).
     */
    public function checkTyping(Request $request)
    {
        $conversationId = $request->conversation_id;
        
        $conversation = Chat::find($conversationId);
        
        if (!$conversation) {
            return response()->json([
                'admin_is_typing' => false,
                'user_is_typing' => false,
            ]);
        }
        
        // Check if typing has expired
        $adminTyping = $conversation->admin_is_typing;
        $userTyping = $conversation->user_is_typing;
        
        if ($conversation->typing_expires_at && now()->greaterThan($conversation->typing_expires_at)) {
            // Typing has expired, reset both
            if ($adminTyping) {
                $conversation->update(['admin_is_typing' => false]);
                $adminTyping = false;
            }
            if ($userTyping) {
                $conversation->update(['user_is_typing' => false, 'typing_expires_at' => null]);
                $userTyping = false;
            }
        }
        
        return response()->json([
            'admin_is_typing' => $adminTyping,
            'user_is_typing' => $userTyping,
        ]);
    }

    /**
     * Generate auto-reply based on user message.
     */
    private function generateAutoReply($message)
    {
        // Get settings or use defaults
        $replies = [
            'track' => \App\Models\Setting::get('chat_reply_track_order', 'To track your order, please log in to your account and visit the My Orders section. You can also contact our support with your order number.'),
            'delivery' => \App\Models\Setting::get('chat_reply_delivery', 'We offer free delivery on orders over ৳500! Standard delivery takes 1-3 business days within Dhaka and 3-5 days outside Dhaka.'),
            'payment' => \App\Models\Setting::get('chat_reply_payment', 'We accept multiple payment methods: bKash, Nagad, Rocket, Credit/Debit Cards, and Cash on Delivery.'),
            'return' => \App\Models\Setting::get('chat_reply_return', 'We have a 7-day return policy. If not satisfied, please contact our support team within 7 days of delivery.'),
            'halal' => \App\Models\Setting::get('chat_reply_halal', 'All our meat and food products are 100% Halal certified. We source from trusted suppliers.'),
            'deal' => \App\Models\Setting::get('chat_reply_price', 'Check our Deals section for the latest offers! We have weekly specials and seasonal discounts.'),
            'greeting' => \App\Models\Setting::get('chat_reply_greeting', 'Wa Alaikum Assalam! Welcome to Halal Food Store. How can I help you today?'),
        ];

        $message = strtolower($message);
        
        if (strpos($message, 'track') !== false || strpos($message, 'order') !== false) {
            return $replies['track'];
        }
        
        if (strpos($message, 'delivery') !== false || strpos($message, 'shipping') !== false || strpos($message, 'area') !== false) {
            return $replies['delivery'];
        }
        
        if (strpos($message, 'payment') !== false || strpos($message, 'pay') !== false) {
            return $replies['payment'];
        }
        
        if (strpos($message, 'return') !== false || strpos($message, 'refund') !== false) {
            return $replies['return'];
        }
        
        if (strpos($message, 'halal') !== false || strpos($message, 'quality') !== false) {
            return $replies['halal'];
        }
        
        if (strpos($message, 'deal') !== false || strpos($message, 'discount') !== false || strpos($message, 'offer') !== false || strpos($message, 'price') !== false) {
            return $replies['deal'];
        }
        
        if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false || strpos($message, 'hey') !== false || strpos($message, 'assalam') !== false || strpos($message, 'salam') !== false) {
            return $replies['greeting'];
        }
        
        return null;
    }

    /**
     * Check if guest already exists by session.
     */
    public function checkGuest(Request $request)
    {
        // First check if user is logged in
        if (auth()->check()) {
            // Check if user has any existing conversation
            $conversation = Chat::where('user_id', auth()->id())
                ->latest()
                ->first();
            
            if ($conversation) {
                return response()->json([
                    'exists' => true,
                    'conversation_id' => $conversation->id,
                    'is_logged_in' => true,
                    'user_name' => auth()->user()->name,
                    'user_email' => auth()->user()->email,
                    'status' => $conversation->status,
                ]);
            }
            
            // User is logged in but no conversation yet
            return response()->json([
                'exists' => false,
                'is_logged_in' => true,
                'user_name' => auth()->user()->name,
                'user_email' => auth()->user()->email,
            ]);
        }
        
        $sessionId = session()->getId();
        
        // Check if there's an existing conversation with this session
        $conversation = Chat::where('session_id', $sessionId)
            ->whereNotNull('guest_name')
            ->whereNotNull('guest_phone')
            ->first();
        
        if ($conversation) {
            return response()->json([
                'exists' => true,
                'conversation_id' => $conversation->id,
                'guest_name' => $conversation->guest_name,
                'guest_phone' => $conversation->guest_phone,
                'status' => $conversation->status,
                'is_logged_in' => false,
            ]);
        }
        
        return response()->json([
            'exists' => false,
            'is_logged_in' => false,
        ]);
    }

    /**
     * Register guest user with name and phone.
     */
    public function registerGuest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', new Phone],
            'conversation_id' => 'nullable|integer',
        ]);

        $sessionId = session()->getId();
        $phone = $request->phone;
        
        // First, check if this phone number already exists in any conversation
        // This allows restoring conversation even after clearing cookies
        $existingByPhone = Chat::where('guest_phone', $phone)
            ->whereNotNull('guest_name')
            ->whereNotNull('guest_phone')
            ->first();
        
        if ($existingByPhone) {
            // Restore existing conversation - link new session to it
            $existingByPhone->session_id = $sessionId;
            $existingByPhone->save();
            
            return response()->json([
                'success' => true,
                'conversation_id' => $existingByPhone->id,
                'guest_name' => $existingByPhone->guest_name,
                'guest_phone' => $existingByPhone->guest_phone,
                'restored' => true, // Flag to show welcome back message
            ]);
        }
        
        // Check if there's already an existing conversation with this session
        $existingBySession = Chat::where('session_id', $sessionId)
            ->whereNotNull('guest_name')
            ->whereNotNull('guest_phone')
            ->first();
        
        if ($existingBySession) {
            // Return existing conversation
            return response()->json([
                'success' => true,
                'conversation_id' => $existingBySession->id,
                'guest_name' => $existingBySession->guest_name,
                'guest_phone' => $existingBySession->guest_phone,
            ]);
        }

        // Create or update conversation with guest info
        $conversation = null;
        
        if ($request->conversation_id) {
            $conversation = Chat::find($request->conversation_id);
        }
        
        if (!$conversation) {
            // Create new conversation for guest
            $conversation = Chat::create([
                'user_id' => null,
                'session_id' => $sessionId,
                'status' => 'new',
                'guest_name' => $request->name,
                'guest_phone' => $phone,
            ]);
        } else {
            // Update existing conversation with guest info
            $conversation->guest_name = $request->name;
            $conversation->guest_phone = $phone;
            $conversation->session_id = $sessionId; // Ensure session is linked
            $conversation->save();
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'guest_name' => $conversation->guest_name,
            'guest_phone' => $conversation->guest_phone,
        ]);
    }

    /**
     * Register or get conversation for logged in user.
     */
    public function registerLoggedIn(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'error' => 'User not authenticated',
            ], 401);
        }

        // Check if user already has a conversation
        $conversation = Chat::where('user_id', auth()->id())
            ->latest()
            ->first();

        if (!$conversation) {
            // Create new conversation for logged in user
            $conversation = Chat::create([
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'status' => 'new',
            ]);
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'user_name' => auth()->user()->name,
            'user_email' => auth()->user()->email,
        ]);
    }

    /**
     * Get chat messages.
     */
    public function messages(Request $request)
    {
        // Allow both with and without conversation_id for flexibility
        if (!$request->conversation_id) {
            return response()->json([]);
        }
        
        // Verify conversation exists
        $chat = Chat::find($request->conversation_id);
        if (!$chat) {
            return response()->json([]);
        }

        $messages = ChatMessage::where('chat_id', $request->conversation_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Update user online status.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'is_online' => 'required|boolean',
        ]);

        $userId = auth()->id() ?? $request->session_id;
        $userName = auth()->user()->name ?? 'Guest';

        try {
            broadcast(new UserStatusChanged($userId, $request->is_online, $userName))->toOthers();
        } catch (\Exception $e) {
            // Broadcasting failed
        }

        return response()->json(['success' => true]);
    }

    /**
     * AI Chatbot response.
     */
    public function aiChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $apiKey = Setting::where('key', 'openai_api_key')->value('value');

        if (!$apiKey) {
            // Return a default response if OpenAI is not configured
            return response()->json([
                'success' => true,
                'reply' => $this->getLocalResponse($request->message),
            ]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful customer support assistant for an e-commerce website. Be friendly and helpful.'],
                    ['role' => 'user', 'content' => $request->message],
                ],
                'max_tokens' => 500,
            ]);

            $data = $response->json();

            return response()->json([
                'success' => true,
                'reply' => $data['choices'][0]['message']['content'] ?? 'I apologize, I could not process your request. Please try again.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'reply' => $this->getLocalResponse($request->message),
            ]);
        }
    }

    /**
     * Live chat page.
     */
    public function live()
    {
        return view('themes.general.chat.live');
    }

    /**
     * Get local response for common queries.
     */
    private function getLocalResponse($message)
    {
        return $this->generateAutoReply($message);
    }
}
