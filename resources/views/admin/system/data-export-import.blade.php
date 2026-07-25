@extends('admin.layouts.app')

@section('title', 'Data Export/Import')

@section('content')

<!-- Stats Cards Row -->
<div class="stat-card-row stat-card-row-6 mb-4" style="width: 100%; max-width: 100%;">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-box"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Products</span>
            <span class="stat-card-value">{{ number_format($counts['products'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-folder"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Categories</span>
            <span class="stat-card-value">{{ number_format($counts['categories'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-award"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Brands</span>
            <span class="stat-card-value">{{ number_format($counts['brands'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-people"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Users</span>
            <span class="stat-card-value">{{ number_format($counts['users'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-ticket-perforated"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Coupons</span>
            <span class="stat-card-value">{{ number_format($counts['coupons'] ?? 0) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-images"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Banners</span>
            <span class="stat-card-value">{{ number_format($counts['banners'] ?? 0) }}</span>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Data Types Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-collection me-2"></i>Available Data Types</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="bi bi-box text-primary me-2"></i>
                            <div>
                                <div class="small text-muted">Products</div>
                                <div class="fw-bold">{{ $counts['products'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="bi bi-folder text-success me-2"></i>
                            <div>
                                <div class="small text-muted">Categories</div>
                                <div class="fw-bold">{{ $counts['categories'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="bi bi-award text-warning me-2"></i>
                            <div>
                                <div class="small text-muted">Brands</div>
                                <div class="fw-bold">{{ $counts['brands'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="bi bi-person text-info me-2"></i>
                            <div>
                                <div class="small text-muted">Users</div>
                                <div class="fw-bold">{{ $counts['users'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="bi bi-ticket-perforated text-danger me-2"></i>
                            <div>
                                <div class="small text-muted">Coupons</div>
                                <div class="fw-bold">{{ $counts['coupons'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="bi bi-image text-secondary me-2"></i>
                            <div>
                                <div class="small text-muted">Banners</div>
                                <div class="fw-bold">{{ $counts['banners'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="bi bi-collection-play text-dark me-2"></i>
                            <div>
                                <div class="small text-muted">Sliders</div>
                                <div class="fw-bold">{{ $counts['sliders'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <i class="bi bi-file-text me-2" style="color: #8a2be2;"></i>
                            <div>
                                <div class="small text-muted">Blogs</div>
                                <div class="fw-bold">{{ $counts['blogs'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export and Import Tabs -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <ul class="nav nav-tabs card-header-tabs" id="exportImportTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="export-tab" data-bs-toggle="tab" data-bs-target="#export-tab-pane" type="button" role="tab">
                            <i class="bi bi-download me-1"></i> Export Data
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import-tab-pane" type="button" role="tab">
                            <i class="bi bi-upload me-1"></i> Import Data
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="exportImportTabsContent">
                    <!-- Export Tab -->
                    <div class="tab-pane fade show active" id="export-tab-pane" role="tabpanel" tabindex="0">
                        <form method="GET" action="{{ route('admin.system.data-export.export') }}" target="_blank">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label">Select Data Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            <option value="">-- Select --</option>
                                            <optgroup label="Products & Catalog">
                                                <option value="products">Products</option>
                                                <option value="categories">Categories</option>
                                                <option value="brands">Brands</option>
                                                <option value="attributes">Attributes</option>
                                                <option value="colors">Colors</option>
                                                <option value="product_bundles">Product Bundles</option>
                                            </optgroup>
                                            <optgroup label="Marketing">
                                                <option value="coupons">Coupons</option>
                                                <option value="banners">Banners</option>
                                                <option value="sliders">Sliders</option>
                                                <option value="subscribers">Subscribers</option>
                                            </optgroup>
                                            <optgroup label="Content">
                                                <option value="blogs">Blogs</option>
                                                <option value="faqs">FAQs</option>
                                                <option value="pages">Pages</option>
                                            </optgroup>
                                            <optgroup label="Users & Customers">
                                                <option value="users">Users</option>
                                            </optgroup>
                                            <optgroup label="Settings">
                                                <option value="taxes">Taxes</option>
                                                <option value="delivery_zones">Delivery Zones</option>
                                                <option value="warehouses">Warehouses</option>
                                            </optgroup>
                                        </select>
                                        <div class="form-text">Select the type of data you want to export</div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label">Export Format</label>
                                        <select name="format" class="form-select">
                                            <option value="csv">CSV (Excel Compatible)</option>
                                            <option value="json">JSON</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-download me-1"></i> Export
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Download Template Section -->
                        <hr class="my-4">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-file-earmark-arrow-down me-2"></i>Download Import Templates</h6>
                        <p class="text-muted small mb-3">Download CSV templates to help you format your data correctly for import.</p>
                        <div class="row g-2">
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.system.data-export.template', ['type' => 'categories']) }}" class="btn btn-outline-secondary btn-sm w-100 text-start">
                                    <i class="bi bi-folder me-1"></i> Categories Template
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.system.data-export.template', ['type' => 'brands']) }}" class="btn btn-outline-secondary btn-sm w-100 text-start">
                                    <i class="bi bi-award me-1"></i> Brands Template
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.system.data-export.template', ['type' => 'products']) }}" class="btn btn-outline-secondary btn-sm w-100 text-start">
                                    <i class="bi bi-box me-1"></i> Products Template
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.system.data-export.template', ['type' => 'coupons']) }}" class="btn btn-outline-secondary btn-sm w-100 text-start">
                                    <i class="bi bi-ticket-perforated me-1"></i> Coupons Template
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.system.data-export.template', ['type' => 'banners']) }}" class="btn btn-outline-secondary btn-sm w-100 text-start">
                                    <i class="bi bi-image me-1"></i> Banners Template
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.system.data-export.template', ['type' => 'sliders']) }}" class="btn btn-outline-secondary btn-sm w-100 text-start">
                                    <i class="bi bi-collection-play me-1"></i> Sliders Template
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.system.data-export.template', ['type' => 'faqs']) }}" class="btn btn-outline-secondary btn-sm w-100 text-start">
                                    <i class="bi bi-question-circle me-1"></i> FAQs Template
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <a href="{{ route('admin.system.data-export.template', ['type' => 'pages']) }}" class="btn btn-outline-secondary btn-sm w-100 text-start">
                                    <i class="bi bi-file-text me-1"></i> Pages Template
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Import Tab -->
                    <div class="tab-pane fade" id="import-tab-pane" role="tabpanel" tabindex="0">
                        <form method="POST" action="{{ route('admin.system.data-export.import') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-5">
                                    <div class="mb-3">
                                        <label class="form-label">Select Data Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            <option value="">-- Select --</option>
                                            <optgroup label="Products & Catalog">
                                                <option value="products">Products</option>
                                                <option value="categories">Categories</option>
                                                <option value="brands">Brands</option>
                                                <option value="attributes">Attributes</option>
                                                <option value="colors">Colors</option>
                                                <option value="product_bundles">Product Bundles</option>
                                            </optgroup>
                                            <optgroup label="Marketing">
                                                <option value="coupons">Coupons</option>
                                                <option value="banners">Banners</option>
                                                <option value="sliders">Sliders</option>
                                                <option value="subscribers">Subscribers</option>
                                            </optgroup>
                                            <optgroup label="Users & Customers">
                                                <option value="users">Users</option>
                                            </optgroup>
                                            <optgroup label="Content">
                                                <option value="blogs">Blogs</option>
                                                <option value="faqs">FAQs</option>
                                                <option value="pages">Pages</option>
                                            </optgroup>
                                            <optgroup label="Settings">
                                                <option value="taxes">Taxes</option>
                                                <option value="delivery_zones">Delivery Zones</option>
                                                <option value="warehouses">Warehouses</option>
                                            </optgroup>
                                        </select>
                                        <div class="form-text">Select the type of data you want to import</div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label">Import Action</label>
                                        <select name="action" class="form-select">
                                            <option value="create">Create New Only (Skip Existing)</option>
                                            <option value="update">Update Existing Only</option>
                                            <option value="both">Create & Update</option>
                                        </select>
                                        <div class="form-text">How to handle records that already exist</div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Select File <span class="text-danger">*</span></label>
                                        <input type="file" name="file" class="form-control" accept=".csv,.json" required>
                                        <div class="form-text">CSV or JSON file (max 10MB)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-upload me-1"></i> Import Data
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" data-bs-toggle="modal" data-bs-target="#importInstructionsModal">
                                        <i class="bi bi-info-circle me-1"></i> Import Instructions
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Import Tips -->
                        <div class="alert alert-info mt-4 mb-0">
                            <h6 class="alert-heading"><i class="bi bi-lightbulb me-1"></i> Import Tips</h6>
                            <ul class="mb-0 small">
                                <li>Use the templates above to ensure correct column names</li>
                                <li>For CSV files, use comma (,) as the delimiter</li>
                                <li>For existing records, matching is done by unique fields (slug, code, email, etc.)</li>
                                <li>Importing large files may take a few minutes - please be patient</li>
                                <li>It's recommended to backup your data before importing</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Instructions Modal -->
<div class="modal fade" id="importInstructionsModal" tabindex="-1" aria-labelledby="importInstructionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importInstructionsModalLabel">
                    <i class="bi bi-info-circle me-2"></i>Import Instructions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>How to Import Data</h6>
                <ol>
                    <li><strong>Download a template</strong> - Use the template links above to get a properly formatted CSV file</li>
                    <li><strong>Fill in your data</strong> - Add your data to the template, keeping the header row intact</li>
                    <li><strong>Choose data type</strong> - Select what type of data you're importing</li>
                    <li><strong>Choose import action</strong> - Decide whether to create new records, update existing ones, or both</li>
                    <li><strong>Upload and import</strong> - Select your file and click Import Data</li>
                </ol>

                <h6 class="mt-4">Supported File Formats</h6>
                <ul>
                    <li><strong>CSV (Comma Separated Values)</strong> - Best for bulk data import from Excel or Google Sheets</li>
                    <li><strong>JSON</strong> - Best for developers or API data transfers</li>
                </ul>

                <h6 class="mt-4">Data Matching Rules</h6>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Data Type</th>
                            <th>Match Field</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Categories</td><td>slug</td></tr>
                        <tr><td>Brands</td><td>slug</td></tr>
                        <tr><td>Products</td><td>slug</td></tr>
                        <tr><td>Coupons</td><td>code</td></tr>
                        <tr><td>Banners</td><td>title</td></tr>
                        <tr><td>Sliders</td><td>title</td></tr>
                        <tr><td>Subscribers</td><td>email</td></tr>
                        <tr><td>FAQs</td><td>question</td></tr>
                        <tr><td>Pages</td><td>slug</td></tr>
                        <tr><td>Taxes</td><td>name</td></tr>
                        <tr><td>Delivery Zones</td><td>name</td></tr>
                        <tr><td>Warehouses</td><td>name</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Force Bootstrap Icons to display on this page */
    .stat-card-icon i,
    .stat-card-icon i::before,
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
    
    /* Override icon colors for stat cards */
    .stat-card-primary .stat-card-icon i::before { color: #0d6efd !important; }
    .stat-card-success .stat-card-icon i::before { color: #198754 !important; }
    .stat-card-info .stat-card-icon i::before { color: #0dcaf0 !important; }
    .stat-card-warning .stat-card-icon i::before { color: #ffc107 !important; }
    .stat-card-danger .stat-card-icon i::before { color: #dc3545 !important; }
    .stat-card-secondary .stat-card-icon i::before { color: #6c757d !important; }
    
    /* Make the whole icon colored */
    .stat-card-icon i { color: inherit !important; }
    
    /* Text color classes */
    .text-primary { color: #0d6efd !important; }
    .text-success { color: #198754 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #0dcaf0 !important; }
    .text-secondary { color: #6c757d !important; }
    .text-dark { color: #212529 !important; }
    
    .bg-purple {
        background-color: rgba(138, 43, 226, 0.1) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-submit export form when type changes
    document.querySelector('select[name="type"]')?.addEventListener('change', function() {
        // This is optional - could auto-submit for export
    });

    // File size validation
    document.querySelector('input[name="file"]')?.addEventListener('change', function() {
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (this.files[0] && this.files[0].size > maxSize) {
            alert('File size exceeds 10MB limit. Please choose a smaller file.');
            this.value = '';
        }
    });
</script>
@endpush
