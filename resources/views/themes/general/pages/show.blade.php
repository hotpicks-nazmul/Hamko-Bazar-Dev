@extends('themes.general.layouts.app')

@section('title', $page->meta_title ?? $page->title)

@section('meta')
    <meta name="description" content="{{ $page->meta_description ?? Str::limit(strip_tags($page->content), 160) }}">
    <meta property="og:title" content="{{ $page->meta_title ?? $page->title }}">
    <meta property="og:description" content="{{ $page->meta_description ?? Str::limit(strip_tags($page->content), 160) }}">
    <meta property="og:type" content="article">
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-4">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="{{ route('home') }}" class="hover:text-primary">Home</a></li>
                <li><span class="text-gray-300">/</span></li>
                <li><span class="text-gray-700">{{ $page->title }}</span></li>
            </ol>
        </nav>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ $page->title }}</h1>
        
        @if($page->featured_image)
        <div class="mb-6">
            <img src="{{ asset('storage/' . $page->featured_image) }}" alt="{{ $page->title }}" class="w-full h-64 object-cover rounded-lg">
        </div>
        @endif
        
        <div class="prose prose-lg max-w-none">
            {{-- Content is expected to be HTML from admin editor. Ensure admin input is sanitized. --}}
            {!! class_exists('Purifier') ? Purifier::clean($page->content) : $page->content !!}
        </div>
        
        <!-- Back to Home Link -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('home') }}" class="text-primary hover:text-primary-dark font-medium">
                <i class="bi bi-arrow-left me-1"></i> Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
