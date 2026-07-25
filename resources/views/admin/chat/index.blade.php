@extends('admin.layouts.app')

@section('title', 'Live Chat')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Live Chat</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" onclick="refreshConversations()">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-chat-dots"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Chats</span>
            <span class="stat-card-value" id="statTotal">0</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-chat-square-text"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">New</span>
            <span class="stat-card-value" id="statNew">0</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Pending</span>
            <span class="stat-card-value" id="statPending">0</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value" id="statActive">0</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-check2-all"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Closed</span>
            <span class="stat-card-value" id="statClosed">0</span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Conversations List -->
    <div class="col-lg-4 col-md-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Conversations</h5>
                    <span class="badge bg-primary" id="conversationCount">0</span>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Filters -->
                <div class="border-bottom p-2">
                    <div class="filter-tabs d-flex flex-wrap gap-1">
                        <button class="btn btn-sm btn-outline-secondary filter-btn active position-relative flex-fill" data-filter="all">
                            All <span class="badge rounded-pill bg-secondary" id="badgeAll">0</span>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary filter-btn position-relative flex-fill" data-filter="new">
                            New <span class="badge rounded-pill bg-info" id="badgeNew">0</span>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary filter-btn position-relative flex-fill" data-filter="closed">
                            Closed <span class="badge rounded-pill bg-dark" id="badgeClosed">0</span>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary filter-btn position-relative flex-fill" data-filter="replied">
                            Replied <span class="badge rounded-pill bg-success" id="badgeReplied">0</span>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary filter-btn position-relative flex-fill" data-filter="pending">
                            Pending <span class="badge rounded-pill bg-warning" id="badgePending">0</span>
                        </button>
                    </div>
                </div>
                <!-- Search -->
                <div class="p-2 border-bottom">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="conversationSearch" class="form-control" placeholder="Search conversations...">
                    </div>
                </div>
                <!-- Conversations List -->
                <div class="list-group list-group-flush" id="conversationsList" style="max-height: 450px; overflow-y: auto;">
                    <div class="list-group-item text-center text-muted py-4" id="noConversations">
                        <i class="bi bi-chat-dots" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2">No conversations yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chat Area -->
    <div class="col-lg-8 col-md-7">
        <div class="card border-0 shadow-sm" id="chatCard" style="min-height: 500px; display: none;">
            <!-- Chat Header -->
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <h6 class="mb-0" id="chatUserName">Select a conversation</h6>
                        <small class="text-muted" id="chatUserEmail"></small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" id="markUnreadBtn" onclick="markAsUnread()" title="Mark as Unread" style="display: none;">
                        <i class="bi bi-envelope"></i> Mark as Unread
                    </button>
                    <button class="btn btn-sm btn-outline-success" id="markReadBtn" onclick="markAsRead()" title="Mark as Read">
                        <i class="bi bi-envelope-check"></i> Mark as Read
                    </button>
                    <select class="form-select form-select-sm" id="chatStatusSelect" style="width: auto;" onchange="updateChatStatus()">
                        <option value="new">New</option>
                        <option value="pending">Pending</option>
                        <option value="replied">Replied</option>
                        <option value="closed">Closed</option>
                    </select>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteChat()" title="Delete Chat">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="closeChat()" title="Close Chat">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            
            <!-- Messages Container -->
            <div class="card-body" style="height: 350px; overflow-y: auto;" id="messagesContainer">
                <div class="text-center text-muted py-5" id="noMessages">
                    <i class="bi bi-chat-square-text" style="font-size: 3rem;"></i>
                    <p class="mt-2">Select a conversation to view messages</p>
                </div>
                <div id="messagesList"></div>
            </div>
            
            <!-- Typing Indicator -->
            <div class="px-3 py-2" id="typingIndicator" style="display: none;">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="bg-light rounded p-2">
                        <div class="d-flex gap-1">
                            <span class="typing-dot" style="width: 8px; height: 8px; background: #0d6efd; border-radius: 50%; animation: typing 1.4s infinite both;"></span>
                            <span class="typing-dot" style="width: 8px; height: 8px; background: #0d6efd; border-radius: 50%; animation: typing 1.4s infinite both; animation-delay: 0.2s;"></span>
                            <span class="typing-dot" style="width: 8px; height: 8px; background: #0d6efd; border-radius: 50%; animation: typing 1.4s infinite both; animation-delay: 0.4s;"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Message Input -->
            <div class="card-footer bg-white">
                <!-- Quick Replies -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-medium">Quick Replies</span>
                        <div class="btn-group btn-group-sm" id="quickReplyCategoryTabs">
                            <button type="button" class="btn btn-outline-secondary active" data-category="all">All</button>
                        </div>
                    </div>
                    <div id="quickRepliesContainer">
                        <span class="text-muted small">Loading quick replies...</span>
                    </div>
                </div>
                <form id="sendMessageForm" onsubmit="sendMessage(event)">
                    @csrf
                    <input type="hidden" id="currentConversationId" value="">
                    <div class="input-group">
                        <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- No Chat Selected -->
        <div class="card border-0 shadow-sm" id="noChatCard">
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 500px;">
                <div class="text-center text-muted">
                    <i class="bi bi-chat-dots" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Select a Conversation</h5>
                    <p>Choose a conversation from the list to start chatting</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification permission modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enable Notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Allow notifications to receive alerts for new messages even when this tab is in the background.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Later</button>
                <button type="button" class="btn btn-primary" onclick="requestNotificationPermission()">Enable Notifications</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
