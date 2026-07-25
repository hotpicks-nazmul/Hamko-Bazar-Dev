@php
$qaEntries = \App\Models\ProductQA::where('product_id', $product->id)
    ->where('status', 'published')
    ->with(['user', 'answerer'])
    ->orderBy('is_featured', 'desc')
    ->orderBy('helpful_count', 'desc')
    ->orderBy('created_at', 'desc')
    ->paginate(5);
@endphp

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-bold text-gray-800">Questions & Answers</h3>
        <p class="text-sm text-gray-500">{{ $qaEntries->total() }} questions</p>
    </div>
    <button onclick="openQuestionModal()" class="bg-halal-green text-white px-4 py-2 rounded-lg hover:text-amber-400 transition-colors text-sm font-medium flex items-center gap-2"><i class="bi bi-question-circle"></i>Ask Question</button>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
    <span class="flex items-center gap-2"><i class="bi bi-check-circle text-lg"></i>{{ session('success') }}</span>
    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900"><i class="bi bi-x-lg"></i></button>
</div>
@endif

@if($qaEntries->count() > 0)
<div class="space-y-4">
    @foreach($qaEntries as $qa)
    <div class="qa-card border rounded-xl p-5 {{ $qa->is_featured ? 'border-halal-green bg-green-50' : 'border-gray-200' }}">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 bg-halal-green rounded-full flex items-center justify-center text-white font-bold flex-shrink-0"><i class="bi bi-question-lg"></i></div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-semibold text-gray-800 text-sm">{{ $qa->questioner_name }}</span>
                    @if($qa->is_featured)<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-halal-green text-white"><i class="bi bi-star-fill"></i>Featured</span>@endif
                    <span class="text-gray-400 text-xs ml-auto">{{ $qa->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-gray-700 mt-1">{{ $qa->question }}</p>
            </div>
        </div>
        @if($qa->answer)
        <div class="flex items-start gap-3 mt-4 ml-4 pl-4 border-l-4 border-halal-green">
            <div class="w-9 h-9 bg-halal-dark rounded-full flex items-center justify-center text-white font-bold flex-shrink-0"><i class="bi bi-check-lg"></i></div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-semibold text-gray-800 text-sm">{{ $qa->answerer?->name ?? 'Store Admin' }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Answered</span>
                    <span class="text-gray-400 text-xs ml-auto">{{ $qa->answered_at?->diffForHumans() }}</span>
                </div>
                <p class="text-gray-600 text-sm mt-1">{{ $qa->answer }}</p>
                <div class="flex items-center gap-3 mt-3 text-sm">
                    <span class="text-gray-500">Helpful?</span>
                    <button type="button" class="qa-vote-btn inline-flex items-center gap-1 px-3 py-1 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors" data-qa-id="{{ $qa->id }}" data-is-helpful="1"><i class="bi bi-hand-thumbs-up"></i><span>{{ $qa->helpful_count }}</span></button>
                    <button type="button" class="qa-vote-btn inline-flex items-center gap-1 px-3 py-1 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors" data-qa-id="{{ $qa->id }}" data-is-helpful="0"><i class="bi bi-hand-thumbs-down"></i><span>{{ $qa->not_helpful_count }}</span></button>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>
@if($qaEntries->hasPages())
<div class="mt-6">{{ $qaEntries->links() }}</div>
@endif
@else
<div class="text-center py-12 text-gray-500 bg-gray-50 rounded-xl">
    <i class="bi bi-chat-dots text-5xl text-gray-300"></i>
    <p class="mt-3">No questions yet. Be the first to ask!</p>
</div>
@endif