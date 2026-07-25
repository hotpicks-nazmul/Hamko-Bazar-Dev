@forelse($faqs as $faq)
<tr data-id="{{ $faq->id }}">
    <td>
        <input type="checkbox" class="form-check-input item-checkbox" value="{{ $faq->id }}">
    </td>
    <td>
        <div class="fw-medium">{{ $faq->question }}</div>
    </td>
    <td>
        <div class="text-muted text-truncate" style="max-width: 300px;">
            {{ Str::limit(strip_tags($faq->answer), 100) }}
        </div>
    </td>
    <td class="status-badge">
        {!! $faq->status_badge !!}
    </td>
    <td>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline{{ $faq->status === 'active' ? '-warning' : '-success' }}" 
                    title="{{ $faq->status === 'active' ? 'Deactivate' : 'Activate' }}"
                    onclick="toggleStatus('{{ route('admin.faqs.toggle-status', $faq->id) }}', {{ $faq->id }})">
                <i class="bi bi{{ $faq->status === 'active' ? '-x-circle' : '-check-circle' }}"></i>
            </button>
            <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                        onclick="return confirm('Are you sure you want to delete this FAQ?')">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center py-5">
        <i class="bi bi-question-diamond text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No FAQs found</p>
        <a href="{{ route('admin.faqs.create') }}" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add First FAQ
        </a>
    </td>
</tr>
@endforelse