/* Responsive filter tabs */
.filter-tabs .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.4rem;
    white-space: nowrap;
    min-width: 0;
}
.filter-tabs .btn .badge {
    font-size: 0.65rem;
    padding: 0.2em 0.4em;
}

/* On extra small screens, allow buttons to stack */
@media (max-width: 575.98px) {
    .filter-tabs {
        flex-direction: column;
    }
    .filter-tabs .btn {
        width: 100%;
        margin-bottom: 0.25rem;
    }
    .filter-tabs .btn:last-child {
        margin-bottom: 0;
    }
}

.avatar-circle {
    font-weight: 600;
}
.conversation-item {
    cursor: pointer;
    transition: all 0.2s;
}
.conversation-item:hover {
    background-color: #f8f9fa;
}
.list-group-item.active {
    background-color: #0d6efd;
    border-left: 3px solid #0d6efd;
}
.list-group-item.active .text-muted,
.list-group-item.active small {
    color: #ffffff !important;
}
.message-bubble {
    max-width: 75%;
    word-wrap: break-word;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border-radius: 1.125rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease;
}

.message-bubble:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.08);
}

.admin-message {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    margin-left: auto;
    border: none;
    border-radius: 1.125rem 1.125rem 0.25rem 1.125rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.user-message {
    background-color: #ffffff;
    color: #374151;
    border: 1px solid #e5e7eb;
    margin-right: auto;
    border-radius: 1.125rem 1.125rem 1.125rem 0.25rem;
}

.user-message .text-muted {
    color: #6c757d !important;
}
/* Typing animation */
@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-4px);
    }
}
.typing-indicator {
    display: flex;
    gap: 4px;
}
.unread-badge {
    position: absolute;
    top: 5px;
    right: 5px;
}
/* Online/Offline Status Indicators */
.online-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}
.online-indicator.online {
    background-color: #28a745;
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.3);
    animation: pulse 2s infinite;
}
.online-indicator.offline {
    background-color: #6c757d;
}
.online-indicator.typing {
    background-color: #ffc107;
    animation: pulse 1s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4); }
    70% { box-shadow: 0 0 0 6px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}
