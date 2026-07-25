@php
    $backgroundColor = $widget->settings['background_color'] ?? '#f8f9fa';
    $textColor = $widget->settings['text_color'] ?? '#212529';
    $buttonColor = $widget->settings['button_color'] ?? '#0d6efd';
    $buttonTextColor = $widget->settings['button_text_color'] ?? '#ffffff';
@endphp

<section class="widget-newsletter py-5" style="background-color: {{ $backgroundColor }}; color: {{ $textColor }};">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                @if($widget->title)
                <h3 class="mb-2">{{ $widget->title }}</h3>
                @endif
                
                @if($widget->subtitle)
                <p class="mb-4">{{ $widget->subtitle }}</p>
                @else
                <p class="mb-4">Subscribe to our newsletter for exclusive deals and updates</p>
                @endif
                
                @if($widget->content)
                <p class="mb-4">{{ $widget->content }}</p>
                @endif
                
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                    @csrf
                    <div class="input-group">
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="Enter your email address" 
                               required
                               style="border-color: {{ $buttonColor }};">
                        <button type="submit" 
                                class="btn text-white" 
                                style="background-color: {{ $buttonColor }};">
                            <i class="bi bi-send me-1"></i> Subscribe
                        </button>
                    </div>
                    <div class="form-text mt-2">
                        We respect your privacy. Unsubscribe at any time.
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
