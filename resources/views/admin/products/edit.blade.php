@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Product: {{ $product->name }}</h4>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="product-form">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
                    
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                        </div>
                        <div class="card-body">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="short_description" class="form-label">Short Description</label>
                                <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2" maxlength="500">{{ old('short_description', $product->short_description) }}</textarea>
                                @error('short_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description', $product->long_description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Regular Price (৳) *</label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sale_price" class="form-label">Sale Price (৳)</label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}">
                                        @error('sale_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sku" class="form-label">SKU *</label>
                                        <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" readonly>
                                        <div class="form-text">Auto-generated, cannot be edited</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="product_code" class="form-label">Product Code</label>
                                        <input type="text" class="form-control @error('product_code') is-invalid @enderror" id="product_code" name="product_code" value="{{ old('product_code', $product->product_code) }}" placeholder="e.g., PRD-001">
                                        @error('product_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="barcode" class="form-label">Barcode</label>
                                        <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}">
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
                                        <select class="form-select @error('brand') is-invalid @enderror" id="brand" name="brand">
                                            <option value="">Select Brand</option>
                                            @foreach($brands as $id => $name)
                                                <option value="{{ $id }}" {{ old('brand', $product->brand_id) == $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('brand')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @if(auth()->user()->hasPermission('products.view-cost'))
                                    <div class="mb-3">
                                        <label for="purchase_price" class="form-label">Purchase/Cost Price (৳)</label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('purchase_price') is-invalid @enderror" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}">
                                        @error('purchase_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stock Quantity *</label>
                                        <input type="number" min="0" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->quantity) }}" required>
                                        @error('stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="low_stock_threshold" class="form-label">Low Stock Alert</label>
                                        <input type="number" min="0" class="form-control @error('low_stock_threshold') is-invalid @enderror" id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}">
                                        @error('low_stock_threshold')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stock_status" class="form-label">Stock Status</label>
                                        <select class="form-select" id="stock_status" name="stock_status">
                                            <option value="in_stock" {{ old('stock_status', $product->stock_status ?? 'in_stock') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                            <option value="out_of_stock" {{ old('stock_status', $product->stock_status ?? 'in_stock') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                            <option value="pre_order" {{ old('stock_status', $product->stock_status ?? 'in_stock') == 'pre_order' ? 'selected' : '' }}>Pre Order</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="weight" class="form-label">Weight (kg)</label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" value="{{ old('weight', $product->weight) }}" placeholder="0.00">
                                        @error('weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Product weight in kilograms</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="dimensions" class="form-label">Dimensions</label>
                                        <input type="text" class="form-control @error('dimensions') is-invalid @enderror" id="dimensions" name="dimensions" value="{{ old('dimensions', $product->dimensions) }}" placeholder="e.g., 30 x 20 x 15 cm">
                                        @error('dimensions')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Length x Width x Height</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Custom Specifications Section -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="mb-2"><i class="bi bi-list-check me-2"></i>Custom Specifications</h6>
                                    <div id="specsContainer">
                                        @php $specs = is_array($product->specs) ? $product->specs : []; @endphp
                                        @if(empty($specs))
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
                                        @else
                                        @foreach($specs as $index => $spec)
                                        <div class="row mb-2 spec-row">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="specs[{{ $index }}][key]" value="{{ $spec['key'] ?? '' }}" placeholder="Specification Name (e.g., Material)">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="specs[{{ $index }}][value]" value="{{ $spec['value'] ?? '' }}" placeholder="Value (e.g., Stainless Steel)">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeSpecRow(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSpecRow()">
                                        <i class="bi bi-plus-lg me-1"></i> Add Specification
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">
                                                Featured Product
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                    </div>
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
                        </div>
                    </div>
                    
                    <!-- Product Colors Section -->
                    <div class="card border-0 shadow-sm mb-3" style="overflow: visible !important;">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Product Colors</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">Select colors to add price, quantity and SKU for each</p>

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
                                            <div class="form-check">
                                                <input class="form-check-input color-checkbox" type="checkbox"
                                                    value="{{ $color->id }}"
                                                    id="color_{{ $color->id }}"
                                                    data-name="{{ $color->name }}"
                                                    data-hex="{{ $color->hex_code }}"
                                                    data-values='@json($color->activeValues->map(function($v) { return ["id" => $v->id, "value" => $v->value, "hex_code" => $v->hex_code]; })->toArray())'>
                                                <label class="form-check-label w-100" for="color_{{ $color->id }}">
                                                    {{ $color->name }}
                                                    <span style="background: {{ $color->hex_code }}; width: 16px; height: 16px; display: inline-block; border-radius: 50%; border: 1px solid #ddd; vertical-align: middle; margin-left: 8px;"></span>
                                                    <small class="text-muted">({{ $color->activeValues->count() }} values)</small>
                                                </label>
                                            </div>
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
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}" {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                @if($product->featured_image)
                                    @php
                                        $imageUrl = $product->featured_image;
                                        if (!str_starts_with($imageUrl, 'http') && !str_starts_with($imageUrl, '/storage/')) {
                                            $imageUrl = '/storage/' . $imageUrl;
                                        }
                                    @endphp
                                    <div class="mb-2 position-relative d-inline-block" id="featured-image-container">
                                        <img src="{{ $imageUrl }}" class="img-thumbnail" style="max-width: 150px;" id="featured-image-preview">
                                        <button type="button" class="badge bg-danger rounded-circle border-0 position-absolute top-0 start-100 translate-middle p-0"
                                            style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                                            onclick="deleteFeaturedImage({{ $product->id }})">
                                            <i class="bi bi-x" style="font-size: 12px;"></i>
                                        </button>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                       id="image" name="image" accept="image/*" form="product-form"
                                       onchange="previewFeaturedImage(this)"
                                       data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('image') }}">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Main image. Max 5MB. Recommended: 1920x1080px</div>
                                <div id="featuredImagePreview" class="mt-2"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="images" class="form-label">Gallery Images</label>
                                @php
                                    $rawImages = $product->images ?? ($product->gallery ?? null);
                                    if (is_string($rawImages)) {
                                        $galleryImages = json_decode($rawImages, true) ?: [];
                                    } else {
                                        $galleryImages = is_array($rawImages) ? $rawImages : [];
                                    }
                                @endphp
                                @if(!empty($galleryImages) && is_array($galleryImages))
                                    <div class="mb-2 d-flex flex-wrap gap-2">
                                        @foreach($galleryImages as $index => $img)
                                            @php
                                                $galleryUrl = is_array($img) ? ($img['url'] ?? $img['path'] ?? '') : $img;
                                                if (!is_string($galleryUrl)) {
                                                    $galleryUrl = '';
                                                }
                                                if (!str_starts_with($galleryUrl, 'http') && !str_starts_with($galleryUrl, '/storage/') && $galleryUrl) {
                                                    $galleryUrl = '/storage/' . $galleryUrl;
                                                }
                                            @endphp
                                            <div class="position-relative gallery-image-item" data-index="{{ $index }}" id="gallery-img-{{ $index }}">
                                                <img src="{{ $galleryUrl }}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                <button type="button" class="badge bg-danger rounded-circle border-0 position-absolute top-0 start-100 translate-middle p-0"
                                                    style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                                                    onclick="markGalleryImageForDeletion({{ $index }})">
                                                    <i class="bi bi-x" style="font-size: 12px;"></i>
                                                </button>
                                                <input type="hidden" name="deleted_image_indices[]" value="{{ $index }}" disabled class="delete-index-{{ $index }}">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('images') is-invalid @enderror" 
                                       id="images" name="images[]" multiple accept="image/*" form="product-form"
                                       onchange="previewGalleryImages(this)"
                                       data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $errors->first('images') }}">
                                @error('images')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Additional images. Multiple allowed.</div>
                                <div id="galleryPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                            </div>
</div>
                     </div>
                     
                     <!-- Variant Images (Combination Images) -->
                     <div class="card border-0 shadow-sm mb-3">
                         <div class="card-header bg-white">
                             <h6 class="mb-0"><i class="bi bi-image me-2"></i>Variant Images</h6>
                         </div>
                         <div class="card-body">
                             <p class="text-muted small mb-3">Upload images for specific attribute combinations.</p>
                             <div id="variantImagesContainer">
                                 <p class="text-muted small">Select colors and/or attributes below to enable variant images.</p>
                             </div>
                         </div>
                     </div>
                     
                     <!-- Related Products Quick Access -->
                     <div class="card border-0 shadow-sm mb-3">
                         <div class="card-header bg-white">
                             <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Related Products</h6>
                         </div>
                        <div class="card-body py-2">
                            @php
                                $relatedProducts = $product->relatedProducts()->limit(5)->get();
                                $relatedCount = $product->relatedProducts()->count();
                            @endphp
                            @if($relatedCount > 0)
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-primary me-2">{{ $relatedCount }}</span>
                                    <span class="small text-muted">related product(s)</span>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @foreach($relatedProducts as $rel)
                                        @php
                                            $relImages = is_string($rel->images) ? json_decode($rel->images, true) : $rel->images;
                                            $relImage = $rel->featured_image ?? ($relImages[0] ?? null);
                                        @endphp
                                        <img src="{{ $relImage ?? asset('images/placeholder.png') }}" 
                                             alt="{{ $rel->name }}" 
                                             class="rounded" 
                                             style="width: 40px; height: 40px; object-fit: cover;"
                                             title="{{ $rel->name }}">
                                    @endforeach
                                    @if($relatedCount > 5)
                                        <span class="badge bg-light text-dark" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            +{{ $relatedCount - 5 }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted small mb-2">No related products added yet.</p>
                            @endif
                            <a href="{{ route('admin.products.related', $product->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-diagram-3 me-1"></i> Manage Related Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    </form>

<!-- Floating Buttons - Following Preference.md standard -->
<div class="floating-save-container">
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="product-form" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Product
    </button>
</div>

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

@push('styles')
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes slideOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.8); }
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

@else
<div class="alert alert-warning border-0 shadow-sm mb-4">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Access Restricted:</strong> You don't have permission to access products.
    Please contact an administrator if you need access.
</div>
@endif
@endsection

@push('scripts')
<script>
// Form validation for stock quantity
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('product-form');
    const stockInput = document.getElementById('stock');
    const lowStockInput = document.getElementById('low_stock_threshold');
    
    // Auto-scroll to first error field (using existing invalid-feedback)
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
                
                // Add error class
                stockInput.classList.add('is-invalid');
                
                // Create or update invalid-feedback div
                let feedbackDiv = stockInput.parentElement.querySelector('.invalid-feedback');
                if (!feedbackDiv) {
                    feedbackDiv = document.createElement('div');
                    feedbackDiv.className = 'invalid-feedback';
                    stockInput.parentElement.appendChild(feedbackDiv);
                }
                feedbackDiv.textContent = 'Stock Quantity must be greater than or equal to Low Stock Alert (' + lowStock + ')';
                
                // Scroll to the field
                stockInput.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                stockInput.focus();
            }
        });
        
        // Real-time validation when low stock threshold changes
        lowStockInput.addEventListener('change', function() {
            const stock = parseInt(stockInput.value) || 0;
            const lowStock = parseInt(this.value) || 0;
            
            if (stock < lowStock && stock > 0) {
                stockInput.classList.add('is-invalid');
                
                // Dispose existing popover if any
                var existingPopover = bootstrap.Popover.getInstance(stockInput);
                if (existingPopover) {
                    existingPopover.dispose();
                }
                
                // Let browser show native validation message
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
    
    // Auto-sync stock quantity to all attribute/color quantities
    if (stockInput) {
        // Function to sync stock quantity to all quantity fields
        function syncStockQuantity() {
            const qty = stockInput.value || 0;
            document.querySelectorAll('input[name*="[quantity]"]').forEach(input => {
                input.value = qty;
            });
        }
        
        // Initial sync with delay to ensure attributes are rendered
        setTimeout(syncStockQuantity, 500);
        
        // Also use MutationObserver to catch dynamically added elements
        const observer = new MutationObserver(() => {
            syncStockQuantity();
        });
        observer.observe(document.body, { childList: true, subtree: true });
        
        stockInput.addEventListener('change', syncStockQuantity);
        stockInput.addEventListener('keyup', syncStockQuantity);
    }
});

// Preview featured image
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

// Preview gallery images
function previewGalleryImages(input) {
    const preview = document.getElementById('galleryPreview');
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">';
                preview.appendChild(div.firstChild);
            };
            reader.readAsDataURL(file);
        });
    }
}

