@extends('admin.layouts.app')

@section('title', 'Create Product')

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<div id="imgHoverPreview" class="img-hover-preview"></div>
<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
@csrf
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create New Product</h4>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <input type="hidden" name="redirect_route" value="{{ $redirectRoute ?? 'admin.products.index' }}">
                
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter product name"
                           data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="short_description" class="form-label">Short Description</label>
                    <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2" maxlength="500" placeholder="Brief product description (max 500 characters)"
                              data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('short_description') }}">{{ old('short_description') }}</textarea>
                    @error('short_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Full Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required placeholder="Detailed product description"
                              data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('description') }}">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="price" class="form-label">Regular Price (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required placeholder="0.00"
                                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('price') }}">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sale_price" class="form-label">Sale Price (৳)</label>
                            <input type="number" step="0.01" min="0" class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" placeholder="Leave empty if no sale"
                                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('sale_price') }}">
                            @error('sale_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">If set, this will be the selling price</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="sku" class="form-label">SKU <span class="text-success">(Auto-generated)</span></label>
                            <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $nextSku ?? 'SKU-1000') }}" readonly>
                            <div class="form-text">Auto-generated, cannot be edited</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="product_code" class="form-label">Product Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('product_code') is-invalid @enderror" id="product_code" name="product_code" value="{{ old('product_code') }}" required placeholder="e.g., PRD-001"
                                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('product_code') }}">
                            @error('product_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Manual input (manufacturer/supplier code)</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="barcode" class="form-label">Barcode</label>
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode') }}" placeholder="Product barcode"
                                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('barcode') }}">
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="brand" class="form-label">Brand</label>
                            <select class="form-select @error('brand') is-invalid @enderror" id="brand" name="brand"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('brand') }}">
                                <option value="">Select Brand</option>
                                @foreach($brands as $id => $name)
                                    <option value="{{ $id }}" {{ old('brand') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Choose from existing brands or <a href="{{ route('admin.brands.create') }}" target="_blank">create new</a></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                                @if(auth()->user()->hasPermission('products.view-cost'))
                                <div class="mb-3">
                                    <label for="purchase_price" class="form-label">Purchase/Cost Price (৳)</label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('purchase_price') is-invalid @enderror" id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}" placeholder="Cost price"
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('purchase_price') }}">
                                    @error('purchase_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', 0) }}" required
                                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('stock') }}">
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
<div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="low_stock_threshold" class="form-label">Low Stock Alert</label>
                                        <input type="number" min="0" class="form-control @error('low_stock_threshold') is-invalid @enderror" id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', 10) }}" placeholder="10"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('low_stock_threshold') }}">
                                        @error('low_stock_threshold')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Alert when stock falls below this</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="weight" class="form-label">Weight (kg)</label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" value="{{ old('weight') }}" placeholder="0.00"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('weight') }}">
                                        @error('weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Product weight in kilograms</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="dimensions" class="form-label">Dimensions</label>
                                        <input type="text" class="form-control @error('dimensions') is-invalid @enderror" id="dimensions" name="dimensions" value="{{ old('dimensions') }}" placeholder="e.g., 30 x 20 x 15 cm"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('dimensions') }}">
                                        @error('dimensions')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Length x Width x Height</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="mb-2"><i class="bi bi-list-check me-2"></i>Custom Specifications</h6>
                                    <div id="specsContainer">
                                        <div class="row mb-2 spec-row">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="specs[0][key]" placeholder="Specification Name (e.g., Material)">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="specs[0][value]" placeholder="Value (e.g., Stainless Steel)">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeSpecRow(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSpecRow()">
                                        <i class="bi bi-plus-lg me-1"></i> Add Specification
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
        
        <!-- Product Attributes Section -->
        <div class="card border-0 shadow-sm mb-3" style="overflow: visible !important;">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Product Attributes</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Select attributes to add price, quantity and SKU for each value</p>
                
                 <div class="mb-3">
                      <label class="form-label">Select Attributes</label>
                      <div class="dropdown">
                          <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" type="button" id="productAttributesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                              <span id="productAttributesLabel">Select attributes...</span>
                              <i class="bi bi-chevron-down"></i>
                          </button>
                          <div class="dropdown-menu w-100 p-0" aria-labelledby="productAttributesDropdown" style="max-height: 300px; overflow-y: auto;">
                             <div class="px-2 py-2 border-bottom">
                                 <input type="text" class="form-control form-control-sm" id="productAttributesSearch" placeholder="Search...">
                             </div>
                             <div class="px-2 py-1" id="productAttributesList">
                                 @foreach($attributes as $attribute)
                                     @if($attribute->activeValues->count() > 0)
                                     <div class="form-check">
                                         <input class="form-check-input attribute-checkbox" type="checkbox"
                                             value="{{ $attribute->id }}"
                                             id="attr_{{ $attribute->id }}"
                                             data-name="{{ $attribute->name }}"
                                             data-values='@json($attribute->activeValues->map(function($v) { return ["id" => $v->id, "value" => $v->value]; })->toArray())'>
                                         <label class="form-check-label w-100" for="attr_{{ $attribute->id }}">
                                             {{ $attribute->name }}
                                             <small class="text-muted">({{ $attribute->activeValues->count() }} values)</small>
                                         </label>
                                     </div>
                                     @endif
                                 @endforeach
                             </div>
                         </div>
                     </div>
                     <div class="selected-tags mt-2" id="productAttributesTags"></div>
                 </div>

                <div id="selectedAttributesContainer"></div>

                <!-- Debug info - uncomment to enable debugging -->
                <!-- <div id="debugInfo" style="margin-top: 10px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; display: none;">
                    <strong>Debug Info:</strong><br>
                    <div id="debugContent"></div>
                </div> -->
            </div>
        </div>
        
        <!-- Product Colors Section -->
        <div class="card border-0 shadow-sm mb-3" style="overflow: visible !important;">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Product Colors</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Select colors to add price, quantity, image and SKU for each</p>
                
                <div class="mb-3">
                    <label class="form-label">Select Colors</label>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" type="button" id="productColorsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="productColorsLabel">Select colors...</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu w-100 p-0" aria-labelledby="productColorsDropdown" style="max-height: 300px; overflow-y: auto;">
                            <div class="px-2 py-2 border-bottom">
                                <input type="text" class="form-control form-control-sm" id="productColorsSearch" placeholder="Search...">
                            </div>
                            <div class="px-2 py-1" id="productColorsList">
                                @foreach($colors as $color)
                                    @if($color->activeValues->count() > 0)
                                    <div class="form-check">
                                        <input class="form-check-input color-checkbox" type="checkbox"
                                            value="{{ $color->id }}"
                                            id="color_{{ $color->id }}"
                                            data-name="{{ $color->name }}"
                                            data-hex="{{ $color->hex_code }}"
                                            data-values='@json($color->activeValues->map(function($v) { return ["id" => $v->id, "value" => $v->value, "hex_code" => $v->hex_code]; })->toArray())'>
                                        <label class="form-check-label w-100" for="color_{{ $color->id }}">
                                            {{ $color->name }}
                                            <small class="text-muted">({{ $color->activeValues->count() }} values)</small>
                                            <span style="background: {{ $color->hex_code }}; width: 16px; height: 16px; display: inline-block; border-radius: 50%; border: 1px solid #ddd; vertical-align: middle; margin-left: 8px;"></span>
                                        </label>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="selected-tags mt-2" id="productColorsTags"></div>
                </div>
                
                <div id="selectedColorsContainer"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Category Selection -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-folder me-2"></i>Category</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="category_id" class="form-label">Select Category <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required
                           data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('category_id') }}">
                        <option value="">Select Category</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ old('category_id', $preselectedCategory) == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Choose the most relevant category</div>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.categories.create') }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i> New Category
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Images -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-image me-2"></i>Product Images</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="image" class="form-label">Featured Image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" onchange="previewFeaturedImage(this)"
                           data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('image') }}">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Main product image. Max 5MB.</div>
                    <div id="featuredImagePreview" class="mt-2"></div>
                </div>
                
                <div class="mb-3">
                    <label for="images" class="form-label">Gallery Images</label>
                    <input type="file" class="form-control @error('images') is-invalid @enderror" id="images" name="images[]" multiple accept="image/*" onchange="previewGalleryImages(this)"
                           data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('images') }}">
                    @error('images')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Additional product images. Multiple allowed.</div>
                    <div id="galleryPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>
        
        <!-- Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Status</h6>
            </div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        <i class="bi bi-check-circle text-success me-1"></i> Active
                    </label>
                    <div class="form-text">Product will be visible on the store</div>
                </div>
                
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_featured">
                        <i class="bi bi-star text-warning me-1"></i> Featured Product
                    </label>
                    <div class="form-text">Featured products appear prominently</div>
                </div>
