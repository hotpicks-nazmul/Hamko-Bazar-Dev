{{-- One image, every format the disk actually has: AVIF first, WebP/PNG fallback, and the plain
     <img> when neither derivative set exists, so an untouched image renders exactly as before. --}}
@php
    $avif = $avif ?? '';
    $srcset = $srcset ?? '';
    $sizes = $sizes ?? null;
    $class = $class ?? null;
    $loading = $loading ?? null;
@endphp
@if($avif)
    <picture>
        <source type="image/avif" srcset="{{ $avif }}" @if($sizes) sizes="{{ $sizes }}" @endif>
        <img src="{{ $src }}"
             @if($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
             alt="{{ $alt }}" @if($class) class="{{ $class }}" @endif @if($loading) loading="{{ $loading }}" @endif>
    </picture>
@else
    <img src="{{ $src }}"
         @if($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
         alt="{{ $alt }}" @if($class) class="{{ $class }}" @endif @if($loading) loading="{{ $loading }}" @endif>
@endif
