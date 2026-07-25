@extends('admin.layouts.app')

@section('title', 'Theme Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-palette me-2"></i>Theme Settings</h4>
    <a href="{{ route('admin.themes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Themes
    </a>
</div>

<!-- Current Theme Info -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
            <i class="bi bi-palette text-primary" style="font-size: 1.5rem;"></i>
        </div>
        <div>
            <h6 class="mb-0">Currently Editing: {{ ucfirst($theme) }}</h6>
            <p class="text-muted mb-0">Customize the appearance of your {{ ucfirst($theme) }} theme</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.themes.settings.update') }}" method="POST" id="themeSettingsForm">
            @csrf
            
            <!-- Color Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Color Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="primary_color" class="form-label">Primary Color <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="primary_color_preview" value="{{ $settings['primary_color'] ?? '#4f46e5' }}">
                                <input type="text" name="primary_color" id="primary_color" class="form-control" value="{{ $settings['primary_color'] ?? '#4f46e5' }}">
                            </div>
                            <div class="form-text">Main brand color for buttons and links</div>
                        </div>
                        <div class="col-md-4">
                            <label for="secondary_color" class="form-label">Secondary Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="secondary_color_preview" value="{{ $settings['secondary_color'] ?? '#7c3aed' }}">
                                <input type="text" name="secondary_color" id="secondary_color" class="form-control" value="{{ $settings['secondary_color'] ?? '#7c3aed' }}">
                            </div>
                            <div class="form-text">For hover states</div>
                        </div>
                        <div class="col-md-4">
                            <label for="gold_color" class="form-label">Accent Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="gold_color_preview" value="{{ $settings['gold_color'] ?? '#d4af37' }}">
                                <input type="text" name="gold_color" id="gold_color" class="form-control" value="{{ $settings['gold_color'] ?? '#d4af37' }}">
                            </div>
                            <div class="form-text">For badges and special elements</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Typography Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-type me-2"></i>Typography Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="heading_font" class="form-label">Heading Font</label>
                            <select name="heading_font" id="heading_font" class="form-select">
                                <option value="Inter" {{ ($settings['heading_font'] ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                <option value="Poppins" {{ ($settings['heading_font'] ?? '') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                <option value="Roboto" {{ ($settings['heading_font'] ?? '') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                <option value="Open Sans" {{ ($settings['heading_font'] ?? '') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                <option value="Montserrat" {{ ($settings['heading_font'] ?? '') == 'Montserrat' ? 'selected' : '' }}>Montserrat</option>
                                <option value="Lato" {{ ($settings['heading_font'] ?? '') == 'Lato' ? 'selected' : '' }}>Lato</option>
                            </select>
                            <div class="form-text">Font family for headings (h1-h6)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="body_font" class="form-label">Body Font</label>
                            <select name="body_font" id="body_font" class="form-select">
                                <option value="Inter" {{ ($settings['body_font'] ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                <option value="Poppins" {{ ($settings['body_font'] ?? '') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                <option value="Roboto" {{ ($settings['body_font'] ?? '') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                <option value="Open Sans" {{ ($settings['body_font'] ?? '') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                <option value="Lato" {{ ($settings['body_font'] ?? '') == 'Lato' ? 'selected' : '' }}>Lato</option>
                            </select>
                            <div class="form-text">Font family for body text</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme Features -->
            @if(isset($config['features']) && is_array($config['features']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-toggle-on me-2"></i>Theme Features</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($config['features'] as $feature => $enabled)
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="feature_{{ $feature }}" name="features[{{ $feature }}]" {{ $enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="feature_{{ $feature }}">
                                    {{ ucwords(str_replace('_', ' ', $feature)) }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Menu Styling Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-list me-2"></i>Menu Styling</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="menu_hover_color" class="form-label">Menu Hover Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="menu_hover_color_preview" value="{{ $settings['menu_hover_color'] ?? '#ffffff' }}">
                                <input type="text" name="menu_hover_color" id="menu_hover_color" class="form-control" value="{{ $settings['menu_hover_color'] ?? '#ffffff' }}">
                            </div>
                            <div class="form-text">Background color on menu item hover</div>
                        </div>
                        <div class="col-md-4">
                            <label for="menu_text_hover_color" class="form-label">Menu Text Hover Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="menu_text_hover_color_preview" value="{{ $settings['menu_text_hover_color'] ?? '#4f46e5' }}">
                                <input type="text" name="menu_text_hover_color" id="menu_text_hover_color" class="form-control" value="{{ $settings['menu_text_hover_color'] ?? '#4f46e5' }}">
                            </div>
                            <div class="form-text">Text color on menu item hover</div>
                        </div>
                        <div class="col-md-4">
                            <label for="menu_active_color" class="form-label">Menu Active Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="menu_active_color_preview" value="{{ $settings['menu_active_color'] ?? '#ffffff' }}">
                                <input type="text" name="menu_active_color" id="menu_active_color" class="form-control" value="{{ $settings['menu_active_color'] ?? '#ffffff' }}">
                            </div>
                            <div class="form-text">Background color for active menu item</div>
                        </div>
                        <div class="col-md-4">
                            <label for="menu_active_text_color" class="form-label">Menu Active Text Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="menu_active_text_color_preview" value="{{ $settings['menu_active_text_color'] ?? '#4f46e5' }}">
                                <input type="text" name="menu_active_text_color" id="menu_active_text_color" class="form-control" value="{{ $settings['menu_active_text_color'] ?? '#4f46e5' }}">
                            </div>
                            <div class="form-text">Text color for active menu item</div>
                        </div>
                        <div class="col-md-4">
                            <label for="menu_font_size" class="form-label">Menu Font Size (px)</label>
                            <input type="number" name="menu_font_size" id="menu_font_size" class="form-control" value="{{ $settings['menu_font_size'] ?? '14' }}" min="10" max="24">
                            <div class="form-text">Font size for menu items</div>
                        </div>
                        <div class="col-md-4">
                            <label for="menu_font_weight" class="form-label">Menu Font Weight</label>
                            <select name="menu_font_weight" id="menu_font_weight" class="form-select">
                                <option value="300" {{ ($settings['menu_font_weight'] ?? '400') == '300' ? 'selected' : '' }}>Light (300)</option>
                                <option value="400" {{ ($settings['menu_font_weight'] ?? '400') == '400' ? 'selected' : '' }}>Regular (400)</option>
                                <option value="500" {{ ($settings['menu_font_weight'] ?? '400') == '500' ? 'selected' : '' }}>Medium (500)</option>
                                <option value="600" {{ ($settings['menu_font_weight'] ?? '400') == '600' ? 'selected' : '' }}>Semi-Bold (600)</option>
                                <option value="700" {{ ($settings['menu_font_weight'] ?? '400') == '700' ? 'selected' : '' }}>Bold (700)</option>
                            </select>
                            <div class="form-text">Font weight for menu items</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <!-- Actions Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <button type="submit" form="themeSettingsForm" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <button type="button" class="btn btn-outline-secondary w-100" onclick="previewTheme()">
                    <i class="bi bi-eye me-1"></i> Preview
                </button>
            </div>
        </div>

        <!-- Reset Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Reset</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Reset all theme settings to their default values.</p>
                <form action="{{ route('admin.themes.reset') }}" method="POST" onsubmit="return confirm('Are you sure you want to reset all settings?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Default
                    </button>
                </form>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Help</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Changes made here will affect the frontend appearance of your store.</p>
                <ul class="text-muted small ps-3 mb-0">
                    <li>Colors can be entered as hex codes or selected using the color picker</li>
                    <li>Fonts are loaded from Google Fonts</li>
                    <li>Click "Preview" to see changes without saving</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.themes.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="themeSettingsForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Changes
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
    // Sync color picker with text input
    document.querySelectorAll('.form-control-color').forEach(function(picker) {
        const input = picker.nextElementSibling;
        picker.addEventListener('input', function() {
            input.value = this.value;
        });
        input.addEventListener('input', function() {
            picker.value = this.value;
        });
    });

    // Preview function
    function previewTheme() {
        // Get form values and open preview
        const form = document.getElementById('themeSettingsForm');
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        // Properly serialize FormData entries
        for (const [key, value] of formData.entries()) {
            params.append(key, value);
        }
        
        // Open frontend in new tab with preview parameters
        window.open('{{ url("/") }}?' + params.toString() + '&preview=1', '_blank');
    }

    // Auto-scroll to first error field on page load
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
