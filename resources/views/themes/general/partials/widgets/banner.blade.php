@php
    $bannerImage = $settings['image'] ?? null;
    $bannerLink = $settings['link'] ?? '#';
    $bannerAlt = $settings['alt'] ?? $widget->title ?? 'Banner';
    $backgroundColor = $settings['background_color'] ?? '#f8f9fa';
    $textColor = $settings['text_color'] ?? '#212529';
@endphp

@if($bannerImage)
<section class="widget-banner py-3">
    <div class="container">
        <a href="{{ $bannerLink }}" class="d-block">
            <div class="banner-wrapper rounded overflow-hidden" style="background-color: {{ $backgroundColor }};">
                <img src="{{ $bannerImage }}" alt="{{ $bannerAlt }}" class="img-fluid w-100" style="max-height: {{ $settings['height'] ?? '250px' }}; object-fit: cover;">
            </div>
        </a>
    </div>
</section>
@else
{{-- Fallback for when no banner image is set --}}
<section class="widget-banner py-3">
    <div class="container">
        <div class="banner-placeholder rounded" style="background-color: {{ $backgroundColor }}; min-height: 150px;">
            <div class="d-flex align-items-center justify-content-center h-100 text-center p-4" style="color: {{ $textColor }};">
                <div>
                    @if($widget->title)
                    <h4>{{ $widget->title }}</h4>
                    @endif
                    @if($widget->subtitle)
                    <p class="mb-0">{{ $widget->subtitle }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif
