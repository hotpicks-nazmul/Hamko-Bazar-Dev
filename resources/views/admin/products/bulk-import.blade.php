@extends('admin.layouts.app')

@section('title', 'Bulk Import Products')

@section('content')
@if(auth()->user()->hasPermission('products') && auth()->user()->hasPermission('products.view'))
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-upload me-2"></i>Bulk Import Products</h4>
        <p class="text-muted mb-0">Import products from CSV or Excel file</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Upload Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload File</h6>
            </div>
            <div class="card-body">
                <form id="importForm" method="POST" action="{{ route('admin.products.bulk-import.process') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- File Upload Area -->
                    <div class="mb-4">
                        <label class="form-label">Import File <span class="text-danger">*</span></label>
                        <div class="upload-area p-4 border border-2 border-dashed rounded text-center" id="uploadArea" style="cursor: pointer;">
                            <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
                            <p class="mb-1">Drag and drop your file here, or click to browse</p>
                            <small class="text-muted">Supported formats: CSV, XLS, XLSX (Max: 10MB)</small>
                            <input type="file" name="import_file" id="importFile" class="d-none" accept=".csv,.xls,.xlsx,.txt">
                        </div>
                        <div id="fileInfo" class="mt-2" style="display: none;">
                            <div class="alert alert-info py-2 mb-0">
                                <i class="bi bi-file-earmark me-2"></i>
                                <span id="fileName"></span>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="clearFile()">Remove</button>
                            </div>
                        </div>
                        @error('import_file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Import Options -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Default Category</label>
                            <select name="default_category" class="form-select">
                                <option value="">-- Select Category (Optional) --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Products without category will be assigned to this category</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Import Options</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skip_duplicates" id="skipDuplicates" value="1">
                                <label class="form-check-label" for="skipDuplicates">
                                    Skip duplicate products (by SKU)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="update_existing" id="updateExisting" value="1">
                                <label class="form-check-label" for="updateExisting">
                                    Update existing products with same SKU
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                     <div class="d-flex gap-2">
                         <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                             <i class="bi bi-upload me-1"></i> Start Import
                         </button>
                         <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">
                             <i class="bi bi-x-lg me-1"></i> Clear
                         </button>
                     </div>
                 </form>
             </div>
         </div>

         <!-- Import Progress -->
         <div id="importProgress" style="display: none;">
             <div class="card border-0 shadow-sm mb-4">
                 <div class="card-header bg-info text-white">
                     <h6 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>Import Progress</h6>
                 </div>
                 <div class="card-body">
                     <div class="mb-3">
                         <div class="progress" style="height: 25px;">
                             <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                         </div>
                         <p class="mb-0" id="progressText">Importing... <span id="progressPercent">0%</span></p>
                     </div>
                 </div>
             </div>
         </div>

         <!-- Import Results -->
        @if(session('success'))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-check-circle me-2"></i>Import Completed</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('import_errors'))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Import Warnings</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Instructions Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Instructions</h6>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2">Download the sample CSV template below</li>
                    <li class="mb-2">Fill in your product data following the format</li>
                    <li class="mb-2">Save the file as CSV, XLS, or XLSX</li>
                    <li class="mb-2">Upload the file using the form</li>
                    <li>Review the import results</li>
                </ol>
            </div>
        </div>

        <!-- Required Fields Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-asterisk me-2"></i>Required Fields</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><span class="badge bg-danger me-2">Required</span> <code>name</code> - Product name</li>
                    <li class="mb-2"><span class="badge bg-secondary me-2">Optional</span> <code>sku</code> - Product SKU</li>
                    <li class="mb-2"><span class="badge bg-secondary me-2">Optional</span> <code>price</code> - Regular price</li>
                    <li class="mb-2"><span class="badge bg-secondary me-2">Optional</span> <code>sale_price</code> - Sale price</li>
                    <li class="mb-2"><span class="badge bg-secondary me-2">Optional</span> <code>quantity</code> - Stock quantity</li>
                    <li class="mb-2"><span class="badge bg-secondary me-2">Optional</span> <code>category</code> - Category name</li>
                </ul>
            </div>
        </div>

        <!-- Sample Template Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-download me-2"></i>Sample Template</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Download the sample CSV template to get started:</p>
                <button class="btn btn-outline-primary w-100" onclick="downloadSampleTemplate()">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download CSV Template
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="importForm" class="btn btn-primary floating-save-btn" id="floatingSubmitBtn" disabled>
        <i class="bi bi-upload me-1"></i> Start Import
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
    .upload-area:hover {
        border-color: #667eea !important;
        background-color: rgba(102, 126, 234, 0.05);
    }
    .upload-area.dragover {
        border-color: #667eea !important;
        background-color: rgba(102, 126, 234, 0.1);
    }
    .border-dashed {
        border-style: dashed !important;
    }
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('importFile');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const submitBtn = document.getElementById('submitBtn');
    const floatingSubmitBtn = document.getElementById('floatingSubmitBtn');
    const progressBar = document.getElementById('progressBar');

    // Click to upload
    uploadArea.addEventListener('click', () => fileInput.click());

    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });

    // Form submission with progress tracking
    document.getElementById('importForm').addEventListener('submit', function(e) {
        // Show progress bar immediately
        updateProgress('0%', 'Starting import...');
        
        // Disable submit button to prevent multiple submissions
        submitBtn.disabled = true;
        floatingSubmitBtn.disabled = true;
        
        // In a real implementation, you would use AJAX to get progress updates
        // For now, we'll simulate progress updates
        const progressInterval = setInterval(() => {
            // This is a simulation - in reality, you'd get actual progress from the server
            const currentWidth = parseInt(progressBar.style.width) || 0;
            if (currentWidth >= 100) {
                clearInterval(progressInterval);
                updateProgress('100%', 'Import completed!');
            } else {
                const newWidth = Math.min(currentWidth + 10, 90); // Cap at 90% until completion
                updateProgress(newWidth + '%', 'Processing...');
            }
        }, 500);
        
        // Re-enable button after a delay (in case of quick failure)
        setTimeout(() => {
            if (!submitBtn.disabled) { // Only re-enable if not already disabled by server response
                submitBtn.disabled = false;
                floatingSubmitBtn.disabled = false;
            }
        }, 5000);
    });

    function handleFileSelect(file) {
        const allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        const extension = file.name.split('.').pop().toLowerCase();
        
        if (!['csv', 'xls', 'xlsx', 'txt'].includes(extension)) {
            alert('Please upload a CSV or Excel file.');
            clearFile();
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB.');
            clearFile();
            return;
        }

        fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        fileInfo.style.display = 'block';
        uploadArea.style.display = 'none';
        submitBtn.disabled = false;
        floatingSubmitBtn.disabled = false;
    }

    function clearFile() {
        fileInput.value = '';
        fileInfo.style.display = 'none';
        uploadArea.style.display = 'block';
        submitBtn.disabled = true;
        floatingSubmitBtn.disabled = true;
    }

    function clearForm() {
        clearFile();
        document.getElementById('skipDuplicates').checked = false;
        document.getElementById('updateExisting').checked = false;
        document.querySelector('select[name="default_category"]').value = '';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function downloadSampleTemplate() {
         const headers = [
             'name', 'sku', 'product_code', 'barcode', 'category', 'brand',
             'short_description', 'long_description', 'price', 'sale_price',
             'cost_price', 'purchase_price', 'quantity', 'low_stock_threshold',
             'weight', 'status', 'featured', 'meta_title', 'meta_description',
             'meta_keywords', 'tags'
         ];
         
         const sampleData = [
             'Sample Product 1', 'SKU-001', 'PC-001', '1234567890123', 'Electronics', 'Brand A',
             'Short description here', 'Long description goes here', '100.00', '79.99',
             '50.00', '45.00', '100', '10',
             '0.5', 'active', 'yes', 'Product Title', 'Meta description',
             'keyword1, keyword2', 'tag1, tag2'
         ];
         
         let csvContent = headers.join(',') + '\n' + sampleData.map(v => `"${v}"`).join(',');
         
         const blob = new Blob([csvContent], { type: 'text/csv' });
         const url = window.URL.createObjectURL(blob);
         const a = document.createElement('a');
         a.href = url;
         a.download = 'product-import-template.csv';
         document.body.appendChild(a);
         a.click();
         document.body.removeChild(a);
         window.URL.revokeObjectURL(url);
     }
     
     // Function to update progress bar
     function updateProgress(percent, text) {
         const progressText = document.getElementById('progressText');
         const progressPercent = document.getElementById('progressPercent');
         
         if (progressBar) {
             progressBar.style.width = percent + '%';
             progressBar.setAttribute('aria-valuenow', percent);
             progressBar.textContent = text;
         }
         
         if (progressText) {
             progressText.textContent = 'Importing... ' + text;
         }
         
         if (progressPercent) {
             progressPercent.textContent = text;
         }
         
         // Show progress bar
         const importProgress = document.getElementById('importProgress');
         if (importProgress) {
             importProgress.style.display = 'block';
         }
     }

     // Auto-scroll to first validation error
     document.addEventListener('DOMContentLoaded', function() {
         @if($errors->any())
             var firstErrorField = document.querySelector('.is-invalid, .invalid-feedback.d-block');
             if (firstErrorField) {
                 setTimeout(function() {
                     firstErrorField.scrollIntoView({ 
                         behavior: 'smooth', 
                         block: 'center' 
                     });
                 }, 100);
             }
         @endif
     });
 </script>
@endpush