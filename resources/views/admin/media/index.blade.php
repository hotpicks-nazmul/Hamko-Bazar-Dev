@extends('admin.layouts.app')

@section('title', 'Media Library')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-images me-2"></i>Media Library</h4>
</div>

<!-- Filter and Search Bar -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.media.index') }}" class="row g-2 align-items-end" id="filterForm">
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label class="form-label small text-muted">Filter by Type</label>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>All Files</option>
                    <option value="images" {{ request('type') == 'images' ? 'selected' : '' }}>Images</option>
                    <option value="videos" {{ request('type') == 'videos' ? 'selected' : '' }}>Videos</option>
                    <option value="documents" {{ request('type') == 'documents' ? 'selected' : '' }}>Documents</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <label class="form-label small text-muted">Search Files</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by filename..." value="{{ request('search') }}">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request('search') || request('type') != 'all')
                    <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <label class="form-label small text-muted">Per Page</label>
                <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 per page</option>
                    <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50 per page</option>
                    <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100 per page</option>
                </select>
            </div>
            <div class="col-lg-5 col-md-6 col-sm-6 d-flex align-items-end">
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Showing {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} of {{ $paginator->total() }} files
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
    <div class="card-body py-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted"><span id="selectedCount">0</span> selected</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll()">
                    Select All
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                    Clear Selection
                </button>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()">
                    <i class="bi bi-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Area -->
<div class="upload-area mb-3" id="uploadArea">
    <form id="uploadForm" enctype="multipart/form-data">
        @csrf
        <input type="file" name="files[]" id="fileInput" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" style="display: none;">
        <div class="d-flex align-items-center justify-content-center gap-3">
            <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-cloud-upload me-2"></i> Select Files
            </button>
            <span class="text-muted">or drag and drop files here</span>
        </div>
        <div class="mt-2 text-muted small">
            Max file size: 10MB. Supported: Images, Videos, Audio, Documents
        </div>
    </form>
    <div id="uploadProgress" class="mt-3" style="display: none;">
        <div class="progress" style="height: 8px;">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
        <small class="text-muted mt-1 d-block" id="uploadProgressText">Uploading...</small>
    </div>
</div>

