<section class="widget-custom-html py-4">
    <div class="container">
        @if($widget->title)
        <div class="widget-header mb-3">
            <h3 class="widget-title">{{ $widget->title }}</h3>
            @if($widget->subtitle)
            <p class="widget-subtitle text-muted">{{ $widget->subtitle }}</p>
            @endif
        </div>
        @endif
        
        <div class="widget-content">
            {!! $content !!}
        </div>
    </div>
</section>
