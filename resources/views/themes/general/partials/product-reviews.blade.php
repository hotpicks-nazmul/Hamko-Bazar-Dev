<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-bold text-gray-800">Customer Reviews</h3>
        <p class="text-sm text-gray-500">{{ $product->approved_reviews_count }} reviews</p>
    </div>
    @auth
        @php
            $hp = auth()->user()->orders()->whereHas('items',fn($q)=>$q->where('product_id',$product->id))->where('status','delivered')->exists();
            $hr = \App\Models\Review::where('user_id',auth()->id())->where('product_id',$product->id)->exists();
        @endphp
        @if($hr)
            <span class="text-sm text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full flex items-center gap-1.5"><i class="bi bi-check-circle"></i>You reviewed this product</span>
        @elseif(!$hp)
            <button onclick="showPurchaseRequiredMessage()" class="bg-halal-green text-white px-4 py-2 rounded-lg hover:text-amber-400 transition-colors text-sm font-medium flex items-center gap-2"><i class="bi bi-pencil-square"></i>Write Review</button>
        @else
            <button onclick="openReviewModal()" class="bg-halal-green text-white px-4 py-2 rounded-lg hover:text-amber-400 transition-colors text-sm font-medium flex items-center gap-2"><i class="bi bi-pencil-square"></i>Write Review</button>
        @endif
    @else
        <button onclick="window.location.href='{{ route('login') }}'" class="bg-halal-green text-white px-4 py-2 rounded-lg hover:text-amber-400 transition-colors text-sm font-medium flex items-center gap-2"><i class="bi bi-pencil-square"></i>Write Review</button>
    @endauth
</div>

<!-- Rating Summary -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6 p-5 bg-gray-50 rounded-xl">
    <div class="text-center">
        <div class="text-4xl font-bold text-halal-green">{{ number_format($product->average_rating,1) }}</div>
        <div class="flex justify-center mt-1 text-amber-400">@for($i=1;$i<=5;$i++)<i class="bi bi-star{{ $product->average_rating>=$i?'-fill':($product->average_rating>=$i-0.5?'-half':'') }}"></i>@endfor</div>
        <p class="text-sm text-gray-500 mt-1">{{ $product->approved_reviews_count }} reviews</p>
    </div>
    <div class="sm:col-span-2">
        @php $dist = $product->rating_distribution; $total = $product->approved_reviews_count; @endphp
        @for($i=5;$i>=1;$i--)
            @php $pct = $total > 0 ? ($dist[$i] ?? 0) / $total * 100 : 0; @endphp
            <div class="flex items-center gap-2 text-sm mb-1.5">
                <span class="w-10 text-right text-gray-600">{{ $i }}<i class="bi bi-star-fill text-amber-400 ms-0.5 text-xs"></i></span>
                <div class="flex-1 h-2.5 bg-gray-200 rounded-full overflow-hidden"><div class="h-full bg-amber-400 rounded-full transition-all" style="width:{{ $pct }}%"></div></div>
                <span class="w-8 text-gray-500 text-xs">{{ $dist[$i] ?? 0 }}</span>
            </div>
        @endfor
    </div>
</div>

