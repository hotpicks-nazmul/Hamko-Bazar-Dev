@extends('admin.layouts.app')

@section('title', 'Bulk Export Products')

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-download me-2"></i>Bulk Export Products</h4>
        <p class="text-muted mb-0">Export products to CSV or JSON format</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Export Options Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Export Options</h6>
            </div>
            <div class="card-body">
                <form id="exportForm" method="GET" action="{{ route('admin.products.bulk-export.download') }}">
                    <!-- Format Selection -->
                    <div class="mb-4">
                        <label class="form-label">Export Format <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check card p-3 border">
                                    <input class="form-check-input" type="radio" name="format" id="formatCsv" value="csv" checked>
                                    <label class="form-check-label w-100" for="formatCsv">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-spreadsheet fs-4 text-success me-3"></i>
                                            <div>
                                                <strong>CSV</strong>
                                                <p class="text-muted small mb-0">Comma-separated values</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check card p-3 border">
                                    <input class="form-check-input" type="radio" name="format" id="formatJson" value="json">
                                    <label class="form-check-label w-100" for="formatJson">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-code fs-4 text-warning me-3"></i>
                                            <div>
                                                <strong>JSON</strong>
                                                <p class="text-muted small mb-0">JavaScript Object Notation</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Options -->
                    <h6 class="mb-3"><i class="bi bi-funnel me-2"></i>Filter Products</h6>
                    
                    <div class="row g-3 mb-4">
                        <!-- Category Filter -->
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>

                        <!-- Stock Status Filter -->
                        <div class="col-md-6">
                            <label class="form-label">Stock Status</label>
                            <select name="stock_status" class="form-select">
                                <option value="">All Stock Status</option>
                                <option value="in_stock">In Stock (> 10)</option>
                                <option value="low_stock">Low Stock (1-10)</option>
                                <option value="out_of_stock">Out of Stock (0)</option>
                            </select>
                        </div>

                        <!-- Featured Filter -->
                        <div class="col-md-6">
                            <label class="form-label">Featured Status</label>
                            <select name="featured" class="form-select">
                                <option value="">All Products</option>
                                <option value="yes">Featured Only</option>
                                <option value="no">Non-Featured Only</option>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="col-md-6">
                            <label class="form-label">Price Range</label>
                            <div class="input-group">
                                <input type="number" name="price_min" class="form-control" placeholder="Min" step="0.01">
                                <span class="input-group-text">to</span>
                                <input type="number" name="price_max" class="form-control" placeholder="Max" step="0.01">
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="col-md-6">
                            <label class="form-label">Created Date Range</label>
                            <div class="input-group">
                                <input type="date" name="date_from" class="form-control">
                                <span class="input-group-text">to</span>
                                <input type="date" name="date_to" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Export Summary -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2 fs-5"></i>
                            <div>
                                <strong>Total Products Available:</strong> {{ $totalProducts }}
                                <br>
                                <small>Apply filters to export specific products, or leave filters empty to export all.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download me-1"></i> Export Products
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                            <i class="bi bi-x-lg me-1"></i> Clear Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Export Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Export</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Export products quickly with preset filters:</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.products.bulk-export.download', ['status' => 'active']) }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-check-circle me-1"></i> Active Products
                    </a>
                    <a href="{{ route('admin.products.bulk-export.download', ['stock_status' => 'low_stock']) }}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-exclamation-triangle me-1"></i> Low Stock Products
                    </a>
                    <a href="{{ route('admin.products.bulk-export.download', ['stock_status' => 'out_of_stock']) }}" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Out of Stock Products
                    </a>
                    <a href="{{ route('admin.products.bulk-export.download', ['featured' => 'yes']) }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-star me-1"></i> Featured Products
                    </a>
                    <a href="{{ route('admin.products.bulk-export.download') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-seam me-1"></i> All Products
                    </a>
                </div>
            </div>
        </div>

        <!-- Export Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Export Information</h6>
            </div>
            <div class="card-body">
                <h6 class="small text-muted text-uppercase mb-2">CSV Format Includes:</h6>
                <ul class="small mb-3">
                    <li>Product ID, Name, Slug, SKU</li>
                    <li>Product Code, Barcode</li>
                    <li>Category, Brand</li>
                    <li>Descriptions (Short & Long)</li>
                    <li>Prices (Regular, Sale, Cost)</li>
                    <li>Stock Information</li>
                    <li>Status & Featured Flags</li>
                    <li>SEO Meta Data</li>
                    <li>Tags & Timestamps</li>
                </ul>
                
                <h6 class="small text-muted text-uppercase mb-2">Usage Tips:</h6>
                <ul class="small mb-0">
                    <li>CSV files can be opened in Excel</li>
                    <li>JSON format is ideal for developers</li>
                    <li>Exported files can be re-imported</li>
                </ul>
            </div>
        </div>

        <!-- Recent Exports Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Export Tips</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning py-2 small mb-0">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Tip:</strong> Use filters to export only the products you need. Large exports may take longer to process.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="exportForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-download me-1"></i> Export Products
    </button>
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
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function clearFilters() {
        document.getElementById('exportForm').reset();
    }
</script>
@endpush