</div>
        </div>
         
         <!-- Variant Images (Combination Images) -->
         <div class="card border-0 shadow-sm mb-3">
             <div class="card-header bg-white">
                 <h6 class="mb-0"><i class="bi bi-image me-2"></i>Variant Images</h6>
             </div>
             <div class="card-body">
                 <p class="text-muted small mb-3">Upload images for specific attribute combinations. Each combination shows the exact product with those attributes selected.</p>
                 <div id="variantImagesContainer">
                     <p class="text-muted small">Select colors and/or attributes above to enable variant images.</p>
                 </div>
             </div>
         </div>
         
         <!-- Actions -->
         <div class="card border-0 shadow-sm">
             <div class="card-header bg-white">
                 <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Actions</h6>
             </div>
             <div class="card-body">
                 <div class="d-grid gap-2">
                     <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                         <i class="bi bi-x-lg me-1"></i> Cancel
                     </a>
                 </div>
             </div>
         </div>

        </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="product-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Product
    </button>
</div>
</form>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4" id="alertModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Access Restricted:</strong> You don't have permission to access products.
    Please contact an administrator if you need access.
</div>
@endif
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }

    .btn-close-red {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: 0.6;
        cursor: pointer;
    }
    .btn-close-red:hover {
        opacity: 1;
    }

    .popover-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 8px 12px;
    }

    .color-preview {
        width: 18px;
        height: 18px;
        border-radius: 3px;
        display: inline-block;
        vertical-align: middle;
        border: 1px solid #ddd;
    }

    .img-hover-preview {
        position: fixed;
        z-index: 9999;
        max-width: 300px;
        max-height: 300px;
        display: none;
        border: 2px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        border-radius: 4px;
        background: transparent;
    }

    .img-hover-preview.visible {
        display: block;
    }
    .selected-tags .badge {
        font-weight: 500;
        padding: 4px 8px;
    }
    .dropdown-menu {
        transform: none !important;
        clip: auto !important;
        clip-path: none !important;
    }
    .card-dropdown-wrapper {
        overflow: visible !important;
        position: relative !important;
        z-index: 1000 !important;
    }
    .card-dropdown-wrapper .card-body {
        overflow: visible !important;
        position: relative !important;
        z-index: 1000 !important;
    }
    .card-dropdown-wrapper .dropdown {
        position: relative !important;
        z-index: 1001 !important;
    }
    .card-dropdown-wrapper .dropdown-menu {
        position: absolute !important;
        z-index: 1002 !important;
        top: 100% !important;
        left: 0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('product-form');
    const stockInput = document.getElementById('stock');
    const lowStockInput = document.getElementById('low_stock_threshold');
    
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
    
    if (form && stockInput && lowStockInput) {
        form.addEventListener('submit', function(e) {
            const stock = parseInt(stockInput.value) || 0;
            const lowStock = parseInt(lowStockInput.value) || 0;
            
            if (stock < lowStock) {
                e.preventDefault();
                
                stockInput.classList.add('is-invalid');
                
                let feedbackDiv = stockInput.parentElement.querySelector('.invalid-feedback');
                if (!feedbackDiv) {
                    feedbackDiv = document.createElement('div');
                    feedbackDiv.className = 'invalid-feedback';
                    stockInput.parentElement.appendChild(feedbackDiv);
                }
                feedbackDiv.textContent = 'Stock Quantity must be greater than or equal to Low Stock Alert (' + lowStock + ')';
                
                stockInput.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                stockInput.focus();
            }
        });
        
        lowStockInput.addEventListener('change', function() {
            const stock = parseInt(stockInput.value) || 0;
            const lowStock = parseInt(this.value) || 0;
            
            if (stock < lowStock && stock > 0) {
                stockInput.classList.add('is-invalid');
                
                var existingPopover = bootstrap.Popover.getInstance(stockInput);
                if (existingPopover) {
                    existingPopover.dispose();
                }
                
                stockInput.focus();
            } else {
                stockInput.classList.remove('is-invalid');
                var popover = bootstrap.Popover.getInstance(stockInput);
                if (popover) {
                    popover.dispose();
                }
            }
});
    }
    
    if (stockInput) {
        const initialQty = stockInput.value || 0;
        document.querySelectorAll('input[name*="[quantity]"]').forEach(input => {
            input.value = initialQty;
        });
        
        stockInput.addEventListener('change', function() {
            const qty = this.value || 0;
            document.querySelectorAll('input[name*="[quantity]"]').forEach(input => {
                input.value = qty;
            });
        });
        
        stockInput.addEventListener('keyup', function() {
            const qty = this.value || 0;
            document.querySelectorAll('input[name*="[quantity]"]').forEach(input => {
                input.value = qty;
            });
        });
    }
});