<!-- Reviews List -->
@if($reviews && $reviews->count() > 0)
    <div class="space-y-4">
        @foreach($reviews as $review)
        <div class="review-card border border-gray-200 rounded-xl p-5 hover:border-gray-300 transition-colors">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-halal-green to-halal-dark flex items-center justify-center text-white font-bold flex-shrink-0">{{ strtoupper(substr($review->user->name??'U',0,1)) }}</div>
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $review->user->name ?? 'Anonymous' }}</h4>
                        <div class="flex items-center gap-2 mt-0.5">
                            <div class="flex text-amber-400 text-sm">@for($i=1;$i<=5;$i++)<i class="bi bi-star{{ $i<=$review->rating?'-fill':'' }}"></i>@endfor</div>
                            <span class="text-gray-400 text-xs">{{ $review->created_at->diffForHumans() }}</span>
                            @if($review->verified_purchase)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"><i class="bi bi-patch-check-fill"></i>Verified</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($review->user_id === auth()->id())
                <form action="{{ route('reviews.destroy',$review->id) }}" method="POST" onsubmit="return confirm('Delete this review?')">@csrf @method('DELETE')<button class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition-colors"><i class="bi bi-trash"></i></button></form>
                @endif
            </div>
            @if($review->title)<h5 class="font-semibold text-gray-800 mt-3">{{ $review->title }}</h5>@endif
            <p class="text-gray-600 mt-1 text-sm leading-relaxed">{{ $review->comment }}</p>
            @if($review->images && count($review->images)>0)
            <div class="flex gap-2 mt-3 flex-wrap">
                @foreach($review->images as $img)
                <a href="{{ asset($img) }}" target="_blank"><img src="{{ asset($img) }}" class="w-16 h-16 object-cover rounded-lg border hover:opacity-80 transition-opacity"></a>
                @endforeach
            </div>
            @endif
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center gap-3 text-sm">
                <span class="text-gray-500">Helpful?</span>
                @auth
                <button type="button" class="vote-btn inline-flex items-center gap-1 px-3 py-1 rounded-full border transition-colors border-gray-300 hover:bg-gray-100" data-review-id="{{ $review->id }}" data-is-helpful="1"><i class="bi bi-hand-thumbs-up"></i><span class="helpful-count">{{ $review->helpful_count }}</span></button>
                <button type="button" class="vote-btn inline-flex items-center gap-1 px-3 py-1 rounded-full border transition-colors border-gray-300 hover:bg-gray-100" data-review-id="{{ $review->id }}" data-is-helpful="0"><i class="bi bi-hand-thumbs-down"></i><span class="not-helpful-count">{{ $review->not_helpful_count }}</span></button>
                @else
                <button class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-gray-200 text-gray-400 cursor-not-allowed text-sm" disabled><i class="bi bi-hand-thumbs-up"></i>{{ $review->helpful_count }}</button>
                <button class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-gray-200 text-gray-400 cursor-not-allowed text-sm" disabled><i class="bi bi-hand-thumbs-down"></i>{{ $review->not_helpful_count }}</button>
                @endauth
            </div>
        </div>
        @endforeach
    </div>
    @if($reviews->hasPages())
    <div class="mt-6">{{ $reviews->links() }}</div>
    @endif
@else
    <div class="text-center py-12 text-gray-500 bg-gray-50 rounded-xl">
        <i class="bi bi-chat-square-text text-5xl text-gray-300"></i>
        <p class="mt-3">No reviews yet. Be the first!</p>
    </div>
@endif

<script>
function showPurchaseRequiredMessage() {
    const popup = document.createElement('div');
    popup.id = 'purchasePopup';
    popup.innerHTML = '<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;" onclick="document.getElementById(\'purchasePopup\').remove();document.body.style.overflow=\'auto\'">'+
        '<div style="background:white;border-radius:16px;padding:30px;max-width:380px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center;" onclick="event.stopPropagation()">'+
        '<div style="font-size:60px;margin-bottom:16px;">🛒</div>'+
        '<h3 style="color:#dc2626;margin-bottom:8px;font-weight:700;">Purchase Required</h3>'+
        '<p style="color:#6b7280;margin-bottom:20px;font-size:14px;">You need to purchase this product before writing a review.</p>'+
        '<button onclick="document.getElementById(\'purchasePopup\').remove();document.body.style.overflow=\'auto\'" style="background:#2D5A27;color:white;border:none;padding:12px 24px;border-radius:8px;cursor:pointer;font-weight:600;">OK</button></div></div>';
    document.body.appendChild(popup);
    document.body.style.overflow = 'hidden';
}
</script>