/* Attachment preview */
.attachment-preview {
    max-width: 200px;
    border-radius: 8px;
    margin-top: 5px;
}
.attachment-file {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 5px;
}
/* Message timestamp */
.message-time {
    font-size: 0.7rem;
    opacity: 0.7;
}
/* Sound notification */
.sound-toggle {
    cursor: pointer;
}
/* Toast notifications */
.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s ease;
}
.toast-notification.show {
    transform: translateY(0);
    opacity: 1;
}
.toast-content {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.toast-success .toast-content {
    border-left: 4px solid #28a745;
}
.toast-success .toast-content i {
    color: #28a745;
}
.toast-error .toast-content {
    border-left: 4px solid #dc3545;
}
.toast-error .toast-content i {
    color: #dc3545;
}
.toast-warning .toast-content {
    border-left: 4px solid #ffc107;
}
.toast-warning .toast-content i {
    color: #ffc107;
}
</style>
@endpush

@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
const PUSHER_APP_KEY = '{{ config("broadcasting.connections.pusher.key") }}';
const PUSHER_CLUSTER = '{{ config("broadcasting.connections.pusher.options.cluster") }}';

let pusher = null;
let globalPusherChannel = null;
let currentChatChannel = null;
try {
    if (PUSHER_APP_KEY && PUSHER_APP_KEY !== '') {
        pusher = new Pusher(PUSHER_APP_KEY, { cluster: PUSHER_CLUSTER, encrypted: true });
        initPusher();
    }
} catch (e) { /* Pusher unavailable, polling fallback active */ }

let soundEnabled = true;
const notificationSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2tleRQ3xf3s0p12EzWCAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');

let currentConversationId = null;
let currentFilter = 'all';
let refreshInterval = null;
let messagePollingInterval = null;
let typingPollInterval = null;
let typingTimeout = null;
let onlineUsers = {};
let isLoading = false;
let isSending = false;
let predefinedMessages = [];
let currentQuickReplyCategory = 'all';

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => { clearTimeout(timeout); func(...args); };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function getTimeAgo(dateString) {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return date.toLocaleDateString();
}

function getStatusBadge(status) {
    const badges = {
        'new': '<span class="badge bg-info">New</span>',
        'pending': '<span class="badge bg-warning">Pending</span>',
        'replied': '<span class="badge bg-success">Replied</span>',
        'closed': '<span class="badge bg-secondary">Closed</span>'
    };
    return badges[status] || '';
}

document.addEventListener('DOMContentLoaded', function() {
    loadConversations();
    loadPredefinedMessages();
    refreshInterval = setInterval(loadConversations, 10000);
    document.getElementById('conversationSearch').addEventListener('input', debounce(loadConversations, 300));
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); e.stopPropagation();
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            loadConversations();
        });
    });
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(e); }
    });
    setupTypingListener();
    addFileUpload();
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key >= '1' && e.key <= '9') {
            e.preventDefault();
            const index = parseInt(e.key) - 1;
            let visibleMessages = predefinedMessages;
            if (currentQuickReplyCategory !== 'all') {
                visibleMessages = predefinedMessages.filter(m => m.category === currentQuickReplyCategory);
            }
            if (visibleMessages[index]) sendQuickReply(visibleMessages[index].id, visibleMessages[index].message);
        }
        if ((e.ctrlKey || e.metaKey) && e.key === '0') {
            e.preventDefault();
            document.getElementById('messageInput').focus();
        }
    });
    if (Notification.permission === 'default') {
        setTimeout(() => { if (typeof $ !== 'undefined') $('#notificationModal').modal('show'); }, 3000);
    }
});

function loadConversations() {
    if (isLoading) return;
    isLoading = true;
    const search = document.getElementById('conversationSearch').value;
    const url = `{{ route('admin.chat.conversations') }}?filter=${currentFilter}&search=${encodeURIComponent(search)}&_t=${Date.now()}`;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            updateConversationsList(data.data?.data || data.data || data);
            updateStats(data);
        })
        .catch(err => showToast('Failed to load conversations', 'error'))
        .finally(() => { isLoading = false; });
}

