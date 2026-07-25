@php
    $testimonials = $settings['testimonials'] ?? [];
    $backgroundColor = $settings['background_color'] ?? '#f8f9fa';
@endphp

<section class="widget-testimonials py-5" style="background-color: {{ $backgroundColor }};">
    <div class="container">
        @if($widget->title || $widget->subtitle)
        <div class="widget-header mb-4 text-center">
            @if($widget->title)
            <h3 class="widget-title">{{ $widget->title }}</h3>
            @endif
            @if($widget->subtitle)
            <p class="widget-subtitle text-muted">{{ $widget->subtitle }}</p>
            @endif
        </div>
        @endif
        
        @if(count($testimonials) > 0)
        <div class="row g-4">
            @foreach($testimonials as $testimonial)
            <div class="col-md-6 col-lg-4">
                <div class="testimonial-card h-100 bg-white p-4 rounded shadow-sm">
                    <div class="testimonial-rating mb-3">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star-fill text-warning"></i>
                        @endfor
                    </div>
                    <blockquote class="testimonial-text mb-3">
                        "{{ $testimonial['message'] ?? '' }}"
                    </blockquote>
                    <div class="testimonial-author d-flex align-items-center">
                        @if(isset($testimonial['avatar']))
                        <img src="{{ $testimonial['avatar'] }}" alt="{{ $testimonial['name'] ?? 'User' }}" class="rounded-circle me-3" width="50" height="50">
                        @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            {{ strtoupper(substr($testimonial['name'] ?? 'U', 0, 1)) }}
                        </div>
                        @endif
                        <div>
                            <strong>{{ $testimonial['name'] ?? 'Anonymous' }}</strong>
                            @if(isset($testimonial['role']))
                            <div class="text-muted small">{{ $testimonial['role'] }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>
            No testimonials configured. Add testimonials in widget settings.
        </div>
        @endif
    </div>
</section>