@if(session('error') || $errors->has('general'))
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('alertModal'));
    document.getElementById('alertModalLabel').innerHTML = '<i class="bi bi-exclamation-triangle text-danger me-2"></i>Error';
    @if(session('error'))
    document.getElementById('alertModalBody').innerHTML = '<p class="mb-0">{{ session('error') }}</p>';
    @else
    document.getElementById('alertModalBody').innerHTML = '<p class="mb-0">{{ $errors->first('general') }}</p>';
    @endif
    modal.show();
});
@endif

function generateSKU() {
    const skuInput = document.getElementById('sku');
    
    if (skuInput) {
        const currentSku = skuInput.value;
        const parts = currentSku.split('-');
        if (parts.length === 2) {
            const lastNumber = parseInt(parts[1]);
            const newNumber = lastNumber + 1;
            skuInput.value = 'SKU-' + newNumber;
        }
    }
}

function previewFeaturedImage(input) {
    const preview = document.getElementById('featuredImagePreview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function syncQuantityToAll() {
    const stockInput = document.getElementById('stock');
    if (stockInput) {
        const qty = stockInput.value || 0;
        document.querySelectorAll('input[name*="[quantity]"]').forEach(input => {
            input.value = qty;
        });
    }
}

function getStockQty() {
    const stockInput = document.getElementById('stock');
    return stockInput ? (stockInput.value || 0) : 0;
}

function previewAttrImage(input, uniqueId) {
    const preview = document.getElementById('preview-attr-' + uniqueId);
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 80px; max-height: 80px;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewColorImage(input, previewId) {
    let preview = document.getElementById('preview-color-' + previewId);
    if (!preview) {
        const label = document.querySelector(`label[for="color-img-${previewId}"]`);
        if (label) {
            preview = document.createElement('div');
            preview.id = 'preview-color-' + previewId;
            preview.className = 'mt-1';
            label.parentNode.insertBefore(preview, label.nextSibling);
        }
    }

    if (preview) {
        preview.innerHTML = '';

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
}

function previewGalleryImages(input) {
    const preview = document.getElementById('galleryPreview');
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'position-relative';
                div.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">';
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

@php $attributesData = $attributesData ?? []; @endphp
const attributesData = @json($attributesData);

let selectedAttributes = {};
let generatedVariants = [];
let selectedProductColors = {};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.attribute-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const checked = document.querySelectorAll('.attribute-checkbox:checked');
            const selectedAttributeIds = Array.from(checked).map(c => c.value);

            Object.keys(selectedAttributes).forEach(attrId => {
                if (!selectedAttributeIds.includes(attrId)) {
                    delete selectedAttributes[attrId];
                    document.getElementById('attr-values-' + attrId)?.remove();
                }
            });

            checked.forEach(checkbox => {
                const attrId = checkbox.value;
                if (!selectedAttributes[attrId]) {
                    const attrName = checkbox.dataset.name;
                    const attrValues = JSON.parse(checkbox.dataset.values || '[]');
                    if (attrValues.length > 0) {
                        selectedAttributes[attrId] = { name: attrName, values: [] };
                        renderAttributeValues(attrId, { name: attrName, values: attrValues });
                    }
                }
            });

            updateAttributesLabel();
            updateGenerateButton();
            hideVariantsIfNoSelection();
        });
    });

    document.querySelectorAll('.color-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const checked = document.querySelectorAll('.color-checkbox:checked');
            const selectedColorIds = Array.from(checked).map(c => c.value);

            Object.keys(selectedProductColors).forEach(id => {
                if (!selectedColorIds.includes(id)) {
                    delete selectedProductColors[id];
                    document.getElementById('product-color-' + id)?.remove();
                }
            });

            checked.forEach(checkbox => {
                const colorId = checkbox.value;
                if (!selectedProductColors[colorId]) {
                    const colorName = checkbox.dataset.name;
                    const hexCode = checkbox.dataset.hex;
                    const colorValues = JSON.parse(checkbox.dataset.values || '[]');
                    selectedProductColors[colorId] = { name: colorName, hex_code: hexCode, values: colorValues };
                    renderProductColor(colorId, colorName, hexCode, colorValues);
                }
            });

            updateColorsLabel();
            updateProductColorsSection();
        });
    });

    document.getElementById('productAttributesSearch').addEventListener('input', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('#productAttributesList .form-check').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    });

    document.getElementById('productColorsSearch').addEventListener('input', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('#productColorsList .form-check').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    });
});