function updateConversationsList(conversations) {
    const list = document.getElementById('conversationsList');
    const countBadge = document.getElementById('conversationCount');
    countBadge.textContent = conversations.length;
    if (conversations.length === 0) {
        list.innerHTML = '<div class="list-group-item text-center text-muted py-4" id="noConversations"><i class="bi bi-chat-dots" style="font-size: 2rem;"></i><p class="mb-0 mt-2">No conversations yet</p></div>';
        return;
    }
    let html = '';
    conversations.forEach(conv => {
        const isActive = currentConversationId == conv.id ? 'active' : '';
        let userName = 'Guest';
        if (conv.user && conv.user.name) userName = conv.user.name;
        else if (conv.guest_name) userName = conv.guest_name + ' (Guest)';
        const lastMessage = conv.last_message ? conv.last_message.message : 'No messages yet';
        const words = lastMessage.split(' ');
        const truncatedMessage = words.slice(0, 5).join(' ') + (words.length > 5 ? '...' : '');
        const timeAgo = conv.updated_at ? getTimeAgo(conv.updated_at) : '';
        const unread = conv.unread_count > 0 ? `<span class="badge bg-danger unread-badge">${conv.unread_count}</span>` : '';
        const statusBadge = getStatusBadge(conv.status);
        const safeUserName = escapeHtml(userName);
        const safeMessage = escapeHtml(truncatedMessage);
        const safeStatus = escapeHtml(conv.status);
        html += `<a href="#" class="list-group-item list-group-item-action ${isActive}" data-id="${conv.id}" onclick="selectConversation(${conv.id}, '${safeUserName.replace(/'/g, "\\'")}', '${safeStatus}'); return false;">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>${safeUserName}</strong>
                        ${unread}
                    </div>
                    <small class="d-block text-muted text-truncate">${safeMessage}</small>
                    <small class="text-muted">${timeAgo}</small>
                </div>
                <div class="ms-2">${statusBadge}</div>
            </div>
        </a>`;
    });
    list.innerHTML = html;
}

function updateStats(data) {
    const filter = currentFilter;
    document.getElementById('statTotal').textContent = (filter && filter !== 'all') ? (data.filtered_total || 0) : (data.total || 0);
    document.getElementById('statNew').textContent = data.new || 0;
    document.getElementById('statPending').textContent = data.pending || 0;
    document.getElementById('statActive').textContent = data.replied || 0;
    document.getElementById('statClosed').textContent = data.closed || 0;
    document.getElementById('badgeAll').textContent = data.total || 0;
    document.getElementById('badgeNew').textContent = data.new || 0;
    document.getElementById('badgePending').textContent = data.pending || 0;
    document.getElementById('badgeReplied').textContent = data.replied || 0;
    document.getElementById('badgeClosed').textContent = data.closed || 0;
}

function selectConversation(id, userName, status) {
    currentConversationId = id;
    document.getElementById('noChatCard').style.display = 'none';
    document.getElementById('chatCard').style.display = 'block';
    document.getElementById('chatUserName').textContent = userName;
    document.getElementById('currentConversationId').value = id;
    document.getElementById('chatStatusSelect').value = status;
    document.getElementById('markUnreadBtn').style.display = 'inline-block';
    document.querySelectorAll('#conversationsList .list-group-item').forEach(el => el.classList.remove('active'));
    const activeItem = document.querySelector(`#conversationsList .list-group-item[data-id="${id}"]`);
    if (activeItem) activeItem.classList.add('active');
    loadMessages(id);
    subscribeToChat(id);
}

function loadMessages(conversationId) {
    fetch(`{{ route('admin.chat.conversation', ':id') }}`.replace(':id', conversationId), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        displayMessages(data.messages || []);
        document.getElementById('chatUserEmail').textContent = data.user ? data.user.email : '';
        if (data.status) document.getElementById('chatStatusSelect').value = data.status;
    })
    .catch(err => showToast('Failed to load messages', 'error'));
}

