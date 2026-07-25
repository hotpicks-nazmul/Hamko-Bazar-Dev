@php
use App\Models\Currency;
use App\Models\Setting;

// Check if frontend currency switcher is enabled
$frontendCurrencySwitcher = Setting::get('frontend_currency_switcher', 0);

// Get active currencies
$currencies = \Cache::remember('active_currencies', 3600, function() { return \App\Models\Currency::active()->get(); });
$defaultCurrency = \Cache::remember('default_currency', 3600, function() { return \App\Models\Currency::getDefault(); });

// Get current currency from session or use default
$currentCurrencyCode = session('currency_code', $defaultCurrency?->code ?? 'USD');
$currentCurrency = Currency::where('code', $currentCurrencyCode)->first() ?? $defaultCurrency;
@endphp

@if($frontendCurrencySwitcher && $currencies->count() > 1)
<div class="relative inline-block">
    <button type="button" class="flex items-center space-x-1 text-sm hover:text-halal-gold transition-colors" id="currencyDropdown" data-dropdown-toggle="currency-menu" aria-expanded="false" aria-haspopup="true">
        <i class="bi bi-currency-exchange"></i>
        <span>{{ $currentCurrency?->code ?? 'USD' }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    
    <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-200" id="currency-menu">
        @foreach($currencies as $currency)
        <a href="{{ route('currency.switch', $currency->code) }}" 
           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $currency->code === $currentCurrencyCode ? 'bg-gray-50 font-medium' : '' }}">
            <span class="mr-2">{{ $currency->symbol }}</span>
            <span>{{ $currency->name }} ({{ $currency->code }})</span>
            @if($currency->is_default)
            <span class="ml-auto text-xs text-gray-500">Default</span>
            @endif
        </a>
        @endforeach
    </div>
</div>

<script>
    // Toggle currency dropdown
    const currencyDropdown = document.getElementById('currencyDropdown');
    const currencyMenu = document.getElementById('currency-menu');
    
    if (currencyDropdown && currencyMenu) {
        currencyDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            currencyMenu.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!currencyDropdown.contains(e.target) && !currencyMenu.contains(e.target)) {
                currencyMenu.classList.add('hidden');
            }
        });
    }
</script>
@endif
