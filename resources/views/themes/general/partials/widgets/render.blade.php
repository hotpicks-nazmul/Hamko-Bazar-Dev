{{-- 
    Widget Renderer - Use this in any page to render widgets
    
    Usage in controller:
    $widgets = \App\Models\Widget::active()->ordered()->get();
    
    Usage in view:
    @include('themes.general.partials.widgets.render', ['widgets' => $widgets])
    
    Or use WidgetHelper:
    @php
        $widgets = \App\Services\WidgetHelper::getActiveWidgets();
    @endphp
    @foreach($widgets as $widget)
        {!! \App\Services\WidgetHelper::renderWidget($widget) !!}
    @endforeach
--}}

@if(isset($widgets) && $widgets->count() > 0)
    @foreach($widgets as $widget)
        {!! \App\Services\WidgetHelper::renderWidget($widget) !!}
    @endforeach
@endif
