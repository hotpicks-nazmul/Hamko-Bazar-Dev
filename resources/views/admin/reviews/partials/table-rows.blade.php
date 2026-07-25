@forelse($reviews as $review)
<tr>
    <td>
        <input class="form-check-input review-checkbox" type="checkbox" value="{{ $review->id }}">
    </td>
    <td>
        <div class="d-flex align-items-center">
            @if($review->product)
                @php
                    $imageUrl = $review->product->image;
                    if($imageUrl && !str_starts_with($imageUrl, '/storage/') && !str_starts_with($imageUrl, 'http')) {
                        $imageUrl = '/storage/' . $imageUrl;
                    }
                @endphp
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $review->product->name }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                @else
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <i class="bi bi-image text-white"></i>
                    </div>
                @endif
                <div>
                    <a href="{{ route('products.show', $review->product->slug) }}" target="_blank" class="text-decoration-none">
                        <span class="fw-medium">{{ Str::limit($review->product->name, 30) }}</span>
                    </a>
                </div>
            @else
                <span class="text-muted">Product deleted</span>
            @endif
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                <i class="bi bi-person text-primary"></i>
            </div>
            <div>
                <span class="fw-medium">{{ $review->user->name ?? 'Guest' }}</span>
                <br>
                <small class="text-muted">{{ $review->user->email ?? '' }}</small>
            </div>
        </div>
    </td>
    <td>
        <div class="d-flex flex-column">
            <div class="text-warning">
                @for($i = 1; $i <= 5; $i++)
                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                @endfor
            </div>
            <small class="text-muted">{{ $review->rating }}/5</small>
        </div>
    </td>
    <td>
        @if($review->title)
            <strong>{{ $review->title }}</strong><br>
        @endif
        <span class="text-muted">{{ Str::limit($review->comment, 80) }}</span>
        @if(strlen($review->comment) > 80)
            <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $review->id }}">
                Read more
            </button>
        @endif
    </td>
    <td>
        @if($review->status === 'approved')
            <span class="badge bg-success-subtle text-success">
                <i class="bi bi-check-circle me-1"></i>Approved
            </span>
        @elseif($review->status === 'rejected')
            <span class="badge bg-danger-subtle text-danger">
                <i class="bi bi-x-circle me-1"></i>Rejected
            </span>
        @else
            <span class="badge bg-warning-subtle text-warning">
                <i class="bi bi-clock me-1"></i>Pending
            </span>
        @endif
    </td>
    <td>
        <small>{{ $review->created_at->format('d M, Y') }}</small>
        <br>
        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
    </td>
    <td>
        <div class="btn-group" role="group">
            @if($review->status !== 'approved')
                <button type="button" class="btn btn-sm btn-outline-success" title="Approve" onclick="approveReview({{ $review->id }})">
                    <i class="bi bi-check-lg"></i>
                </button>
            @endif
            <button type="button" class="btn btn-sm btn-outline-primary" title="View" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $review->id }}">
                <i class="bi bi-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteReview({{ $review->id }})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </td>
</tr>

<!-- Review Detail Modal -->
<div class="modal fade" id="reviewModal{{ $review->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="bi bi-person text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $review->user->name ?? 'Guest' }}</h6>
                        <small class="text-muted">{{ $review->created_at->format('F d, Y \a\t h:i A') }}</small>
                    </div>
                    <div class="ms-auto text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                        @endfor
                    </div>
                </div>
                @if($review->product)
                    <div class="bg-light p-3 rounded mb-3">
                        <small class="text-muted">Product:</small>
                        <p class="mb-0 fw-medium">{{ $review->product->name }}</p>
                    </div>
                @endif
                @if($review->title)
                    <h6 class="fw-bold">{{ $review->title }}</h6>
                @endif
                <p class="text-muted mb-0">{{ $review->comment }}</p>
            </div>
            <div class="modal-footer">
                @if($review->status !== 'approved')
                    <button type="button" class="btn btn-success" onclick="approveReview({{ $review->id }})">
                        <i class="bi bi-check-lg me-1"></i> Approve
                    </button>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-chat-square-text text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-2 mb-0">No reviews found.</p>
    </td>
</tr>
@endforelse
