@php
    $slides = $settings['slides'] ?? [];
@endphp

@if(count($slides) > 0)
<section class="widget-slider py-4">
    <div class="container">
        <div id="widgetSlider{{ $widget->id }}" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($slides as $index => $slide)
                <button type="button" data-bs-target="#widgetSlider{{ $widget->id }}" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner rounded">
                @foreach($slides as $index => $slide)
                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                    <a href="{{ $slide['link'] ?? '#' }}">
                        <img src="{{ $slide['image'] }}" class="d-block w-100" alt="{{ $slide['title'] ?? 'Slide' }}" style="max-height: {{ $settings['height'] ?? '400px' }}; object-fit: cover;">
                    </a>
                    @if($slide['title'] || $slide['description'])
                    <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5);">
                        @if($slide['title'])
                        <h5>{{ $slide['title'] }}</h5>
                        @endif
                        @if($slide['description'])
                        <p>{{ $slide['description'] }}</p>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#widgetSlider{{ $widget->id }}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#widgetSlider{{ $widget->id }}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</section>
@else
{{-- No slides placeholder --}}
<section class="widget-slider py-4">
    <div class="container">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            No slides configured. Add slides in widget settings.
        </div>
    </div>
</section>
@endif
