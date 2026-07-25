@extends('admin.layouts.app')

@section('title', 'Answer Question')

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
    
    .question-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
    }
    
    .question-card .question-text {
        font-size: 1.25rem;
        line-height: 1.6;
    }
    
    .question-meta {
        opacity: 0.9;
        font-size: 0.875rem;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid rgba(255,255,255,0.2);
    }
    
    .answer-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 24px;
    }
    
    .product-card {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .product-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    
    .related-qa-item {
        padding: 12px;
        border-radius: 8px;
        background: #f8f9fa;
        margin-bottom: 8px;
    }
    
    .related-qa-item:hover {
        background: #e9ecef;
    }
    
    .status-badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
    }
    
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .status-answered {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .status-published {
        background-color: #d1fae5;
        color: #065f46;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.product-qa.index') }}">Product Q&A</a></li>
            <li class="breadcrumb-item active">Question #{{ $product_qa->id }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Question Card -->
            <div class="question-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="mb-0"><i class="bi bi-question-circle me-2"></i>Question</h5>
                    @if($product_qa->is_featured)
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-star-fill me-1"></i> Featured
                        </span>
                    @endif
                </div>
                <div class="question-text">
                    {{ $product_qa->question }}
                </div>
                <div class="question-meta">
                    <div class="row">
                        <div class="col-md-6">
                            <i class="bi bi-person me-2"></i>
                            Asked by: <strong>{{ $product_qa->questioner_name ?? ($product_qa->user?->name ?? 'Guest') }}</strong>
                            @if($product_qa->is_anonymous)
                                <span class="badge bg-light text-dark ms-1">Anonymous</span>
                            @endif
                        </div>
                        <div class="col-md-6 text-md-end">
                            <i class="bi bi-calendar me-2"></i>
                            {{ $product_qa->created_at->format('F d, Y \a\t H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Answer Section -->
            <div class="answer-section">
                <h5 class="mb-3">
                    <i class="bi bi-chat-left-text me-2"></i>
                    {{ $product_qa->answer ? 'Edit Answer' : 'Provide Answer' }}
                </h5>
                
                <form id="answerForm" method="POST" action="{{ route('admin.product-qa.update', $product_qa->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Your Answer <span class="text-danger">*</span></label>
                        <textarea name="answer" class="form-control" rows="5" required placeholder="Type your answer here...">{{ old('answer', $product_qa->answer) }}</textarea>
                        <small class="text-muted">Provide a clear and helpful answer to the customer's question.</small>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $product_qa->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="answered" {{ $product_qa->status === 'answered' ? 'selected' : '' }}>Answered</option>
                                <option value="published" {{ $product_qa->status === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            <small class="text-muted">
                                <strong>Pending:</strong> Not answered yet<br>
                                <strong>Answered:</strong> Answer saved, not visible publicly<br>
                                <strong>Published:</strong> Visible on product page
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Options</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" {{ $product_qa->is_featured ? 'checked' : '' }}>
                                <label class="form-check-label" for="isFeatured">
                                    <i class="bi bi-star text-warning"></i> Feature this Q&A
                                </label>
                                <small class="text-muted d-block">Featured Q&A will be highlighted on the product page</small>
                            </div>
                        </div>
                    </div>
                    
                    @if($product_qa->answer && $product_qa->answered_by)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Previously answered by <strong>{{ $product_qa->answerer?->name }}</strong> on {{ $product_qa->answered_at->format('F d, Y \a\t H:i') }}
                        </div>
                    @endif
                </form>
            </div>

            <!-- Helpful Stats -->
            @if($product_qa->helpful_count > 0 || $product_qa->not_helpful_count > 0)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-hand-thumbs-up me-2"></i>Helpfulness</h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 text-success mb-0">{{ $product_qa->helpful_count }}</div>
                            <small class="text-muted">Helpful</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 text-danger mb-0">{{ $product_qa->not_helpful_count }}</div>
                            <small class="text-muted">Not Helpful</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 text-primary mb-0">{{ $product_qa->helpful_percentage }}%</div>
                            <small class="text-muted">Positive Rate</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Product Info -->
            <div class="card border-0 shadow-sm mb-3 product-card">
                @if($product_qa->product)
                    @php
                        $imageUrl = $product_qa->product->thumbnail;
                        if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                            $imageUrl = '/storage/' . $imageUrl;
                        }
                    @endphp
                    @if($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $product_qa->product->name }}">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="bi bi-box display-4 text-muted"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <h6 class="card-title">{{ $product_qa->product->name }}</h6>
                        <p class="text-muted mb-2">
                            <small>SKU: {{ $product_qa->product->sku ?? 'N/A' }}</small>
                        </p>
                        <p class="mb-2">
                            <strong>{{ config('app.currency', '$') }}{{ number_format($product_qa->product->price, 2) }}</strong>
                        </p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.products.edit', $product_qa->product->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i> Edit Product
                            </a>
                            <a href="{{ route('products.show', $product_qa->product->slug) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-box-arrow-up-right me-1"></i> View on Store
                            </a>
                        </div>
                    </div>
                @else
                    <div class="card-body text-center py-5">
                        <i class="bi bi-box display-4 text-muted"></i>
                        <p class="text-muted mt-2">Product not found</p>
                    </div>
                @endif
            </div>

            <!-- Current Status -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Current Status</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Status:</span>
                        @if($product_qa->status === 'pending')
                            <span class="status-badge status-pending">Pending</span>
                        @elseif($product_qa->status === 'answered')
                            <span class="status-badge status-answered">Answered</span>
                        @else
                            <span class="status-badge status-published">Published</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Featured:</span>
                        <span>{{ $product_qa->is_featured ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </div>

            <!-- Related Q&A -->
            @if($relatedQA->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Related Questions</h6>
                </div>
                <div class="card-body p-2">
                    @foreach($relatedQA as $related)
                        <a href="{{ route('admin.product-qa.show', $related->id) }}" class="text-decoration-none">
                            <div class="related-qa-item">
                                <div class="small text-muted mb-1">
                                    @if($related->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($related->status === 'answered')
                                        <span class="badge bg-info">Answered</span>
                                    @else
                                        <span class="badge bg-success">Published</span>
                                    @endif
                                </div>
                                <div class="text-dark">
                                    {{ Str::limit($related->question, 60) }}
                                </div>
                                <small class="text-muted">{{ $related->created_at->diffForHumans() }}</small>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.product-qa.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    @if($product_qa->exists)
    <form action="{{ route('admin.product-qa.destroy', $product_qa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Q&A?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger floating-reset-btn">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
    </form>
    @endif
    <button type="submit" form="answerForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> {{ $product_qa->answer ? 'Update Answer' : 'Submit Answer' }}
    </button>
</div>
@endsection

@push('scripts')
<script>
    // Auto-resize textarea
    const textarea = document.querySelector('textarea[name="answer"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(150, this.scrollHeight) + 'px';
        });
    }
</script>
@endpush