function previewAttrImage(input, uniqueId) {
    const preview = document.getElementById('preview-attr-' + uniqueId);
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); animation: fadeIn 0.3s ease;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewColorImage(input, uniqueId) {
    const preview = document.getElementById('preview-color-' + uniqueId);
    if (preview) {
        preview.innerHTML = '';

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); animation: fadeIn 0.3s ease;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
}

// ==================== Product Attributes Section ====================
@php $attributesData = $attributesData ?? []; $existingAttributes = $existingAttributes ?? []; @endphp
const attributesData = @json($attributesData);
const existingAttributes = @json($existingAttributes);

// ==================== Product Colors Section ====================
@php $colorsData = $colorsData ?? []; $existingColors = $existingColors ?? []; @endphp
const colorsData = @json($colorsData);
const existingColors = @json($existingColors);

let selectedAttributes = {};
let selectedProductColors = {};
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
    const existingAttrIds = Object.keys(existingAttributes).map(id => parseInt(id));
    existingAttrIds.forEach(attrId => {
        const checkbox = document.getElementById('attr_' + attrId);
        if (checkbox) {
            checkbox.checked = true;
            const attrName = checkbox.dataset.name;
            const attrValues = JSON.parse(checkbox.dataset.values || '[]');
            if (attrValues.length > 0 && !selectedAttributes[attrId]) {
                selectedAttributes[attrId] = { name: attrName, values: [] };
                let existingValues = [];
                const existingAttrData = existingAttributes[attrId];
                if (existingAttrData && existingAttrData.values) {
                    existingValues = existingAttrData.values;
                }
                renderAttributeValues(attrId, { name: attrName, values: attrValues }, existingValues);
            }
        }
    });

    existingColors.forEach(colorData => {
        const checkbox = document.getElementById('color_' + colorData.color_id);
        if (checkbox) {
            checkbox.checked = true;
            const colorName = checkbox.dataset.name;
            const hexCode = checkbox.dataset.hex;
            const colorValues = JSON.parse(checkbox.dataset.values || '[]');
            selectedProductColors[colorData.color_id] = { name: colorName, hex_code: hexCode, values: colorValues };
            const hasExistingValues = colorData.values && typeof colorData.values === 'object' && Object.keys(colorData.values).length > 0;
            
            let valuesArray;
            if (hasExistingValues) {
                const savedValueIds = Object.keys(colorData.values).map(k => parseInt(colorData.values[k].value_id));
                
                // Always include all fresh values from database, not just saved ones
                const freshColor = colorsData.find(c => c.id == colorData.color_id);
                if (freshColor && freshColor.values && freshColor.values.length > 0) {
                    valuesArray = freshColor.values.map(fv => {
                        const savedValue = Object.values(colorData.values).find(sv => parseInt(sv.value_id) === fv.id);
                        return {
                            id: fv.id,
                            value: fv.value,
                            hex_code: fv.hex_code,
                            existingData: savedValue || null
                        };
                    });
                } else {
                    // Fallback to saved values only
                    valuesArray = Object.values(colorData.values).map(v => {
                        return { id: v.value_id, value: v.value_name, hex_code: v.hex_code };
                    });
                }
            } else {
                valuesArray = colorValues;
            }
            renderProductColor(colorData.color_id, colorName, hexCode, valuesArray, colorData);
        }
    });

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
                        let existingValues = [];
                        const existingAttrData = existingAttributes[attrId];
                        if (existingAttrData && existingAttrData.values) {
                            existingValues = existingAttrData.values;
                        }
                        renderAttributeValues(attrId, { name: attrName, values: attrValues }, existingValues);
                    }
                }
            });

            updateAttributesLabel();
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
                    const existingColorData = existingColors.find(c => c.color_id == colorId);
                    const hasExistingValues = existingColorData && existingColorData.values && typeof existingColorData.values === 'object' && Object.keys(existingColorData.values).length > 0;
                    if (hasExistingValues) {
                        const valuesArray = Object.values(existingColorData.values).map(v => {
                            // Try to get hex_code from colorsData (fresh from database)
                            let hex_code = v.hex_code || hexCode;
                            if (!hex_code || hex_code === hexCode) {
                                const freshColor = colorsData.find(c => c.id == colorId);
                                if (freshColor && freshColor.values) {
                                    const freshValue = freshColor.values.find(fv => fv.id == v.value_id);
                                    if (freshValue && freshValue.hex_code) {
                                        hex_code = freshValue.hex_code;
                                    }
                                }
                            }
                            return { id: v.value_id, value: v.value_name, hex_code: hex_code };
                        });
                        renderProductColor(colorId, colorName, hexCode, valuesArray, existingColorData);
                    } else {
                        renderProductColor(colorId, colorName, hexCode, colorValues, existingColorData);
                    }
                }
            });

            updateColorsLabel();
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

    updateAttributesLabel();
    updateColorsLabel();

    // Render variant images section after attributes and colors are loaded
    if (typeof renderVariantImagesSection === 'function') {
        setTimeout(renderVariantImagesSection, 100);
    }
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

