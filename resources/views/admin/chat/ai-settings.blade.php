@extends('admin.layouts.app')

@section('title', 'AI Chatbot Settings')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-robot me-2"></i>AI Chatbot Settings</h4>
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

<!-- Validation Errors -->
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.chat.ai-settings') }}" method="POST" id="ai-settings-form">
    @csrf
    
    <div class="row">
        <!-- Settings Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Chatbot Configuration</h5>
                </div>
                <div class="card-body">
                    <!-- Enable AI Chatbot -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="ai_enabled" class="form-check-input" id="aiEnabled" 
                                    {{ ($aiSettings['enabled'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="aiEnabled">
                                    <i class="bi bi-robot text-primary me-1"></i> Enable AI Chatbot
                                </label>
                                <div class="form-text">When enabled, AI will auto-respond to customer messages using OpenAI API</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Welcome Message -->
                    <div class="mb-3">
                        <label for="welcomeMessage" class="form-label fw-bold">Welcome Message</label>
                        <input type="text" id="welcomeMessage" name="ai_welcome_message" class="form-control @error('ai_welcome_message') is-invalid @enderror" 
                            value="{{ old('ai_welcome_message', $aiSettings['welcome_message'] ?? 'Hello! How can I help you today?') }}">
                        @error('ai_welcome_message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Message shown to new visitors when they start a chat</div>
                    </div>

                    <!-- OpenAI API Key -->
                    <div class="mb-3">
                        <label for="apiKeyInput" class="form-label fw-bold">
                            <i class="bi bi-key me-1"></i> OpenAI API Key
                        </label>
                        <div class="input-group">
                            <input type="password" id="apiKeyInput" name="openai_api_key" class="form-control @error('openai_api_key') is-invalid @enderror" 
                                value="{{ old('openai_api_key', $aiSettings['openai_key'] ?? '') }}" placeholder="sk-...">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleApiKey()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('openai_api_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Required for AI chatbot functionality. 
                            <a href="https://platform.openai.com/api-keys" target="_blank">Get your API key here</a>
                        </div>
                    </div>

                    <!-- API Key Status -->
                    <div class="mb-3">
                        @if(!empty($aiSettings['openai_key']))
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div>API Key is configured</div>
                        </div>
                        @else
                        <div class="alert alert-warning d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div>API Key is not configured. AI Chatbot will not work without it.</div>
                        </div>
                        @endif
                    </div>

                    <hr class="my-4">

                    <!-- AI Behavior Settings -->
                    <h6 class="fw-bold mb-3"><i class="bi bi-sliders me-2"></i>AI Behavior Settings</h6>
                    
                    <div class="mb-3">
                        <label for="aiModel" class="form-label">AI Model</label>
                        <select id="aiModel" class="form-select @error('ai_model') is-invalid @enderror" name="ai_model">
                            <option value="gpt-3.5-turbo" {{ old('ai_model', $aiSettings['model'] ?? 'gpt-3.5-turbo') == 'gpt-3.5-turbo' ? 'selected' : '' }}>gpt-3.5-turbo (Fast & Cost-effective)</option>
                            <option value="gpt-4" {{ old('ai_model', $aiSettings['model'] ?? 'gpt-3.5-turbo') == 'gpt-4' ? 'selected' : '' }}>gpt-4 (More Capable)</option>
                            <option value="gpt-4-turbo" {{ old('ai_model', $aiSettings['model'] ?? 'gpt-3.5-turbo') == 'gpt-4-turbo' ? 'selected' : '' }}>gpt-4-turbo (Latest & Fast)</option>
                        </select>
                        @error('ai_model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Select the OpenAI model to use for responses</div>
                    </div>

                    <div class="mb-3">
                        <label for="maxTokens" class="form-label">Max Response Length</label>
                        <input type="number" id="maxTokens" class="form-control @error('ai_max_tokens') is-invalid @enderror" name="ai_max_tokens" value="{{ old('ai_max_tokens', $aiSettings['max_tokens'] ?? '500') }}" min="50" max="2000">
                        @error('ai_max_tokens')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maximum number of tokens in AI response (1 token ≈ 4 characters)</div>
                    </div>

                    <div class="mb-3">
                        <label for="temperatureRange" class="form-label">Temperature (Creativity)</label>
                        <input type="range" class="form-range" name="ai_temperature" min="0" max="1" step="0.1" value="{{ old('ai_temperature', $aiSettings['temperature'] ?? '0.7') }}" id="temperatureRange">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Precise (0)</span>
                            <span class="text-muted small">Creative (1)</span>
                        </div>
                        @error('ai_temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- System Prompt -->
                    <div class="mb-4">
                        <label for="systemPrompt" class="form-label fw-bold">System Prompt</label>
                        <textarea id="systemPrompt" class="form-control @error('ai_system_prompt') is-invalid @enderror" name="ai_system_prompt" rows="5" placeholder="You are a helpful customer support assistant for an e-commerce store...">{{ old('ai_system_prompt', $aiSettings['system_prompt'] ?? 'You are a helpful and friendly customer support assistant for an e-commerce store. You help customers with their inquiries about products, orders, shipping, and general questions. Keep your responses concise and helpful.') }}</textarea>
                        @error('ai_system_prompt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Define how the AI should behave and respond to customers</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="col-lg-4">
            <!-- Widget Preview -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Widget Preview</h6>
                </div>
                <div class="card-body p-0">
                    <div class="bg-light p-3" style="min-height: 200px;">
                        <div class="bg-white rounded p-3 shadow-sm">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary rounded-circle p-2 me-2">
                                    <i class="bi bi-robot text-white"></i>
                                </div>
                                <div>
                                    <strong>AI Assistant</strong>
                                    <div class="small text-muted">Online</div>
                                </div>
                            </div>
                            <div class="bg-primary text-white rounded p-2 mb-2">
                                <small id="previewWelcomeMessage">{{ $aiSettings['welcome_message'] ?? 'Hello! How can I help you today?' }}</small>
                            </div>
                            <div class="text-muted small">
                                <i>Customer message preview...</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Help & Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">How AI Chatbot Works</h6>
                        <p class="small text-muted mb-0">
                            The AI chatbot automatically responds to customer messages using OpenAI's language models. 
                            It can answer product questions, order status, shipping info, and more.
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Pricing</h6>
                        <p class="small text-muted mb-0">
                            OpenAI pricing is pay-as-you-go. GPT-3.5-turbo is very affordable (~$0.002 per 1K tokens).
                        </p>
                    </div>
                    <div>
                        <h6 class="fw-bold">Tips</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Keep system prompt concise</li>
                            <li>Test responses regularly</li>
                            <li>Monitor usage in OpenAI dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Save Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.chat.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="ai-settings-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Settings
    </button>
</div>
@endsection

@push('scripts')
<script>
function toggleApiKey() {
    const input = document.getElementById('apiKeyInput');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Update temperature display
document.getElementById('temperatureRange').addEventListener('input', function(e) {
    // Could add live preview of temperature effect here
});

// Update welcome message preview in real-time
document.getElementById('welcomeMessage').addEventListener('input', function(e) {
    const preview = document.getElementById('previewWelcomeMessage');
    preview.textContent = e.target.value || 'Hello! How can I help you today?';
});

// Auto-scroll to first error field
document.addEventListener('DOMContentLoaded', function() {
    @if($errors->any())
        var firstErrorField = document.querySelector('.is-invalid');
        if (firstErrorField) {
            setTimeout(function() {
                firstErrorField.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                firstErrorField.focus();
            }, 100);
        }
    @endif
});
</script>
@endpush