function displayMessages(messages) {
    const container = document.getElementById('messagesList');
    const noMessages = document.getElementById('noMessages');
    if (messages.length === 0) { noMessages.style.display = 'block'; container.innerHTML = ''; return; }
    noMessages.style.display = 'none';
    let html = '';
    messages.forEach(msg => {
        const isAdmin = msg.sender_type === 'admin';
        const time = new Date(msg.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        const safeMessage = escapeHtml(msg.message);
        let attachmentHtml = '';
        if (msg.attachments) {
            const att = typeof msg.attachments === 'string' ? JSON.parse(msg.attachments) : msg.attachments;
            if (att && att.type) {
                if (att.type.startsWith('image/')) {
                    attachmentHtml = `<img src="${escapeHtml(att.path)}" class="attachment-preview" alt="attachment">`;
                } else {
                    attachmentHtml = `<div class="attachment-file"><i class="bi bi-file-earmark"></i> ${escapeHtml(att.filename)}</div>`;
                }
            }
        }
        html += `<div class="mb-3 d-flex ${isAdmin ? 'justify-content-end' : 'justify-content-start'}">
            <div class="message-bubble ${isAdmin ? 'admin-message' : 'user-message'}">
                <div>${safeMessage}</div>
                ${attachmentHtml}
                <small class="${isAdmin ? 'text-light' : 'text-muted'} message-time">${time}</small>
            </div>
        </div>`;
    });
    container.innerHTML = html;
    const messagesContainer = document.getElementById('messagesContainer');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function sendMessage(e) {
    e.preventDefault();
    if (isSending) return;
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    const fileInput = document.getElementById('attachmentInput');
    const file = fileInput ? fileInput.files[0] : null;
    if (!message && !file) return;
    if (!currentConversationId) return;
    isSending = true;
    const sendBtn = document.querySelector('#sendMessageForm button[type="submit"]');
    if (sendBtn) { sendBtn.disabled = true; sendBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>'; }
    sendTypingStatus(false);
    const doSend = (body, headers) => {
        fetch(`{{ route('admin.chat.send') }}`, { method: 'POST', headers: { ...headers, 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body })
            .then(res => res.json())
            .then(data => {
                input.value = '';
                if (fileInput) fileInput.value = '';
                const preview = document.getElementById('filePreview');
                if (preview) preview.innerHTML = '';
                loadMessages(currentConversationId);
                loadConversations();
            })
            .catch(err => showToast('Failed to send message', 'error'))
            .finally(() => {
                isSending = false;
                if (sendBtn) { sendBtn.disabled = false; sendBtn.innerHTML = '<i class="bi bi-send"></i>'; }
            });
    };
    if (file) {
        const formData = new FormData();
        formData.append('conversation_id', currentConversationId);
        if (message) formData.append('message', message);
        formData.append('attachment', file);
        doSend(formData, {});
    } else {
        doSend(JSON.stringify({ conversation_id: currentConversationId, message }), { 'Content-Type': 'application/json' });
    }
}

function showTypingIndicator() {
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
        typingIndicator.style.display = 'block';
        if (typingTimeout) clearTimeout(typingTimeout);
        typingTimeout = setTimeout(hideTypingIndicator, 5000);
    }
}

function hideTypingIndicator() {
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) typingIndicator.style.display = 'none';
}

function sendTypingStatus(isTyping) {
    if (!currentConversationId) return;
    fetch(`{{ route('admin.chat.typing') }}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ conversation_id: currentConversationId, is_typing: isTyping })
    }).catch(() => {});
}

function setupTypingListener() {
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        let typingTimer = null;
        messageInput.addEventListener('input', function() {
            sendTypingStatus(true);
            if (typingTimer) clearTimeout(typingTimer);
            typingTimer = setTimeout(() => sendTypingStatus(false), 1000);
        });
    }
}

function updateChatStatus() {
    if (!currentConversationId) return;
    const status = document.getElementById('chatStatusSelect').value;
    fetch(`{{ route('admin.chat.update-status', ':id') }}`.replace(':id', currentConversationId), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status })
    }).then(res => res.json()).then(() => loadConversations()).catch(err => showToast('Failed to update status', 'error'));
}

function markAsUnread() {
    if (!currentConversationId) return;
    fetch(`{{ route('admin.chat.mark-unread', ':id') }}`.replace(':id', currentConversationId), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`#conversationsList .list-group-item[data-id="${currentConversationId}"]`);
            if (item) {
                let badge = item.querySelector('.unread-badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge bg-danger unread-badge';
                    const wrapper = item.querySelector('.d-flex');
                    if (wrapper && !wrapper.querySelector('.unread-badge')) wrapper.appendChild(badge);
                }
                badge.textContent = data.unread_count;
            }
            loadConversations();
        }
    })
    .catch(err => showToast('Failed to mark as unread', 'error'));
}

