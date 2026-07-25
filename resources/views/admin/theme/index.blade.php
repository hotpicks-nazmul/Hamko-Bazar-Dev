@extends('admin.layouts.app')

@section('title', 'Themes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-palette me-2"></i>Themes</h4>
    <a href="{{ route('admin.themes.settings') }}" class="btn btn-outline-secondary">
        <i class="bi bi-gear me-1"></i> Theme Settings
    </a>
</div>

<!-- Current Active Theme Info -->
@if($activeTheme)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                <i class="bi bi-palette text-primary" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h6 class="mb-0">Active Theme</h6>
                <p class="text-muted mb-0">{{ ucfirst($activeTheme) }}</p>
            </div>
        </div>
        <a href="{{ route('admin.themes.settings') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-sliders me-1"></i> Configure
        </a>
    </div>
</div>
@endif

<!-- Themes Grid -->
<div class="row">
    @forelse($themes as $key => $theme)
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm {{ $theme['active'] ? 'border-primary' : '' }}">
            @if(isset($theme['screenshot']) && $theme['screenshot'])
            <img src="{{ asset($theme['screenshot']) }}" class="card-img-top" alt="{{ $theme['name'] }}" style="height: 200px; object-fit: cover;">
            @else
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="bi bi-palette text-secondary" style="font-size: 4rem;"></i>
            </div>
            @endif
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0">{{ $theme['name'] ?? 'Unknown' }}</h5>
                    @if($theme['active'])
                    <span class="badge bg-primary">Active</span>
                    @endif
                </div>
                <p class="card-text text-muted small">{{ $theme['description'] ?? 'No description available.' }}</p>
                <div class="d-flex gap-3 text-muted small">
                    <span><i class="bi bi-tag me-1"></i> {{ $theme['category'] ?? 'general' }}</span>
                    <span><i class="bi bi-hash me-1"></i> v{{ $theme['version'] ?? '1.0.0' }}</span>
                </div>
            </div>
            <div class="card-footer bg-white border-top">
                @if($theme['active'])
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.themes.settings') }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                            <i class="bi bi-gear me-1"></i> Settings
                        </a>
                        <button class="btn btn-sm btn-success flex-grow-1" disabled>
                            <i class="bi bi-check-circle me-1"></i> Active
                        </button>
                    </div>
                @else
                    <form action="{{ route('admin.themes.activate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $key }}">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-check-lg me-1"></i> Activate Theme
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-palette text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No Themes Found</h5>
                <p class="text-muted">No themes are available in the themes directory.</p>
                <p class="text-muted small">Themes should be placed in <code>resources/views/themes/</code></p>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