function updateAttributesLabel() {
    const checked = document.querySelectorAll('.attribute-checkbox:checked');
    const label = document.getElementById('productAttributesLabel');
    const tagsContainer = document.getElementById('productAttributesTags');
    if (checked.length === 0) {
        label.textContent = 'Select attributes...';
        tagsContainer.innerHTML = '';
    } else {
        label.textContent = checked.length + ' attribute(s) selected';
        tagsContainer.innerHTML = Array.from(checked).map(cb =>
            `<span class="badge bg-primary me-1">${cb.dataset.name} <i class="bi bi-x-circle-fill ms-1" style="cursor:pointer" onclick="this.parentElement.remove(); cb.checked=false; cb.dispatchEvent(new Event('change'));"></i></span>`
        ).join('');
    }
}

function updateColorsLabel() {
    const checked = document.querySelectorAll('.color-checkbox:checked');
    const label = document.getElementById('productColorsLabel');
    const tagsContainer = document.getElementById('productColorsTags');
    if (checked.length === 0) {
        label.textContent = 'Select colors...';
        tagsContainer.innerHTML = '';
    } else {
        label.textContent = checked.length + ' color(s) selected';
        tagsContainer.innerHTML = Array.from(checked).map(cb =>
            `<span class="badge me-1" style="background:${cb.dataset.hex}">${cb.dataset.name} <i class="bi bi-x-circle-fill ms-1" style="cursor:pointer;color:#fff" onclick="this.parentElement.remove(); cb.checked=false; cb.dispatchEvent(new Event('change'));"></i></span>`
        ).join('');
    }
}