function markAsRead() {
    if (!currentConversationId) return;
    fetch(`{{ route('admin.chat.mark-read', ':id') }}`.replace(':id', currentConversationId), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`#conversationsList .list-group-item[data-id="${currentConversationId}"]`);
            if (item) { const badge = item.querySelector('.unread-badge'); if (badge) badge.remove(); }
            loadConversations();
        }
    })
    .catch(err => showToast('Failed to mark as read', 'error'));
}

function closeChat() {
    if (!currentConversationId) return;
    if (confirm('Are you sure you want to close this chat?')) {
        fetch(`{{ route('admin.chat.close', ':id') }}`.replace(':id', currentConversationId), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(() => {
            currentConversationId = null;
            document.getElementById('chatCard').style.display = 'none';
            document.getElementById('noChatCard').style.display = 'block';
            document.getElementById('messagesList').innerHTML = '';
            document.getElementById('noMessages').style.display = 'block';
            document.querySelectorAll('#conversationsList .list-group-item').forEach(el => el.classList.remove('active'));
            stopTypingPolling();
            stopMessagePolling();
            loadConversations();
            showToast('Chat closed', 'success');
        })
        .catch(err => showToast('Failed to close chat', 'error'));
    }
}

function deleteChat() {
    if (!currentConversationId) return;
    if (confirm('Are you sure you want to delete this conversation? This action cannot be undone.')) {
        fetch(`{{ route('admin.chat.conversation.destroy', ':id') }}`.replace(':id', currentConversationId), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentConversationId = null;
                document.getElementById('chatCard').style.display = 'none';
                document.getElementById('noChatCard').style.display = 'block';
                stopTypingPolling();
                loadConversations();
                showToast('Conversation deleted', 'success');
            } else {
                showToast('Failed to delete conversation', 'error');
            }
        })
        .catch(err => showToast('Error deleting conversation', 'error'));
    }
}

function refreshConversations() {
    loadConversations();
    if (currentConversationId) loadMessages(currentConversationId);
}

