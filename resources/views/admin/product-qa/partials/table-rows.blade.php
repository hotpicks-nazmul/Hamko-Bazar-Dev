@forelse($qaEntries as $qa)
<tr data-id="{{ $qa->id }}">
    <td>
        <input type="checkbox" class="form-check-input item-checkbox" value="{{ $qa->id }}">
    </td>
    <td>
        <div class="product-info">
            @if($qa->product && $qa->product->thumbnail)
                <img src="{{ asset('storage/' . $qa->product->thumbnail) }}" alt="{{ $qa->product->name }}">
            @else
                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 4px;">
                    <i class="bi bi-box text-muted"></i>
                </div>
            @endif
            <div>
                <div class="fw-medium">{{ Str::limit($qa->product?->name, 30) }}</div>
                <small class="text-muted">ID: {{ $qa->product_id }}</small>
            </div>
        </div>
    </td>
    <td>
        <div class="qa-question" title="{{ $qa->question }}">
            {{ $qa->question }}
        </div>
        @if($qa->is_featured)
            <i class="bi bi-star-fill featured-icon" title="Featured"></i>
        @endif
    </td>
    <td>
        @if($qa->answer)
            <div class="qa-answer" title="{{ $qa->answer }}">
                {{ $qa->answer }}
            </div>
            <small class="text-muted">by {{ $qa->answerer?->name }} {{ $qa->answered_at?->diffForHumans() }}</small>
        @else
            <span class="text-muted fst-italic">Not answered yet</span>
        @endif
    </td>
    <td>
        @if($qa->status === 'pending')
            <span class="status-badge status-pending">Pending</span>
        @elseif($qa->status === 'answered')
            <span class="status-badge status-answered">Answered</span>
        @else
            <span class="status-badge status-published">Published</span>
        @endif
    </td>
    <td>
        {{ $qa->questioner_name ?? ($qa->user?->name ?? 'Guest') }}
        @if($qa->is_anonymous)
            <span class="badge bg-secondary ms-1">Anonymous</span>
        @endif
    </td>
    <td>
        <div>{{ $qa->created_at->format('M d, Y') }}</div>
        <small class="text-muted">{{ $qa->created_at->format('H:i') }}</small>
    </td>
    <td>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.product-qa.show', $qa->id) }}" class="btn btn-outline-primary" title="View & Answer">
                <i class="bi bi-eye"></i>
            </a>
            <button type="button" class="btn btn-outline-warning toggle-featured" data-id="{{ $qa->id }}" title="{{ $qa->is_featured ? 'Unfeature' : 'Feature' }}">
                <i class="bi {{ $qa->is_featured ? 'bi-star-fill' : 'bi-star' }}"></i>
            </button>
            <form action="{{ route('admin.product-qa.destroy', $qa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Q&A?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-question-circle display-4 text-muted"></i>
        <p class="text-muted mt-2">No Q&A entries found</p>
    </td>
</tr>
@endforelse
