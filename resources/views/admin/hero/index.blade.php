@extends('admin.layouts.app')

@section('title', 'Hero Section Settings')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">
            <i class="bi bi-image-fill text-primary me-2"></i> Hero Section Settings
        </h4>
        <p class="text-muted mb-0 small">Customize the hero section displayed on your homepage</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-eye me-1"></i> Preview
        </a>
    </div>
</div>

<!-- Hero Type Selector -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-toggle-on text-primary me-2"></i>
                    Hero Section Type
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.hero.update-type') }}" method="POST" id="hero-type-form">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Select Hero Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="hero_type" id="hero_type_standard" value="standard" {{ ($heroSettings['hero_type']->value ?? 'standard') == 'standard' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="hero_type_standard">
                                    <i class="bi bi-card-image me-1"></i> Standard Hero
                                </label>
                                <input type="radio" class="btn-check" name="hero_type" id="hero_type_slider" value="slider" {{ ($heroSettings['hero_type']->value ?? 'standard') == 'slider' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="hero_type_slider">
                                    <i class="bi bi-images me-1"></i> Image Slider
                                </label>
                            </div>
                            <div class="form-text mt-2">
                                <strong>Standard Hero:</strong> Displays a static hero section with customizable content.<br>
                                <strong>Image Slider:</strong> Displays a rotating image slider using slides from the Sliders management.
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i> Preview
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Slider Info Card (shown when slider type is selected) -->
@if(($heroSettings['hero_type']->value ?? 'standard') === 'slider')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm border-start border-4 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-info-circle-fill text-info fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-semibold">Image Slider Mode Active</h6>
                        <p class="mb-0 text-muted small">
                            The homepage will display an image slider. You can manage slider images from the 
                            <a href="{{ route('admin.sliders.index') }}" class="text-primary fw-medium">Sliders Management</a> page.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('admin.sliders.index') }}" class="btn btn-info btn-sm">
                            <i class="bi bi-gear me-1"></i> Manage Sliders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Slider Preview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-images text-primary me-2"></i>
                    Active Sliders Preview
                </h5>
            </div>
            <div class="card-body">
                @if($sliders->count() > 0)
                <div id="sliderPreview" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        @foreach($sliders as $index => $slider)
                        <button type="button" data-bs-target="#sliderPreview" data-bs-slide-to="{{ $index }}" 
                            class="{{ $index === 0 ? 'active' : '' }}"></button>
                        @endforeach
                    </div>
                    <div class="carousel-inner rounded overflow-hidden">
                        @foreach($sliders as $index => $slider)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ Storage::url($slider->image) }}" class="d-block w-100" alt="{{ $slider->title }}" style="max-height: 400px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); border-radius: 10px;">
                                <h5>{{ $slider->title }}</h5>
                                <p>{{ $slider->subtitle }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#sliderPreview" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#sliderPreview" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-images fs-1"></i>
                    <p class="mt-2">No active sliders found.</p>
                    <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus me-1"></i> Add Slider
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Features Bar Settings (works for both hero types) -->
<form action="{{ route('admin.hero.update') }}" method="POST" enctype="multipart/form-data" id="features-form">
    @csrf
    @method('PUT')
    
    <!-- Features Bar -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Features Bar</h6>
            <small class="text-muted">These features appear on the homepage for both Standard and Image Slider hero types</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @for ($i = 1; $i <= 4; $i++)
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border bg-light">
                        <div class="card-body p-3">
                            <h6 class="card-title small text-muted mb-3">Feature {{ $i }}</h6>
                            <div class="mb-2">
                                <label for="hero_feature{{ $i }}_icon" class="form-label small">Icon</label>
                                <input type="text" id="hero_feature{{ $i }}_icon" name="hero_feature{{ $i }}_icon" 
                                    value="{{ $heroSettings["hero_feature{$i}_icon"]->value ?? '' }}"
                                    class="form-control form-control-sm @error("hero_feature{$i}_icon") is-invalid @enderror" placeholder="bi bi-truck">
                                @error("hero_feature{$i}_icon")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="hero_feature{{ $i }}_title" class="form-label small">Title</label>
                                <input type="text" id="hero_feature{{ $i }}_title" name="hero_feature{{ $i }}_title" 
                                    value="{{ $heroSettings["hero_feature{$i}_title"]->value ?? '' }}"
                                    class="form-control form-control-sm @error("hero_feature{$i}_title") is-invalid @enderror" placeholder="Free Delivery">
                                @error("hero_feature{$i}_title")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="hero_feature{{ $i }}_subtitle" class="form-label small">Subtitle</label>
                                <input type="text" id="hero_feature{{ $i }}_subtitle" name="hero_feature{{ $i }}_subtitle" 
                                    value="{{ $heroSettings["hero_feature{$i}_subtitle"]->value ?? '' }}"
                                    class="form-control form-control-sm @error("hero_feature{$i}_subtitle") is-invalid @enderror" placeholder="Orders over Tk500">
                                @error("hero_feature{$i}_subtitle")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>
    
    <!-- Floating Save Button -->
    <div class="floating-save-container">
        <a href="{{ route('admin.hero.index') }}" class="btn btn-secondary floating-reset-btn">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" form="features-form" class="btn btn-primary floating-save-btn">
            <i class="bi bi-check-lg me-1"></i> Save Features Bar
        </button>
    </div>
</form>

<!-- Standard Hero Settings (shown when standard type is selected) -->
@if(($heroSettings['hero_type']->value ?? 'standard') === 'standard')
<form action="{{ route('admin.hero.update') }}" method="POST" enctype="multipart/form-data" id="hero-form">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Background Image -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Background Image</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <label for="hero_background_image" class="form-label">Upload Background Image</label>
                            <input type="file" id="hero_background_image" name="hero_background_image" 
                                class="form-control @error('hero_background_image') is-invalid @enderror" accept="image/*"
                                onchange="previewBackgroundImage(this)">
                            @error('hero_background_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Recommended size: 1920x1080px. JPG or PNG format.</div>
                            <div id="backgroundImagePreview" class="mt-2"></div>
                        </div>
                        <div class="col-md-4">
                            @if($heroSettings->has('hero_background_image') && $heroSettings['hero_background_image']->value)
                            <div class="position-relative">
                                <img src="{{ $heroSettings['hero_background_image']->value }}" 
                                    alt="Current Background" 
                                    class="img-fluid rounded shadow-sm" style="max-height: 120px;">
                                <span class="badge bg-success position-absolute top-0 end-0 m-1">Current</span>
                            </div>
                            @else
                            <div class="text-center text-muted py-3 border rounded bg-light">
                                <i class="bi bi-image fs-1"></i>
                                <p class="mb-0 small">No image uploaded</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Badge Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-patch-check me-2"></i>Badge Section</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="hero_badge_icon" class="form-label">Icon Class</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-star-fill"></i></span>
                                <input type="text" id="hero_badge_icon" name="hero_badge_icon" 
                                    value="{{ $heroSettings['hero_badge_icon']->value ?? 'bi bi-patch-check-fill' }}"
                                    class="form-control @error('hero_badge_icon') is-invalid @enderror" placeholder="bi bi-patch-check-fill">
                            </div>
                            @error('hero_badge_icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Use Bootstrap Icons classes (e.g., bi bi-patch-check-fill)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="hero_badge_text" class="form-label">Badge Text</label>
                            <input type="text" id="hero_badge_text" name="hero_badge_text" 
                                value="{{ $heroSettings['hero_badge_text']->value ?? '' }}"
                                class="form-control @error('hero_badge_text') is-invalid @enderror" placeholder="Trusted by 10,000+ Customers">
                            @error('hero_badge_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Title Section -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-type-h1 me-2"></i>Title Section</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="hero_title_line1" class="form-label">Title Line 1</label>
                            <input type="text" id="hero_title_line1" name="hero_title_line1" 
                                value="{{ $heroSettings['hero_title_line1']->value ?? '' }}"
                                class="form-control @error('hero_title_line1') is-invalid @enderror" placeholder="Fresh">
                            @error('hero_title_line1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="hero_title_highlight1" class="form-label">Title Highlight 1 <span class="badge bg-warning text-dark ms-1">Gold</span></label>
                            <input type="text" id="hero_title_highlight1" name="hero_title_highlight1" 
                                value="{{ $heroSettings['hero_title_highlight1']->value ?? '' }}"
                                class="form-control @error('hero_title_highlight1') is-invalid @enderror" placeholder="Halal Food">
                            @error('hero_title_highlight1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="hero_title_line2" class="form-label">Title Line 2</label>
                            <input type="text" id="hero_title_line2" name="hero_title_line2" 
                                value="{{ $heroSettings['hero_title_line2']->value ?? '' }}"
                                class="form-control @error('hero_title_line2') is-invalid @enderror" placeholder="Delivered Fresh">
                            @error('hero_title_line2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="hero_title_line3" class="form-label">Title Line 3 <span class="badge bg-success ms-1">Green</span></label>
                            <input type="text" id="hero_title_line3" name="hero_title_line3" 
                                value="{{ $heroSettings['hero_title_line3']->value ?? '' }}"
                                class="form-control @error('hero_title_line3') is-invalid @enderror" placeholder="To Your Door">
                            @error('hero_title_line3')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-text-paragraph me-2"></i>Description</h6>
                </div>
                <div class="card-body">
                    <label for="hero_description" class="form-label">Hero Description</label>
                    <textarea id="hero_description" name="hero_description" rows="3"
                        class="form-control @error('hero_description') is-invalid @enderror" placeholder="Premium quality halal meat, poultry, seafood & groceries...">{{ $heroSettings['hero_description']->value ?? '' }}</textarea>
                    @error('hero_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Describe your products and services in an engaging way.</div>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-hand-index me-2"></i>Call-to-Action Buttons</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Primary Button -->
                        <div class="col-md-6">
                            <div class="card h-100 border">
                                <div class="card-header bg-warning bg-opacity-10 py-2">
                                    <h6 class="mb-0">
                                        <i class="bi bi-star-fill text-warning me-2"></i>
                                        Primary Button
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="hero_cta1_text" class="form-label small">Button Text</label>
                                        <input type="text" id="hero_cta1_text" name="hero_cta1_text" 
                                            value="{{ $heroSettings['hero_cta1_text']->value ?? '' }}"
                                            class="form-control form-control-sm @error('hero_cta1_text') is-invalid @enderror" placeholder="Shop Now">
                                        @error('hero_cta1_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="hero_cta1_link" class="form-label small">Route Name</label>
                                        <input type="text" id="hero_cta1_link" name="hero_cta1_link" 
                                            value="{{ $heroSettings['hero_cta1_link']->value ?? '' }}"
                                            class="form-control form-control-sm @error('hero_cta1_link') is-invalid @enderror" placeholder="products.index">
                                        @error('hero_cta1_link')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="hero_cta1_icon" class="form-label small">Icon Class</label>
                                        <input type="text" id="hero_cta1_icon" name="hero_cta1_icon" 
                                            value="{{ $heroSettings['hero_cta1_icon']->value ?? '' }}"
                                            class="form-control form-control-sm @error('hero_cta1_icon') is-invalid @enderror" placeholder="bi bi-cart3">
                                        @error('hero_cta1_icon')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Secondary Button -->
                        <div class="col-md-6">
                            <div class="card h-100 border">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0">
                                        <i class="bi bi-fire text-danger me-2"></i>
                                        Secondary Button
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="hero_cta2_text" class="form-label small">Button Text</label>
                                        <input type="text" id="hero_cta2_text" name="hero_cta2_text" 
                                            value="{{ $heroSettings['hero_cta2_text']->value ?? '' }}"
                                            class="form-control form-control-sm @error('hero_cta2_text') is-invalid @enderror" placeholder="Hot Deals">
                                        @error('hero_cta2_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="hero_cta2_link" class="form-label small">Route Name</label>
                                        <input type="text" id="hero_cta2_link" name="hero_cta2_link" 
                                            value="{{ $heroSettings['hero_cta2_link']->value ?? '' }}"
                                            class="form-control form-control-sm @error('hero_cta2_link') is-invalid @enderror" placeholder="products.index">
                                        @error('hero_cta2_link')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="hero_cta2_params" class="form-label small">Route Params (JSON)</label>
                                        <input type="text" id="hero_cta2_params" name="hero_cta2_params" 
                                            value="{{ $heroSettings['hero_cta2_params']->value ?? '' }}"
                                            class="form-control form-control-sm @error('hero_cta2_params') is-invalid @enderror" placeholder='{"sort":"discount"}'>
                                        @error('hero_cta2_params')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label for="hero_cta2_icon" class="form-label small">Icon</label>
                                            <input type="text" id="hero_cta2_icon" name="hero_cta2_icon" 
                                                value="{{ $heroSettings['hero_cta2_icon']->value ?? '' }}"
                                                class="form-control form-control-sm @error('hero_cta2_icon') is-invalid @enderror" placeholder="bi bi-fire">
                                            @error('hero_cta2_icon')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-6">
                                            <label for="hero_cta2_badge" class="form-label small">Badge</label>
                                            <input type="text" id="hero_cta2_badge" name="hero_cta2_badge" 
                                                value="{{ $heroSettings['hero_cta2_badge']->value ?? '' }}"
                                                class="form-control form-control-sm @error('hero_cta2_badge') is-invalid @enderror" placeholder="UP TO 30% OFF">
                                            @error('hero_cta2_badge')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Bar -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Features Bar</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @for ($i = 1; $i <= 4; $i++)
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border bg-light">
                                <div class="card-body p-3">
                                    <h6 class="card-title small text-muted mb-3">Feature {{ $i }}</h6>
                                    <div class="mb-2">
                                        <label for="hero_feature{{ $i }}_icon" class="form-label small">Icon</label>
                                        <input type="text" id="hero_feature{{ $i }}_icon" name="hero_feature{{ $i }}_icon" 
                                            value="{{ $heroSettings["hero_feature{$i}_icon"]->value ?? '' }}"
                                            class="form-control form-control-sm @error("hero_feature{$i}_icon") is-invalid @enderror" placeholder="bi bi-truck">
                                        @error("hero_feature{$i}_icon")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-2">
                                        <label for="hero_feature{{ $i }}_title" class="form-label small">Title</label>
                                        <input type="text" id="hero_feature{{ $i }}_title" name="hero_feature{{ $i }}_title" 
                                            value="{{ $heroSettings["hero_feature{$i}_title"]->value ?? '' }}"
                                            class="form-control form-control-sm @error("hero_feature{$i}_title") is-invalid @enderror" placeholder="Free Delivery">
                                        @error("hero_feature{$i}_title")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="hero_feature{{ $i }}_subtitle" class="form-label small">Subtitle</label>
                                        <input type="text" id="hero_feature{{ $i }}_subtitle" name="hero_feature{{ $i }}_subtitle" 
                                            value="{{ $heroSettings["hero_feature{$i}_subtitle"]->value ?? '' }}"
                                            class="form-control form-control-sm @error("hero_feature{$i}_subtitle") is-invalid @enderror" placeholder="Orders over Tk500">
                                        @error("hero_feature{$i}_subtitle")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Main Image -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-card-image me-2"></i>Main Product Image</h6>
                </div>
                <div class="card-body">
                    <label for="hero_main_image" class="form-label">Upload Image</label>
                    <input type="file" id="hero_main_image" name="hero_main_image" 
                        class="form-control @error('hero_main_image') is-invalid @enderror" accept="image/*"
                        onchange="previewMainImage(this)">
                    @error('hero_main_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text mb-3">Recommended: 600x450px</div>
                    <div id="mainImagePreview" class="mb-3"></div>
                    
                    @if($heroSettings->has('hero_main_image') && $heroSettings['hero_main_image']->value)
                    <div class="position-relative mb-3">
                        <img src="{{ $heroSettings['hero_main_image']->value }}" 
                            alt="Current Image" 
                            class="img-fluid rounded shadow-sm w-100">
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">Current</span>
                    </div>
                    @endif
                    
                    <label for="hero_main_image_alt" class="form-label">Alt Text</label>
                    <input type="text" id="hero_main_image_alt" name="hero_main_image_alt" 
                        value="{{ $heroSettings['hero_main_image_alt']->value ?? '' }}"
                        class="form-control @error('hero_main_image_alt') is-invalid @enderror" placeholder="Fresh Halal Meat">
                    @error('hero_main_image_alt')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Floating Cards -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-layers me-2"></i>Floating Cards</h6>
                </div>
                <div class="card-body">
                    <!-- Today's Special -->
                    <div class="mb-4">
                        <h6 class="text-muted small text-uppercase mb-2">
                            <i class="bi bi-star me-1"></i> Today's Special
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="hero_special_label" class="form-label small">Label</label>
                                <input type="text" id="hero_special_label" name="hero_special_label" 
                                    value="{{ $heroSettings['hero_special_label']->value ?? '' }}"
                                    class="form-control form-control-sm @error('hero_special_label') is-invalid @enderror" placeholder="Today's Special">
                                @error('hero_special_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label for="hero_special_title" class="form-label small">Title</label>
                                <input type="text" id="hero_special_title" name="hero_special_title" 
                                    value="{{ $heroSettings['hero_special_title']->value ?? '' }}"
                                    class="form-control form-control-sm @error('hero_special_title') is-invalid @enderror" placeholder="Premium Beef">
                                @error('hero_special_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label for="hero_special_button" class="form-label small">Button</label>
                                <input type="text" id="hero_special_button" name="hero_special_button" 
                                    value="{{ $heroSettings['hero_special_button']->value ?? '' }}"
                                    class="form-control form-control-sm @error('hero_special_button') is-invalid @enderror" placeholder="Order Now">
                                @error('hero_special_button')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label for="hero_special_link" class="form-label small">Route</label>
                                <input type="text" id="hero_special_link" name="hero_special_link" 
                                    value="{{ $heroSettings['hero_special_link']->value ?? '' }}"
                                    class="form-control form-control-sm @error('hero_special_link') is-invalid @enderror" placeholder="products.index">
                                @error('hero_special_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Time -->
                    <div class="mb-4">
                        <h6 class="text-muted small text-uppercase mb-2">
                            <i class="bi bi-clock me-1"></i> Delivery Time
                        </h6>
                        <div class="row g-2">
                            <div class="col-4">
                                <label for="hero_delivery_icon" class="form-label small">Icon</label>
                                <input type="text" id="hero_delivery_icon" name="hero_delivery_icon" 
                                    value="{{ $heroSettings['hero_delivery_icon']->value ?? '' }}"
                                    class="form-control form-control-sm @error('hero_delivery_icon') is-invalid @enderror" placeholder="bi bi-clock">
                                @error('hero_delivery_icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label for="hero_delivery_label" class="form-label small">Label</label>
                                <input type="text" id="hero_delivery_label" name="hero_delivery_label" 
                                    value="{{ $heroSettings['hero_delivery_label']->value ?? '' }}"
                                    class="form-control form-control-sm @error('hero_delivery_label') is-invalid @enderror" placeholder="Delivery">
                                @error('hero_delivery_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label for="hero_delivery_value" class="form-label small">Value</label>
                                <input type="text" id="hero_delivery_value" name="hero_delivery_value" 
                                    value="{{ $heroSettings['hero_delivery_value']->value ?? '' }}"
                                    class="form-control form-control-sm @error('hero_delivery_value') is-invalid @enderror" placeholder="30-60 Min">
                                @error('hero_delivery_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Happy Customers -->
                    <div>
                        <h6 class="text-muted small text-uppercase mb-2">
                            <i class="bi bi-emoji-smile me-1"></i> Happy Customers
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="hero_customers_label" class="form-label small">Label</label>
                                <input type="text" id="hero_customers_label" name="hero_customers_label" 
                                    value="{{ $heroSettings['hero_customers_label']->value ?? '' }}"
                                    class="form-control form-control-sm @error('hero_customers_label') is-invalid @enderror" placeholder="Happy">
                                @error('hero_customers_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label for="hero_customers_value" class="form-label small">Value</label>
                                <input type="text" id="hero_customers_value" name="hero_customers_value" 
                                    value="{{ $heroSettings['hero_customers_value']->value ?? '' }}"
                                    class="form-control form-control-sm @error('hero_customers_value') is-invalid @enderror" placeholder="Customers">
                                @error('hero_customers_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endif

<!-- Floating Save Button -->
@if(($heroSettings['hero_type']->value ?? 'standard') === 'standard')
<div class="floating-save-container">
    <a href="{{ route('admin.hero.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="hero-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save All Settings
    </button>
</div>
@endif
@endsection

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
    .card {
        transition: all 0.2s ease;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .form-label {
        color: #495057;
    }
    .badge {
        font-weight: 500;
    }
    .carousel-item img {
        max-height: 400px;
        object-fit: cover;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hero type form handling
        var form = document.getElementById('hero-type-form');
        
        if (!form) {
            console.error('Hero type form not found');
            return;
        }
        
        // Handle radio button changes - auto submit
        var radios = form.querySelectorAll('input[name="hero_type"]');
        radios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                form.submit();
            });
        });
        
        // Auto-scroll to first error field
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

    // Preview background image
    function previewBackgroundImage(input) {
        var preview = document.getElementById('backgroundImagePreview');
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail mt-2" style="max-width: 200px; max-height: 120px;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Preview main image
    function previewMainImage(input) {
        var preview = document.getElementById('mainImagePreview');
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