function loadPredefinedMessages() {
    fetch(`{{ route('admin.chat.predefined.messages') }}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        predefinedMessages = data;
        updateQuickReplyCategories(data);
        updateQuickReplies(data);
    })
    .catch(err => showToast('Failed to load quick replies', 'error'));
}

function updateQuickReplyCategories(messages) {
    const tabsContainer = document.getElementById('quickReplyCategoryTabs');
    if (!tabsContainer) return;
    const categories = [...new Set(messages.map(m => m.category).filter(c => c))];
    if (categories.length === 0) {
        tabsContainer.innerHTML = '<button type="button" class="btn btn-outline-secondary active" data-category="all">All</button>';
    } else {
        let html = '<button type="button" class="btn btn-outline-secondary active" data-category="all">All</button>';
        categories.forEach(cat => { html += `<button type="button" class="btn btn-outline-secondary" data-category="${escapeHtml(cat)}">${escapeHtml(cat)}</button>`; });
        tabsContainer.innerHTML = html;
        tabsContainer.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', function() {
                tabsContainer.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentQuickReplyCategory = this.dataset.category;
                filterQuickReplies();
            });
        });
    }
}

function filterQuickReplies() {
    const filtered = currentQuickReplyCategory === 'all' ? predefinedMessages : predefinedMessages.filter(m => m.category === currentQuickReplyCategory);
    updateQuickReplies(filtered);
}

function updateQuickReplies(messages) {
    const container = document.getElementById('quickRepliesContainer');
    if (!container) return;
    if (messages.length === 0) { container.innerHTML = '<span class="text-muted small">No quick replies available</span>'; return; }
    let html = '';
    messages.forEach((msg, index) => {
        const shortcut = index < 9 ? `<span class="badge bg-secondary ms-1" style="font-size: 0.6rem;">${index + 1}</span>` : '';
        const safeTitle = escapeHtml(msg.title);
        const safeMessage = msg.message.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n');
        html += `<button type="button" class="btn btn-sm btn-outline-primary me-1 mb-1" onclick="sendQuickReply(${msg.id}, '${safeMessage}')" title="${escapeHtml(msg.message.substring(0, 80))}">
            ${safeTitle} ${shortcut}
        </button>`;
    });
    container.innerHTML = html;
}

function sendQuickReply(id, message) {
    const conversationId = document.getElementById('currentConversationId').value;
    if (!conversationId) { showToast('Please select a conversation first', 'warning'); return; }
    fetch(`{{ route('admin.chat.send') }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ conversation_id: conversationId, message })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) { loadMessages(conversationId); showToast('Message sent!', 'success'); }
        else { showToast(data.message || 'Failed to send message', 'error'); }
    })
    .catch(err => showToast('Failed to send message', 'error'));
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    const icon = type === 'success' ? 'check-circle-fill' : type === 'error' ? 'exclamation-circle-fill' : 'info-circle-fill';
    toast.innerHTML = `<div class="toast-content"><i class="bi bi-${icon}"></i><span>${escapeHtml(message)}</span></div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
}

function initPusher() {
    if (!pusher) return;
    const statusChannel = pusher.subscribe('chat-users');
    statusChannel.bind('user.status', function(data) {
        onlineUsers[data.user_id] = data.is_online;
        updateOnlineIndicators();
    });
    globalPusherChannel = pusher.subscribe('chat-global');
    globalPusherChannel.bind('message.sent', function(data) {
        handleNewMessage(data);
    });
}

function handleNewMessage(data) {
    // Convert chat_id to string for proper comparison (handles type mismatch between Pusher integer and localStorage string)
    const messageChatId = String(data.chat_id);
    if (messageChatId === String(currentConversationId)) {
        const noMessages = document.getElementById('noMessages');
        if (noMessages) noMessages.style.display = 'none';
        appendMessage(data);
    } else {
        showNotification('New Message', data.message || 'You have a new message');
        playNotificationSound();
    }
    loadConversations();
}

function subscribeToChat(chatId) {
    if (currentChatChannel) { currentChatChannel.unbind_all(); currentChatChannel.unsubscribe(); }
    if (pusher) {
        currentChatChannel = pusher.subscribe('chat.' + chatId);
        currentChatChannel.bind('message.sent', function(data) {
            if (String(data.chat_id) === String(currentConversationId)) appendMessage(data);
        });
    }
    startTypingPolling();
    startMessagePolling();
}

function startTypingPolling() {
    stopTypingPolling();
    typingPollInterval = setInterval(() => { checkUserTypingStatus(); }, 1000);
}

function stopTypingPolling() {
    if (typingPollInterval) { clearInterval(typingPollInterval); typingPollInterval = null; }
}

function checkUserTypingStatus() {
    if (!currentConversationId) return;
    fetch(`{{ route('admin.chat.check-typing') }}?conversation_id=${currentConversationId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.user_is_typing) showTypingIndicator();
        else hideTypingIndicator();
    })
    .catch(() => {});
}

function startMessagePolling() {
    stopMessagePolling();
    messagePollingInterval = setInterval(() => {
        if (currentConversationId) {
            pollNewMessages();
        }
    }, 3000);
}

function stopMessagePolling() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
        messagePollingInterval = null;
    }
}

function pollNewMessages() {
    if (!currentConversationId) return;
    fetch(`{{ route('admin.chat.conversation', ':id') }}`.replace(':id', currentConversationId), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        const messages = data.messages || [];
        const container = document.getElementById('messagesList');
        const existingCount = container.querySelectorAll('.message-bubble').length;
        
        if (messages.length > existingCount) {
            const newMessages = messages.slice(existingCount);
            newMessages.forEach(msg => {
                if (msg.sender_type === 'user') {
                    appendMessage(msg);
                }
            });
        }
    })
    .catch(() => {});
}

