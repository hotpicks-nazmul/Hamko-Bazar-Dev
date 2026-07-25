@php
use App\Models\Language;
use App\Models\Setting;

// Check if frontend language switcher is enabled
$frontendLanguageSwitcher = Setting::get('frontend_language_switcher', 1);

// Only show language switcher if enabled
if (!$frontendLanguageSwitcher) {
    return;
}

$languages = \Cache::remember('active_languages', 3600, function() { return \App\Models\Language::where('is_active', true)->orderBy('sort_order')->get(); });
$currentLocale = session('locale', 'en');
$currentLanguage = $languages->firstWhere('code', $currentLocale) ?? Language::getDefault();
$isRTL = $currentLanguage->is_rtl ?? false;
@endphp

@if($languages->count() > 0)
<div class="relative inline-block">
    <button type="button" class="flex items-center space-x-1 text-sm hover:text-halal-gold transition-colors" id="languageDropdown" data-dropdown-toggle="language-menu">
        <span>{{ $currentLanguage->flag ?? '🌐' }}</span>
        <span class="hidden sm:inline">{{ $currentLanguage->native_name ?? $currentLanguage->name ?? 'English' }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    
    <!-- Dropdown menu - direction aware -->
    <div class="hidden absolute {{ $isRTL ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-200" id="language-menu">
        @foreach($languages as $language)
        <a href="{{ route('language.switch', ['lang' => $language->code]) }}" 
           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $language->code === $currentLocale ? 'bg-gray-50 font-medium' : '' }}"
           aria-label="{{ $language->native_name ?? $language->name }}">
            <span class="mr-2">{{ $language->flag }}</span>
            <span>{{ $language->native_name ?? $language->name }}</span>
            @if($language->is_default)
            <span class="ml-auto text-xs text-gray-500">Default</span>
            @endif
        </a>
        @endforeach
    </div>
</div>

<script>
    // Toggle language dropdown
    const langDropdown = document.getElementById('languageDropdown');
    const langMenu = document.getElementById('language-menu');
    
    if (langDropdown && langMenu) {
        langDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            langMenu.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!langDropdown.contains(e.target) && !langMenu.contains(e.target)) {
                langMenu.classList.add('hidden');
            }
        });
    }
</script>
@endif