function renderAttributeValues(attrId, attrData) {
    const container = document.getElementById('selectedAttributesContainer');
    if (!container) {
        console.error('Container not found! Looking for selectedAttributesContainer');
        return;
    }
    let html = `
    <div class="card mb-3" id="attr-values-${attrId}">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <strong>${attrData.name}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAttributeValues('${attrId}')">
                <i class="bi bi-trash"></i> Remove
            </button>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30%;">Value</th>
                        <th style="width: 25%;">Price (৳)</th>
                        <th style="width: 20%;">Quantity</th>
                        <th style="width: 25%;">SKU</th>
                        <th style="width: 10%;" class="text-center">Visible</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    if (attrData.values && attrData.values.length > 0) {
        attrData.values.forEach(value => {
            html += `
            <tr>
                <td>
                    <input type="hidden" name="product_attributes[${attrId}][values][${value.id}][value_id]" value="${value.id}">
                    <input type="hidden" name="product_attributes[${attrId}][values][${value.id}][value_name]" value="${value.value}">
                    ${value.value}
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_attributes[${attrId}][values][${value.id}][price]" value="0" min="0" step="0.01" placeholder="0.00">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_attributes[${attrId}][values][${value.id}][quantity]" value="${getStockQty()}" min="0" placeholder="0">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="product_attributes[${attrId}][values][${value.id}][sku]" value="${getNextSku('attr', attrId)}" readonly>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch d-flex justify-content-center">
                        <input type="hidden" name="product_attributes[${attrId}][values][${value.id}][is_visible]" value="1">
                        <input type="checkbox" class="form-check-input" checked onchange="this.previousElementSibling.value = this.checked ? '1' : '0';">
                    </div>
                </td>
            </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="5" class="text-center text-muted">No values available for this attribute</td>
            </tr>
        `;
    }
    
    html += `
                </tbody>
            </table>
        </div>
    </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    
    // Store the values for reference
    selectedAttributes[attrId].values = attrData.values.reduce((acc, v) => {
        acc[v.id] = v;
        return acc;
    }, {});
    
    // Add event listeners to checkboxes
    document.querySelectorAll(`#attr-values-${attrId} .attr-value-checkbox`).forEach(cb => {
        cb.addEventListener('change', function() {
            const attrId = this.dataset.attrId;
            const valueId = this.dataset.valueId;
            
            if (this.checked) {
                if (!selectedAttributes[attrId].values.find(v => v.id == valueId)) {
                    selectedAttributes[attrId].values.push({
                        id: valueId,
                        name: this.dataset.valueName
                    });
                }
            } else {
                selectedAttributes[attrId].values = selectedAttributes[attrId].values.filter(v => v.id != valueId);
            }
            
            updateGenerateButton();
            hideVariantsIfNoSelection();
        });
    });
}

function removeAttributeValues(attrId) {
    if (!confirm('Are you sure you want to remove this attribute? All associated data will be lost.')) return;

    document.getElementById('attr-values-' + attrId)?.remove();
    delete selectedAttributes[attrId];

    const checkbox = document.getElementById('attr_' + attrId);
    if (checkbox) {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event('change'));
    }

    updateGenerateButton();
    hideVariantsIfNoSelection();
}

function updateGenerateButton() {
    const btn = document.getElementById('generateVariantsBtn');
    const hasAttributes = Object.keys(selectedAttributes).length > 0;
    const hasValues = Object.values(selectedAttributes).some(a => a.values.length > 0);
    btn.disabled = !(hasAttributes && hasValues);
}

function hideVariantsIfNoSelection() {
    const hasAnyValues = Object.values(selectedAttributes).some(a => a.values.length > 0);
    if (!hasAnyValues) {
        document.getElementById('variantsSection').style.display = 'none';
        generatedVariants = [];
    }
}

function generateVariants() {
    const combinations = [];
    const attrKeys = Object.keys(selectedAttributes);
    
    if (attrKeys.length === 0) return;
    
    function combine(index, current) {
        if (index === attrKeys.length) {
            combinations.push({...current});
            return;
        }
        
        const attrId = attrKeys[index];
        const attr = selectedAttributes[attrId];
        
        if (attr.values.length === 0) {
            combine(index + 1, current);
        } else {
            attr.values.forEach(value => {
                current[attrId] = { attrId, attrName: attr.name, valueId: value.id, valueName: value.name };
                combine(index + 1, {...current});
            });
        }
    }
    
    combine(0, {});
    
    generatedVariants = combinations.map((combo, idx) => {
        const variantKey = Object.values(combo).map(v => v.valueName).join(' / ');
        return {
            id: idx,
            combination: combo,
            name: variantKey,
            sku: getNextSku('attr', attrId),
            price: '',
            stock: getStockQty(),
            image: null
        };
    });
    
    renderVariants();
    document.getElementById('variantsSection').style.display = 'block';
}