<!-- Media Grid -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($paginator->count() > 0)
        <div class="media-grid p-3" id="mediaGrid">
            @foreach($paginator as $file)
            <div class="media-item" data-path="{{ $file['path'] }}" onclick="viewFile('{{ $file['path'] }}')">
                <input type="checkbox" class="form-check-input media-checkbox" 
                       value="{{ $file['path'] }}"
                       onclick="event.stopPropagation(); toggleSelection(this);">
                
                <div class="actions">
                    <button class="btn btn-sm btn-outline-primary action-btn-view" title="View" onclick="event.stopPropagation(); viewFile('{{ $file['path'] }}')">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info action-btn-copy" title="Copy URL" onclick="event.stopPropagation(); copyUrl('{{ $file['url'] }}')">
                        <i class="bi bi-clipboard"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger action-btn-delete" title="Delete" onclick="event.stopPropagation(); deleteFile('{{ $file['path'] }}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                
                <div class="preview">
                    @if($file['is_image'])
                    <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}">
                    @else
                    <div class="file-icon">
                        <i class="bi bi-file-earmark"></i>
                    </div>
                    @endif
                </div>
                
                <div class="info">
                    <div class="name" title="{{ $file['name'] }}">{{ $file['name'] }}</div>
                    <div class="size">{{ $file['size'] > 1024 * 1024 ? number_format($file['size'] / 1024 / 1024, 2) . ' MB' : number_format($file['size'] / 1024, 2) . ' KB' }}</div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        @if($paginator->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <div class="text-muted small">
                    Showing {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} of {{ $paginator->total() }} files
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        @if ($paginator->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">‹</span>
                        </li>
                        @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" rel="prev">‹</a>
                        </li>
                        @endif

                        @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                            @if ($i >= $paginator->currentPage() - 2 && $i <= $paginator->currentPage() + 2)
                                @if ($i == $paginator->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                                @else
                                <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}&{{ http_build_query(request()->except('page')) }}">{{ $i }}</a></li>
                                @endif
                            @endif
                        @endfor

                        @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" rel="next">›</a>
                        </li>
                        @else
                        <li class="page-item disabled">
                            <span class="page-link">›</span>
                        </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="bi bi-images text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3">No Media Files</h5>
            <p class="text-muted">Upload your first file using the form above.</p>
        </div>
        @endif
    </div>
</div>

<!-- File Details Modal -->
<div class="modal fade" id="fileDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark me-2"></i>File Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7">
                        <div id="imagePreviewContainer">
                            <img src="" alt="" class="img-fluid rounded" id="modalPreview" style="max-height: 350px; width: 100%; object-fit: contain; background: #f8f9fa;">
                        </div>
                        <div id="filePreviewContainer" class="text-center py-5" style="display: none;">
                            <i class="bi bi-file-earmark-text" style="font-size: 5rem; color: #6c757d;"></i>
                            <p class="mt-2 text-muted" id="modalFileType"></p>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Filename</label>
                            <div class="fw-medium" id="modalName"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">File Size</label>
                            <div id="modalSize"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">File Type</label>
                            <div><span class="badge bg-secondary" id="modalType"></span></div>
                        </div>
                        <div class="mb-3" id="dimensionsContainer" style="display: none;">
                            <label class="form-label text-muted small mb-1">Dimensions</label>
                            <div id="modalDimensions"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">URL</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="modalUrl" readonly>
                                <button class="btn btn-outline-primary" onclick="copyUrlFromModal()">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-outline-danger" onclick="deleteFileFromModal()">
                                <i class="bi bi-trash me-2"></i>Delete File
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this file? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="successMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Message Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="errorMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }
    
    .media-item {
        position: relative;
        border: 2px solid transparent;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
        cursor: pointer;
        background: #fff;
    }
    
    .media-item:hover {
        border-color: #0d6efd;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .media-item.selected {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    
    .media-item .media-checkbox {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 10;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .media-item:hover .media-checkbox,
    .media-item.selected .media-checkbox {
        opacity: 1;
    }
    
    .media-item .preview {
        height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        overflow: hidden;
    }
    
    .media-item .preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .media-item .preview .file-icon {
        font-size: 3rem;
        color: #6c757d;
    }
    
    .media-item .info {
        padding: 10px;
        border-top: 1px solid #eee;
    }
    
    .media-item .info .name {
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .media-item .info .size {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .media-item .actions {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: 4px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .media-item:hover .actions {
        opacity: 1;
    }
    
    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        transition: all 0.2s ease;
    }
    
    .upload-area:hover,
    .upload-area.dragover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    
    #fileDetailsModal .fw-medium {
        word-wrap: break-word;
        word-break: break-all;
        overflow-wrap: break-word;
        max-width: 100%;
    }
    
    #fileDetailsModal .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    #fileDetailsModal .modal-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
    
    #fileDetailsModal .modal-body {
        padding: 1.5rem;
    }
    
    #fileDetailsModal #modalPreview {
        border-radius: 8px;
        background-color: #f8f9fa;
        max-height: 400px;
        object-fit: contain;
    }
</style>
@endpush

@push('scripts')
<script>
let currentDeletePath = '';
let selectedFiles = new Set();

// Drag and drop
const uploadArea = document.getElementById('uploadArea');

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        uploadFiles(files);
    }
});

document.getElementById('fileInput').addEventListener('change', function(e) {
    if (this.files.length > 0) {
        uploadFiles(this.files);
    }
});

// Upload files with real progress
function uploadFiles(files) {
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    
    const progressDiv = document.getElementById('uploadProgress');
    const progressBar = progressDiv.querySelector('.progress-bar');
    const progressText = document.getElementById('uploadProgressText');
    
    progressDiv.style.display = 'block';
    progressBar.style.width = '0%';
    progressText.textContent = 'Uploading 0%';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route("admin.media.upload") }}', true);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressText.textContent = 'Uploading ' + percent + '%';
        }
    });
    
    xhr.onload = function() {
        progressDiv.style.display = 'none';
        
        try {
            const data = JSON.parse(xhr.responseText);
            if (data.success) {
                showSuccessModal(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorModal(data.message || 'Upload failed');
            }
        } catch (e) {
            showErrorModal('Upload failed');
        }
    };
    
    xhr.onerror = function() {
        progressDiv.style.display = 'none';
        showErrorModal('Upload failed: Network error');
    };
    
    xhr.send(formData);
}