function renderAttributeValues(attrId, attrData, existingValues = null) {
    const container = document.getElementById('selectedAttributesContainer');
    if (!container) {
        console.error('Container not found!');
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
                        <th style="width: 20%;">Price (৳)</th>
                        <th style="width: 15%;">Quantity</th>
                        <th style="width: 25%;">SKU</th>
                        <th style="width: 10%;" class="text-center">Visibility</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    if (attrData.values && attrData.values.length > 0) {
        attrData.values.forEach(value => {
            let existingData = { price: 0, quantity: 0, sku: '', image: null, is_visible: true };
            if (existingValues) {
                const valueId = String(value.id);
                const found = existingValues[valueId] || Object.values(existingValues).find(ev => parseInt(ev.value_id) === parseInt(value.id));
                if (found) {
                    existingData = found;
                }
            }
            
            const existingImage = existingData.image ? existingData.image : null;
            const uniqueId = attrId + '-' + value.id;
            const isVisible = existingData.is_visible !== undefined ? existingData.is_visible : true;
            
            html += `
            <tr>
                <td>
                    <input type="hidden" name="product_attributes[${attrId}][values][${value.id}][value_id]" value="${value.id}">
                    <input type="hidden" name="product_attributes[${attrId}][values][${value.id}][value_name]" value="${value.value}">
                    ${value.value}
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_attributes[${attrId}][values][${value.id}][price]" value="${existingData.price || 0}" min="0" step="0.01" placeholder="0.00">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_attributes[${attrId}][values][${value.id}][quantity]" value="${existingData.quantity || 0}" min="0" placeholder="0">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="product_attributes[${attrId}][values][${value.id}][sku]" value="${existingData && existingData.sku ? existingData.sku : getNextSku('attr', attrId)}" readonly>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch d-flex justify-content-center">
                        <input type="hidden" name="product_attributes[${attrId}][values][${value.id}][is_visible]" value="${isVisible ? '1' : '0'}">
                        <input type="checkbox" class="form-check-input" id="attr-vis-${uniqueId}" ${isVisible ? 'checked' : ''} onchange="this.previousElementSibling.value = this.checked ? '1' : '0';">
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
    
    selectedAttributes[attrId].values = attrData.values.reduce((acc, v) => {
        acc[v.id] = v;
        return acc;
    }, {});
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
}

function removeAttrImage(attrId, valueId) {
    if (!confirm('Delete this image?')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const card = document.getElementById('attr-values-' + attrId);
    const row = card ? card.querySelector(`tr:has(input[value="${valueId}"])`) : null;
    const imgDiv = row ? row.querySelector('.position-relative.d-inline-block') : null;
    
    fetch(`/admin/products/{{ $product->id }}/attributes/${attrId}/${valueId}/image`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (row) {
                const hiddenInput = row.querySelector(`input[name$="[existing_image]"]`);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
                if (imgDiv) {
                    imgDiv.remove();
                }
            }
        } else {
            alert('Failed to delete image');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the image');
    });
}

function renderProductColor(colorId, colorName, hexCode, values = [], existingData = null) {
    const container = document.getElementById('selectedColorsContainer');

    let existingPrice = 0;
    let existingQuantity = 0;
    let existingSku = '';
    let existingImage = null;

    if (existingData) {
        existingPrice = existingData.price_adjustment || existingData.price || 0;
        existingQuantity = existingData.quantity || 0;
        existingSku = existingData.sku ? existingData.sku : getNextSku('color', colorId);
        existingImage = existingData.image || null;
    } else {
        existingSku = getNextSku('color', colorId);
    }

    let imageHtml = '';
    if (existingImage) {
        imageHtml = `
            <div class="position-relative d-inline-block" style="display: inline-block;">
                <div style="width: 50px; height: 50px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                    <img src="${existingImage}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <button type="button" class="badge bg-danger rounded-circle border-0 position-absolute top-0 start-100 translate-middle p-0"
                    style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                    onclick="removeColorImage('${colorId}')">
                    <i class="bi bi-x" style="font-size: 12px;"></i>
                </button>
            </div>
            <input type="hidden" name="product_colors[${colorId}][existing_image]" value="${existingImage}">
        `;
    }

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
            const valueId = value.id || value.value_id;
            const valueName = value.value || value.value_name || value.name;
            const valueObj = value.existingData || null;
            const valuePrice = valueObj ? (valueObj.price || 0) : 0;
            const valueQty = valueObj ? (valueObj.quantity || 0) : 0;
            const valueSku = valueObj && valueObj.sku ? valueObj.sku : getNextSku('color', colorId);
            const valueImg = valueObj ? (valueObj.image || '') : '';
const valueVisible = valueObj ? (valueObj.is_visible !== undefined ? valueObj.is_visible : true) : true;
            
            html += `
            <tr>
                <td>
                    <input type="hidden" name="product_colors[${colorId}][values][${valueId}][value_id]" value="${valueId}">
                    <input type="hidden" name="product_colors[${colorId}][values][${valueId}][value_name]" value="${valueName}">
                    <input type="hidden" name="product_colors[${colorId}][values][${valueId}][hex_code]" value="${valueHex}">
                    <span style="background-color: ${valueHex}; width: 16px; height: 16px; display: inline-block; border-radius: 3px; border: 1px solid #ddd; vertical-align: middle; margin-right: 6px;"></span>
                    ${valueName}
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_colors[${colorId}][values][${valueId}][price]" value="${valuePrice}" min="0" step="0.01" placeholder="0.00">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="product_colors[${colorId}][values][${valueId}][quantity]" value="${valueQty}" min="0" placeholder="0">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="product_colors[${colorId}][values][${valueId}][sku]" value="${valueSku}" readonly>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch d-flex justify-content-center">
                        <input type="hidden" name="product_colors[${colorId}][values][${valueId}][is_visible]" value="${valueVisible ? '1' : '0'}">
                        <input type="checkbox" class="form-check-input" id="color-vis-${colorId}-${valueId}" ${valueVisible ? 'checked' : ''} onchange="this.previousElementSibling.value = this.checked ? '1' : '0';">
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
}

// Gallery Image Deletion (AJAX)
function markGalleryImageForDeletion(index) {
    if (!confirm('Delete this image?')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/admin/products/{{ $product->id }}/images/${index}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const imgItem = document.getElementById('gallery-img-' + index);
            if (imgItem) {
                imgItem.remove();
            }
        } else {
            alert('Failed to delete image');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the image');
    });
}

// Delete Featured Image (AJAX)
function deleteFeaturedImage(productId) {
    if (!confirm('Delete this featured image?')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/admin/products/${productId}/featured-image`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const container = document.getElementById('featured-image-container');
            if (container) {
                container.remove();
            }
        } else {
            alert('Failed to delete image');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the image');
    });
}

// Delete Color Image (AJAX)
function removeColorImage(colorId) {
    if (!confirm('Delete this image?')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const colorCard = document.getElementById('product-color-' + colorId);
    const imgContainer = colorCard ? colorCard.querySelector('.position-relative.d-inline-block') : null;

    fetch(`/admin/products/{{ $product->id }}/colors/${colorId}/image`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const colorCard = document.getElementById('product-color-' + colorId);
            if (colorCard) {
                const hiddenInput = colorCard.querySelector(`input[name="product_colors[${colorId}][existing_image]"]`);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
                if (imgContainer) {
                    imgContainer.remove();
                }
            }
        } else {
            alert('Failed to delete image');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the image');
    });
}

function removeColorValueImage(colorId, valueId) {
    if (!confirm('Delete this image?')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const colorCard = document.getElementById('product-color-' + colorId);
    const row = colorCard ? colorCard.querySelector(`tr:has(input[value="${valueId}"])`) : null;
    const imgDiv = row ? row.querySelector('.position-relative.d-inline-block') : null;

    fetch(`/admin/products/{{ $product->id }}/colors/${colorId}/values/${valueId}/image`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (row) {
                const hiddenInput = row.querySelector(`input[name$="[existing_image]"]`);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
                if (imgDiv) {
                    imgDiv.remove();
                }
            }
        } else {
            alert('Failed to delete image');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the image');
    });
}

let specCount = {{ is_array($product->specs) ? count($product->specs) : 1 }};
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
// Pre-populate with existing variant images from database
let variantImages = {};

// Load existing images from PHP data
var variantImagesData = @json($variantImagesData ?? []);
Object.keys(variantImagesData).forEach(key => {
    variantImages[key] = variantImagesData[key];
});

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
            if (!attributeTypes['color']) {
                attributeTypes['color'] = [];
            }
            colorData.values.forEach(val => {
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
        if (existingData.image) {
            html += '<input type="hidden" name="existing_variant_images[' + key + ']" value="' + existingData.image + '">';
        }
        html += '</td>';
        html += '<td>';
        html += '<img id="variant-img-preview-' + comboId + '" src="' + (existingData.image || 'https://placehold.co/60') + '" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">';
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
            const img = document.getElementById('variant-img-preview-' + comboId);
            img.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeVariantImage(comboId, key) {
    document.getElementById('variant-img-' + comboId).value = '';
    document.getElementById('variant-img-preview-' + comboId).src = 'https://placehold.co/60';
    delete variantImages[key];
}

// Listen for attribute and color changes (for dynamic updates after page load)
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
});
</script>
@endpush
