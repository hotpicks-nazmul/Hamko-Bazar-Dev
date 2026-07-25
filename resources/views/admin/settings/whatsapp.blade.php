@extends('admin.layouts.app')

@section('title', 'WhatsApp Chat Settings')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">
            <i class="bi bi-whatsapp text-success me-2"></i> WhatsApp Chat Settings
        </h4>
        <p class="text-muted mb-0 small">Configure WhatsApp chat widget for your store</p>
    </div>
    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Settings
    </a>
</div>

<form action="{{ route('admin.settings.whatsapp.update') }}" method="POST">
    @csrf

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- General Settings -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-gear me-2"></i>General Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled" value="1" {{ ($settings['whatsapp_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="whatsapp_enabled">Enable WhatsApp Chat Widget</label>
                        <div class="form-text">Show WhatsApp chat button on your website</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">WhatsApp Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="whatsapp_phone_number" class="form-control @error('whatsapp_phone_number') is-invalid @enderror" value="{{ $settings['whatsapp_phone_number'] ?? '' }}" placeholder="8801712345678" required>
                            @error('whatsapp_phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter phone number with country code (without + sign). Example: 8801712345678</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Display Name</label>
                            <input type="text" name="whatsapp_display_name" class="form-control" value="{{ $settings['whatsapp_display_name'] ?? 'Customer Support' }}" placeholder="Customer Support">
                            <div class="form-text">Name shown in the chat popup header</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Welcome Message</label>
                        <textarea name="whatsapp_welcome_message" class="form-control" rows="3" placeholder="Hello! How can I help you today?">{{ $settings['whatsapp_welcome_message'] ?? 'Hello! How can I help you today?' }}</textarea>
                        <div class="form-text">This message will be pre-filled when user clicks to chat</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Predefined Quick Messages (One per line)</label>
                        <textarea name="whatsapp_predefined_messages" class="form-control" rows="4" placeholder="I have a question about a product&#10;I need help with my order&#10;I want to know about shipping">{{ $settings['whatsapp_predefined_messages'] ?? '' }}</textarea>
                        <div class="form-text">Users can quickly select these messages to send</div>
                    </div>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-display me-2"></i>Display Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Button Position</label>
                            <select name="whatsapp_position" class="form-select">
                                <option value="bottom-right" {{ ($settings['whatsapp_position'] ?? 'bottom-right') === 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                                <option value="bottom-left" {{ ($settings['whatsapp_position'] ?? 'bottom-right') === 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                                <option value="top-right" {{ ($settings['whatsapp_position'] ?? 'bottom-right') === 'top-right' ? 'selected' : '' }}>Top Right</option>
                                <option value="top-left" {{ ($settings['whatsapp_position'] ?? 'bottom-right') === 'top-left' ? 'selected' : '' }}>Top Left</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Button Color</label>
                            <div class="input-group">
                                <input type="color" id="whatsapp_button_color_picker" value="{{ $settings['whatsapp_button_color'] ?? '#25D366' }}" class="form-control form-control-color" style="width: 60px;">
                                <input type="text" name="whatsapp_button_color" id="whatsapp_button_color_text" class="form-control" value="{{ $settings['whatsapp_button_color'] ?? '#25D366' }}" placeholder="#25D366">
                            </div>
                            <div class="form-text">Default WhatsApp green: #25D366</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="whatsapp_show_on_desktop" id="whatsapp_show_on_desktop" value="1" {{ ($settings['whatsapp_show_on_desktop'] ?? '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="whatsapp_show_on_desktop">Show on Desktop</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="whatsapp_show_on_mobile" id="whatsapp_show_on_mobile" value="1" {{ ($settings['whatsapp_show_on_mobile'] ?? '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="whatsapp_show_on_mobile">Show on Mobile</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview & Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-eye me-2"></i>Preview</h5>
                </div>
                <div class="card-body text-center">
                    <div class="bg-light rounded p-4 mb-3" style="position: relative; min-height: 200px;">
                        <div class="whatsapp-preview-button" id="whatsappPreviewButton" style="position: absolute; bottom: 20px; right: 20px;">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" id="whatsappPreviewColor" style="width: 60px; height: 60px; background-color: {{ $settings['whatsapp_button_color'] ?? '#25D366' }};">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" style="width: 35px; height: 35px;">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small">This is how the WhatsApp button will appear on your website</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-lightbulb me-2"></i>Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Use business WhatsApp number for better customer support
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Keep welcome message short and friendly
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Add predefined messages for common queries
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Respond to customer messages promptly
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm border-warning">
                <div class="card-header bg-warning bg-opacity-10 py-3">
                    <h5 class="mb-0 fw-semibold text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Important</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-0">
                        Make sure your WhatsApp number is active and can receive messages. Users will be redirected to WhatsApp Web or WhatsApp mobile app to send messages.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Save Button -->
    <div class="floating-save-container">
        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary floating-reset-btn">
            <i class="bi bi-arrow-left me-1"></i> Back to Settings
        </a>
        <button type="submit" class="btn btn-primary floating-save-btn">
            <i class="bi bi-check-lg me-1"></i> Save Settings
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Sync color picker with text input
    document.getElementById('whatsapp_button_color_picker').addEventListener('input', function(e) {
        document.getElementById('whatsapp_button_color_text').value = e.target.value;
        updatePreviewColor(e.target.value);
    });
    
    document.getElementById('whatsapp_button_color_text').addEventListener('input', function(e) {
        document.getElementById('whatsapp_button_color_picker').value = e.target.value;
        updatePreviewColor(e.target.value);
    });
    
    // Update preview button color
    function updatePreviewColor(color) {
        const previewButton = document.getElementById('whatsappPreviewColor');
        if (previewButton) {
            previewButton.style.backgroundColor = color;
        }
    }
    
    // Update preview position when position dropdown changes
    document.querySelector('select[name="whatsapp_position"]').addEventListener('change', function(e) {
        const previewButton = document.getElementById('whatsappPreviewButton');
        const position = e.target.value;
        
        // Reset all position styles
        previewButton.style.top = '';
        previewButton.style.bottom = '';
        previewButton.style.left = '';
        previewButton.style.right = '';
        
        // Apply new position
        switch (position) {
            case 'bottom-right':
                previewButton.style.bottom = '20px';
                previewButton.style.right = '20px';
                break;
            case 'bottom-left':
                previewButton.style.bottom = '20px';
                previewButton.style.left = '20px';
                break;
            case 'top-right':
                previewButton.style.top = '20px';
                previewButton.style.right = '20px';
                break;
            case 'top-left':
                previewButton.style.top = '20px';
                previewButton.style.left = '20px';
                break;
        }
    });
</script>
@endpush
@endsection