// Show success modal
function showSuccessModal(message) {
    document.getElementById('successMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
}

// Show error modal
function showErrorModal(message) {
    document.getElementById('errorMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('errorModal'));
    modal.show();
}

// View file details
function viewFile(path) {
    fetch('{{ route("admin.media.show") }}?path=' + encodeURIComponent(path))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const file = data.file;
                
                document.getElementById('modalName').textContent = file.name;
                document.getElementById('modalSize').textContent = file.size_formatted;
                document.getElementById('modalType').textContent = file.extension.toUpperCase();
                document.getElementById('modalUrl').value = file.url;
                
                const dimensionsContainer = document.getElementById('dimensionsContainer');
                if (file.dimensions) {
                    dimensionsContainer.style.display = 'block';
                    document.getElementById('modalDimensions').textContent = file.dimensions;
                } else {
                    dimensionsContainer.style.display = 'none';
                }
                
                const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                const isImage = imageExtensions.includes(file.extension.toLowerCase());
                
                const imageContainer = document.getElementById('imagePreviewContainer');
                const fileContainer = document.getElementById('filePreviewContainer');
                
                if (isImage) {
                    imageContainer.style.display = 'block';
                    fileContainer.style.display = 'none';
                    document.getElementById('modalPreview').src = file.url;
                } else {
                    imageContainer.style.display = 'none';
                    fileContainer.style.display = 'block';
                    document.getElementById('modalFileType').textContent = file.extension.toUpperCase() + ' File';
                }
                
                currentDeletePath = file.path;
                
                const modal = new bootstrap.Modal(document.getElementById('fileDetailsModal'));
                modal.show();
            }
        });
}

// Copy URL
function copyUrl(url) {
    navigator.clipboard.writeText(window.location.origin + url).then(() => {
        showSuccessModal('URL copied to clipboard');
    }).catch(() => {
        showErrorModal('Failed to copy URL');
    });
}

function copyUrlFromModal() {
    const url = document.getElementById('modalUrl').value;
    copyUrl(url);
}

// Delete file
function deleteFile(path) {
    currentDeletePath = path;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function deleteFileFromModal() {
    bootstrap.Modal.getInstance(document.getElementById('fileDetailsModal')).hide();
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (currentDeletePath) {
        fetch('{{ route("admin.media.destroy") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ path: currentDeletePath })
        })
        .then(response => response.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            
            if (data.success) {
                showSuccessModal(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorModal(data.message || 'Delete failed');
            }
        })
        .catch(() => {
            showErrorModal('Delete failed: Network error');
        });
    }
});

// Bulk selection functionality
function toggleSelection(checkbox) {
    const mediaItem = checkbox.closest('.media-item');
    const path = checkbox.value;
    
    if (checkbox.checked) {
        selectedFiles.add(path);
        mediaItem.classList.add('selected');
    } else {
        selectedFiles.delete(path);
        mediaItem.classList.remove('selected');
    }
    
    updateBulkActions();
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.media-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        selectedFiles.add(checkbox.value);
        checkbox.closest('.media-item').classList.add('selected');
    });
    updateBulkActions();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.media-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        checkbox.closest('.media-item').classList.remove('selected');
    });
    selectedFiles.clear();
    updateBulkActions();
}

function updateBulkActions() {
    const count = selectedFiles.size;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActionsBar').style.display = count > 0 ? 'block' : 'none';
}

function bulkDelete() {
    if (selectedFiles.size === 0) {
        showErrorModal('Please select at least one file to delete.');
        return;
    }
    
    if (!confirm(`Are you sure you want to delete ${selectedFiles.size} file(s)? This action cannot be undone.`)) {
        return;
    }
    
    const paths = Array.from(selectedFiles);
    let deletePromises = paths.map(path => {
        return fetch('{{ route("admin.media.destroy") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ path: path })
        }).then(response => response.json());
    });
    
    Promise.all(deletePromises)
        .then(results => {
            const successCount = results.filter(r => r.success).length;
            const errorCount = results.length - successCount;
            
            if (errorCount === 0) {
                showSuccessModal(`${successCount} file(s) deleted successfully.`);
            } else {
                showSuccessModal(`${successCount} file(s) deleted. ${errorCount} failed.`);
            }
            
            clearSelection();
            setTimeout(() => location.reload(), 1500);
        })
        .catch(() => {
            showErrorModal('Bulk delete failed: Network error');
        });
}
</script>
@endpush
