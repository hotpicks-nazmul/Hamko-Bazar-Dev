@foreach($items as $item)
<a href="{{ route('products.index', ['category' => $item->slug]) }}" 
   class="group flex flex-col items-center p-3 rounded-xl border border-gray-100 hover:border-halal-green/30 hover:bg-halal-green/5 transition-all duration-300">
    @if($item->image)
        <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-lg object-cover mb-1.5 group-hover:scale-110 transition-transform">
    @else
        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center mb-1.5 group-hover:bg-halal-green/10 transition-colors">
            <i class="{{ $item->icon ?? 'bi bi-folder-fill' }} text-lg text-gray-400 group-hover:text-halal-green transition-colors"></i>
        </div>
    @endif
    <span class="font-medium text-gray-700 text-xs text-center group-hover:text-halal-green transition-colors leading-tight">{{ $item->name }}</span>
    <span class="text-xs text-gray-400 mt-0.5">{{ $item->total_products_count ?? 0 }}</span>
</a>
@endforeach