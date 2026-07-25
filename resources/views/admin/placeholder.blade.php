@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $title }}</h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                <i class="bi bi-{{ $icon }} fs-1 text-primary"></i>
            </div>
        </div>
        
        <h3 class="mb-3">{{ $title }}</h3>
        <p class="text-muted mb-4">{{ $description }}</p>
        
        <div class="alert alert-info d-inline-block">
            <i class="bi bi-info-circle me-2"></i>
            This feature is currently under development and will be available soon.
        </div>
        
        <div class="mt-5 pt-4 border-top">
            <h6 class="text-muted mb-3">Related Features</h6>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <span class="badge bg-light text-dark px-3 py-2">
                    <i class="bi bi-tools me-1"></i> Coming Soon
                </span>
                <span class="badge bg-light text-dark px-3 py-2">
                    <i class="bi bi-gear me-1"></i> In Development
                </span>
                <span class="badge bg-light text-dark px-3 py-2">
                    <i class="bi bi-rocket me-1"></i> Planned
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-lightbulb fs-2 text-warning mb-3"></i>
                <h6>Feature Preview</h6>
                <p class="text-muted small mb-0">This feature is being developed based on user feedback and industry best practices.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-clock-history fs-2 text-info mb-3"></i>
                <h6>Expected Release</h6>
                <p class="text-muted small mb-0">This feature is scheduled for an upcoming release. Check back for updates.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="bi bi-chat-dots fs-2 text-success mb-3"></i>
                <h6>Need This Feature?</h6>
                <p class="text-muted small mb-0">Contact the development team if you need this feature prioritized.</p>
            </div>
        </div>
    </div>
</div>
@endsection