function renderVariants() {
    const container = document.getElementById('variantsContainer');
    
    let html = `
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Variant</th>
                    <th style="width: 120px;">SKU</th>
                    <th style="width: 100px;">Price</th>
                    <th style="width: 80px;">Stock</th>
                    <th style="width: 40px;"></th>
                </tr>
            </thead>
            <tbody>
    `;
    
    if (generatedVariants.length === 0) {
        html += `
        <tr>
            <td colspan="5" class="text-center text-muted py-3">
                No variants generated. Select attribute values and click "Generate Variants"
            </td>
        </tr>
        `;
    } else {
        generatedVariants.forEach((variant, idx) => {
            html += `
            <tr>
                <td>
                    <input type="hidden" name="variants[${idx}][combination]" value="${JSON.stringify(variant.combination)}">
                    <span class="badge bg-primary">${variant.name}</span>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="variants[${idx}][sku]" 
                           value="${variant.sku}" readonly>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="variants[${idx}][price]" 
                           value="${variant.price}" placeholder="0.00" step="0.01" min="0">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="variants[${idx}][stock]" 
                           value="${variant.stock}" min="0">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariant(${idx})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            `;
        });
    }
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function removeVariant(idx) {
    generatedVariants.splice(idx, 1);
    renderVariants();
}

function generateAllCombinations() {
    generateVariants();
}

// ==================== Product Attributes Section ====================
let selectedProductAttributes = {};
let colorSkuCounters = {};
let attrSkuCounters = {};

function getNextSku(type, id) {
    const counters = type === 'color' ? colorSkuCounters : attrSkuCounters;
    if (!counters[id]) {
        counters[id] = 0;
    }
    counters[id]++;
    const timestamp = new Date().getTime();
    const random = Math.floor(Math.random() * 1000);
    return 'SKU-' + timestamp + '-' + random;
}

document.addEventListener('DOMContentLoaded', function() {
    // Show debug info (commented out for production)
    // updateDebugInfo();
});

// function updateDebugInfo() {
//     const select = document.getElementById('productAttributesSelect');
//     const debugDiv = document.getElementById('debugInfo');
//     const debugContent = document.getElementById('debugContent');

//     if (debugDiv && debugContent) {
//         let info = 'Attributes found: ' + (select ? select.options.length : 0) + '<br>';
//         info += 'Selected attributes: ';

//         const selected = Array.from(select.selectedOptions).map(opt => opt.dataset.name);
//         info += selected.join(', ') + '<br>';

//         // Show first option data attributes
//         if (select && select.options.length > 0) {
//             const firstOption = select.options[0];
//             info += 'First option data-attribute-id: ' + firstOption.value + '<br>';
//             info += 'First option data-attribute-name: ' + firstOption.dataset.name + '<br>';
//             info += 'First option data-values: ' + firstOption.dataset.values + '<br>';
//         }

//         info += 'selectedProductAttributes keys: ' + Object.keys(selectedProductAttributes).join(', ') + '<br>';

//         debugContent.innerHTML = info;
//         debugDiv.style.display = 'block';
//     }
// }

function renderProductAttribute(attrId, attrName, values) {
    const container = document.getElementById('selectedAttributesContainer');
    if (!container) {
        console.error('Container not found!');
        return;
    }
    let html = `
    <div class="card mb-3" id="product-attr-${attrId}">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <strong>${attrName}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProductAttribute('${attrId}')">
                <i class="bi bi-trash"></i> Remove
            </button>
        </div>
        <div class="card-body py-2">
            <table class="table table-sm table-bordered mb-0">
<thead class="table-light">
                    <tr>
                        <th style="width: 30%;">Value</th>
                        <th style="width: 20%;">Price (৳)</th>
                        <th style="width: 15%;">Quantity</th>
                        <th style="width: 25%;">SKU</th>
                        <th style="width: 10%;" class="text-center">Visibility</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    values.forEach(val => {
        html += `
            <tr>
                <td>
                    <input type="hidden" name="product_attributes[${attrId}][values][${val.id}][value_id]" value="${val.id}">
                    <input type="hidden" name="product_attributes[${attrId}][values][${val.id}][value_name]" value="${val.value}">
                    ${val.value}
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_attributes[${attrId}][values][${val.id}][price]" value="0" min="0" step="0.01" placeholder="0.00">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_attributes[${attrId}][values][${val.id}][quantity]" value="${getStockQty()}" min="0" placeholder="0">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="product_attributes[${attrId}][values][${val.id}][sku]" value="${getNextSku('attr', attrId)}" readonly>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch d-flex justify-content-center">
                        <input type="hidden" name="product_attributes[${attrId}][values][${val.id}][is_visible]" value="1">
                        <input type="checkbox" class="form-check-input" checked onchange="this.previousElementSibling.value = this.checked ? '1' : '0';">
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    selectedProductAttributes[attrId].values = values.reduce((acc, v) => {
        acc[v.id] = v;
        return acc;
    }, {});
}

function removeProductAttribute(attrId) {
    if (!confirm('Are you sure you want to remove this attribute? All associated data will be lost.')) return;

    delete selectedProductAttributes[attrId];
    document.getElementById('product-attr-' + attrId)?.remove();

    const checkbox = document.getElementById('attr_' + attrId);
    if (checkbox) {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event('change'));
    }

    updateProductAttributeSection();
}

function updateProductAttributeSection() {
    const container = document.getElementById('selectedAttributesContainer');
    if (Object.keys(selectedProductAttributes).length === 0) {
        container.innerHTML = '';
    }
}