function appendMessage(msg) {
    const container = document.getElementById('messagesList');
    if (!container) return;
    const isAdmin = msg.sender_type === 'admin';
    const time = new Date(msg.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    const safeMessage = escapeHtml(msg.message);
    let attachmentHtml = '';
    if (msg.attachments) {
        const att = typeof msg.attachments === 'string' ? JSON.parse(msg.attachments) : msg.attachments;
        if (att && att.type) {
            if (att.type.startsWith('image/')) attachmentHtml = `<img src="${escapeHtml(att.path)}" class="attachment-preview" alt="attachment">`;
            else attachmentHtml = `<div class="attachment-file"><i class="bi bi-file-earmark"></i> ${escapeHtml(att.filename)}</div>`;
        }
    }
    const html = `<div class="mb-3 d-flex ${isAdmin ? 'justify-content-end' : 'justify-content-start'}">
        <div class="message-bubble ${isAdmin ? 'admin-message' : 'user-message'}">
            <div>${safeMessage}</div>
            ${attachmentHtml}
            <small class="${isAdmin ? 'text-light' : 'text-muted'} message-time">${time}</small>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function requestNotificationPermission() {
    if (!('Notification' in window)) { alert('This browser does not support desktop notifications'); return; }
    Notification.requestPermission().then(function(permission) {
        if (permission === 'granted') {
            showNotification('Notifications Enabled', 'You will now receive notifications for new messages');
            if (typeof $ !== 'undefined') $('#notificationModal').modal('hide');
        }
    });
}

function showNotification(title, body) {
    if (!('Notification' in window)) return;
    if (Notification.permission === 'granted') {
        const notification = new Notification(title, { body, icon: '/favicon.ico', badge: '/favicon.ico', tag: 'chat-notification', requireInteraction: false });
        notification.onclick = function() { window.focus(); this.close(); };
        setTimeout(() => notification.close(), 5000);
    }
}

function playNotificationSound() {
    if (soundEnabled) { try { notificationSound.play().catch(() => {}); } catch (e) {} }
}

function updateOnlineIndicators() {
    document.querySelectorAll('.conversation-item').forEach(item => {
        const userId = item.dataset.userId;
        const indicator = item.querySelector('.online-indicator');
        if (indicator && userId) {
            indicator.className = onlineUsers[userId] ? 'online-indicator online' : 'online-indicator offline';
        }
    });
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) { showToast('File size must be less than 10MB', 'error'); event.target.value = ''; return; }
    const preview = document.getElementById('filePreview');
    if (preview) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) { preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100px; border-radius: 4px;">`; };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `<span class="text-muted"><i class="bi bi-file-earmark"></i> ${escapeHtml(file.name)}</span>`;
        }
    }
}

function addFileUpload() {
    const form = document.getElementById('sendMessageForm');
    if (!form || document.getElementById('attachmentInput')) return;
    const fileContainer = document.createElement('div');
    fileContainer.className = 'mb-2';
    fileContainer.innerHTML = `<input type="file" id="attachmentInput" name="attachment" accept="image/*,.pdf,.doc,.docx" onchange="handleFileSelect(event)" style="display: none;"><div id="filePreview" class="mb-2"></div>`;
    const inputGroup = form.querySelector('.input-group');
    form.insertBefore(fileContainer, inputGroup);
    const attachBtn = document.createElement('button');
    attachBtn.type = 'button';
    attachBtn.className = 'btn btn-outline-secondary';
    attachBtn.innerHTML = '<i class="bi bi-paperclip"></i>';
    attachBtn.onclick = function() { document.getElementById('attachmentInput').click(); };
    inputGroup.insertBefore(attachBtn, inputGroup.querySelector('button[type="submit"]'));
}

window.addEventListener('beforeunload', function() {
    if (refreshInterval) clearInterval(refreshInterval);
    stopTypingPolling();
    stopMessagePolling();
    if (globalPusherChannel) { globalPusherChannel.unbind_all(); globalPusherChannel.unsubscribe(); }
    if (currentChatChannel) { currentChatChannel.unbind_all(); currentChatChannel.unsubscribe(); }
    if (pusher) pusher.disconnect();
});
</script>
@endpush
