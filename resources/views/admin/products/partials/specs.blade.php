<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Product Specifications</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Add custom specifications like Brand, Material, Capacity, Voltage, etc.</p>
        <div id="specsContainer">
            @php $specs = old('specs', $product->specs ?? []); @endphp
            @if(count($specs) > 0)
                @foreach($specs as $spec)
                <div class="row mb-2 spec-row">
                    <div class="col-md-5">
                        <input type="text" name="specs[{{ $loop->index }}][key]" class="form-control form-control-sm" placeholder="Label (e.g. Brand)" value="{{ $spec['key'] ?? '' }}">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="specs[{{ $loop->index }}][value]" class="form-control form-control-sm" placeholder="Value (e.g. Samsung)" value="{{ $spec['value'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-spec" onclick="this.closest('.spec-row').remove()"><i class="bi bi-x"></i></button>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addSpec()"><i class="bi bi-plus me-1"></i>Add Specification</button>
    </div>
</div>

<script>
function addSpec() {
    const container = document.getElementById('specsContainer');
    const index = container.querySelectorAll('.spec-row').length;
    const row = document.createElement('div');
    row.className = 'row mb-2 spec-row';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="specs[${index}][key]" class="form-control form-control-sm" placeholder="Label (e.g. Brand)">
        </div>
        <div class="col-md-5">
            <input type="text" name="specs[${index}][value]" class="form-control form-control-sm" placeholder="Value (e.g. Samsung)">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger btn-sm remove-spec" onclick="this.closest('.spec-row').remove()"><i class="bi bi-x"></i></button>
        </div>
    `;
    container.appendChild(row);
}
</script>