function renderProductColor(colorId, colorName, hexCode, values = []) {
    const container = document.getElementById('selectedColorsContainer');
    let html = `
    <div class="card mb-3" id="product-color-${colorId}">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <strong>
                <span style="background-color: ${hexCode}; width: 20px; height: 20px; display: inline-block; border-radius: 50%; border: 1px solid #ddd; vertical-align: middle; margin-right: 8px;"></span>
                ${colorName}
            </strong>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProductColor('${colorId}')">
                <i class="bi bi-trash"></i> Remove
            </button>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30%;">Value</th>
                        <th style="width: 20%;">Price (৳)</th>
                        <th style="width: 15%;">Quantity</th>
                        <th style="width: 25%;">SKU</th>
                        <th style="width: 10%;" class="text-center">Visibility</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (values && values.length > 0) {
        values.forEach(value => {
            const valueHex = value.hex_code || hexCode;
            html += `
            <tr>
                <td>
                    <input type="hidden" name="product_colors[${colorId}][values][${value.id}][value_id]" value="${value.id}">
                    <input type="hidden" name="product_colors[${colorId}][values][${value.id}][value_name]" value="${value.value}">
                    <span style="background-color: ${valueHex}; width: 16px; height: 16px; display: inline-block; border-radius: 3px; border: 1px solid #ddd; vertical-align: middle; margin-right: 6px;"></span>
                    ${value.value}
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_colors[${colorId}][values][${value.id}][price]" value="0" min="0" step="0.01" placeholder="0.00">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_colors[${colorId}][values][${value.id}][quantity]" value="${getStockQty()}" min="0" placeholder="0">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="product_colors[${colorId}][values][${value.id}][sku]" value="${getNextSku('color', colorId)}" readonly>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch d-flex justify-content-center">
                        <input type="hidden" name="product_colors[${colorId}][values][${value.id}][is_visible]" value="1">
                        <input type="checkbox" class="form-check-input" checked onchange="this.previousElementSibling.value = this.checked ? '1' : '0';">
                    </div>
                </td>
            </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="5" class="text-center text-muted">No values available for this color</td>
            </tr>
        `;
    }

    html += `
                </tbody>
            </table>
        </div>
    </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}

function removeProductColor(colorId) {
    if (!confirm('Are you sure you want to remove this color? All associated data will be lost.')) return;

    delete selectedProductColors[colorId];
    document.getElementById('product-color-' + colorId)?.remove();

    const checkbox = document.getElementById('color_' + colorId);
    if (checkbox) {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event('change'));
    }

    updateProductColorsSection();
}

function updateProductColorsSection() {
    const container = document.getElementById('selectedColorsContainer');
    if (Object.keys(selectedProductColors).length === 0) {
        container.innerHTML = '';
    }
}

document.addEventListener('mouseover', function(e) {
    if (e.target.tagName === 'IMG' && e.target.src && e.target.src.includes('/storage/')) {
        const preview = document.getElementById('imgHoverPreview');
        preview.innerHTML = '<img src="' + e.target.src + '" style="width: 100%; height: 100%; object-fit: contain; background: #fff;">';
        preview.classList.add('visible');
    }
});

document.addEventListener('mouseout', function(e) {
    if (e.target.tagName === 'IMG' && e.target.src && e.target.src.includes('/storage/')) {
        const preview = document.getElementById('imgHoverPreview');
        preview.classList.remove('visible');
    }
});

document.addEventListener('mousemove', function(e) {
    const preview = document.getElementById('imgHoverPreview');
    if (preview.classList.contains('visible')) {
        const x = e.clientX + 15;
        const y = e.clientY + 15;
        preview.style.left = x + 'px';
        preview.style.top = y + 'px';
    }
});

let specCount = 1;
function addSpecRow() {
    const container = document.getElementById('specsContainer');
    const row = document.createElement('div');
    row.className = 'row mb-2 spec-row';
    row.innerHTML = '<div class="col-md-5"><input type="text" class="form-control" name="specs[' + specCount + '][key]" placeholder="Specification Name (e.g., Material)"></div><div class="col-md-5"><input type="text" class="form-control" name="specs[' + specCount + '][value]" placeholder="Value (e.g., Stainless Steel)"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger" onclick="removeSpecRow(this)"><i class="bi bi-trash"></i></button></div>';
    container.appendChild(row);
    specCount++;
}
function removeSpecRow(btn) {
    const container = document.getElementById('specsContainer');
    if (container.children.length > 1) {
        btn.closest('.spec-row').remove();
    }
}

// ==================== VARIANT IMAGES (COMBINATION IMAGES) ====================
let variantImages = {};

function generateCombinationKey(attributes) {
    const parts = [];
    Object.keys(attributes).forEach(attr => {
        if (attributes[attr]) {
            parts.push(attr + '_' + attributes[attr]);
        }
    });
    parts.sort();
    return parts.join('_');
}

function getAllAttributeCombinations() {
    const attributeTypes = {};
    
    // Collect selected attributes with their values
    document.querySelectorAll('.attribute-checkbox:checked').forEach(cb => {
        const attrId = cb.value;
        const attrName = cb.dataset.name;
        const values = JSON.parse(cb.dataset.values || '[]');
        if (values.length > 0) {
            attributeTypes[attrName] = values.map(v => v.id);
        }
    });
    
    // Also add colors - get color VALUES (not just color IDs)
    Object.keys(selectedProductColors).forEach(colorId => {
        const colorData = selectedProductColors[colorId];
        if (colorData && colorData.values && colorData.values.length > 0) {
            // Use color_{colorId}_value_{valueId} format for value-level uniqueness
            colorData.values.forEach(val => {
                if (!attributeTypes['color']) {
                    attributeTypes['color'] = [];
                }
                attributeTypes['color'].push(val.id);
            });
        }
    });
    
    // Generate combinations using recursive approach
    const combinations = [];
    const attrNames = Object.keys(attributeTypes);
    
    if (attrNames.length === 0) {
        return combinations;
    }
    
    function combine(index, current) {
        if (index === attrNames.length) {
            const key = generateCombinationKey(current);
            if (key) combinations.push({ key, attrs: { ...current } });
            return;
        }
        
        const attrName = attrNames[index];
        const values = attributeTypes[attrName];
        
        values.forEach(valId => {
            current[attrName] = valId;
            combine(index + 1, current);
        });
        
        delete current[attrName];
    }
    
    combine(0, {});
    return combinations;
}

function renderVariantImagesSection() {
    const container = document.getElementById('variantImagesContainer');
    const combinations = getAllAttributeCombinations();
    
    if (combinations.length === 0) {
        container.innerHTML = '<p class="text-muted small">Select colors and/or attributes above to enable variant images.</p>';
        return;
    }
    
    let html = '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th style="width: 35%;">Combination</th><th style="width: 40%;">Image</th><th style="width: 25%;">Preview</th></tr></thead><tbody>';
    
    combinations.forEach(combo => {
        const key = combo.key;
        const existingData = variantImages[key] || {};
        const comboId = 'variant_' + key.replace(/[^a-zA-Z0-9]/g, '_');
        
        html += '<tr id="variant-img-row-' + comboId + '">';
        html += '<td><small class="text-muted">' + formatCombinationLabel(combo.attrs) + '</small></td>';
        html += '<td>';
        html += '<input type="file" class="form-control form-control-sm" id="variant-img-' + comboId + '" name="variant_images[' + key + ']" accept="image/*" onchange="previewVariantImage(this, \'' + comboId + '\')">';
        html += '</td>';
        html += '<td>';
        html += '<img id="variant-img-preview-' + comboId + '" src="' + (existingData.preview || 'https://placehold.co/60') + '" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">';
        html += '<button type="button" class="btn btn-sm btn-link text-danger ms-2" onclick="removeVariantImage(\'' + comboId + '\', \'' + key + '\')"><i class="bi bi-trash"></i></button>';
        html += '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function formatCombinationLabel(attrs) {
    const parts = [];
    Object.keys(attrs).forEach(attrName => {
        const valueId = attrs[attrName];
        if (attrName === 'color') {
            // Find color VALUE name from selectedProductColors
            let valueName = valueId;
            Object.keys(selectedProductColors).forEach(colorId => {
                const colorData = selectedProductColors[colorId];
                if (colorData && colorData.values) {
                    const val = colorData.values.find(v => v.id == valueId);
                    if (val) valueName = val.value_name || val.value || val.name;
                }
            });
            parts.push('Color: ' + valueName);
        } else {
            const attrCb = document.querySelector('.attribute-checkbox[data-name="' + attrName + '"]');
            if (attrCb) {
                const values = JSON.parse(attrCb.dataset.values || '[]');
                const valueData = values.find(v => v.id == valueId);
                parts.push((valueData ? valueData.value : valueId));
            } else {
                parts.push(valueId);
            }
        }
    });
    return parts.join(' + ');
}

function previewVariantImage(input, comboId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('variant-img-preview-' + comboId).src = e.target.result;

            // Store preview for form submission and re-render
            const key = comboId.substring(8); // Remove 'variant_' prefix
            const originalKey = comboId.replace('variant_', '');
            variantImages[originalKey] = { preview: e.target.result, file: input.files[0] };
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeVariantImage(comboId, key) {
    document.getElementById('variant-img-' + comboId).value = '';
    document.getElementById('variant-img-preview-' + comboId).src = 'https://placehold.co/60';
    delete variantImages[key];
}

// Listen for attribute and color changes
document.addEventListener('DOMContentLoaded', function() {
    // Watch for attribute checkbox changes
    document.querySelectorAll('.attribute-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            setTimeout(renderVariantImagesSection, 100);
        });
    });
    
    // Watch for color selection changes
    const originalUpdate = window.updateProductColorsSection;
    window.updateProductColorsSection = function() {
        if (originalUpdate) originalUpdate();
        setTimeout(renderVariantImagesSection, 100);
    };
    
    // Initial render
    renderVariantImagesSection();
});
</script>
@endpush
