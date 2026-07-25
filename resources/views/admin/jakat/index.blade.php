@extends('admin.layouts.app')

@section('title', 'Islamic Jakat Calculator')

@section('content')

@php
    $inputs = $inputs ?? [];
    $selectedNisabType = $selectedNisabType ?? 'gold';
@endphp

<!-- Session Flash Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="mb-4">
    <h4 class="mb-0"><i class="bi bi-calculator me-2"></i>Islamic Jakat Calculator</h4>
    <p class="text-muted mb-0">Calculate your Zakat based on Islamic principles</p>
</div>

<!-- Nisab Information -->
<div class="alert alert-info border-0 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h5 class="text-white mb-2"><i class="bi bi-info-circle me-2"></i>Nisab Thresholds (Minimum Wealth for Zakat)</h5>
            <div class="text-white-50">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-gem me-2"></i>
                    <span>Gold: {{ number_format($nisabInfo['gold']['grams'], 2) }} grams (৳{{ number_format($nisabInfo['gold']['value'], 2) }})</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-circle me-2"></i>
                    <span>Silver: {{ number_format($nisabInfo['silver']['grams'], 2) }} grams (৳{{ number_format($nisabInfo['silver']['value'], 2) }})</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="text-white">
                <p class="mb-1 small">Zakat Rate: <strong>2.5%</strong></p>
                <p class="mb-0 small">Current Gold: ৳{{ number_format($goldPrice, 0) }}/gram</p>
                <p class="mb-0 small">Current Silver: ৳{{ number_format($silverPrice, 0) }}/gram</p>
            </div>
        </div>
    </div>
</div>

<!-- Market Prices Update Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Market Prices</h6>
                <small class="text-muted">Update gold and silver prices for accurate calculations</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#priceUpdateForm">
                <i class="bi bi-pencil-square me-1"></i> Update Prices
            </button>
        </div>
        <div class="collapse mt-3" id="priceUpdateForm">
            <form action="{{ route('admin.jakat.prices') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label small">Gold Price (per gram)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">৳</span>
                        <input type="number" class="form-control" name="gold_price" value="{{ $goldPrice }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Silver Price (per gram)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">৳</span>
                        <input type="number" class="form-control" name="silver_price" value="{{ $silverPrice }}" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-check-lg me-1"></i> Save Prices
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#priceUpdateForm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <!-- Calculator Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Enter Your Assets</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.jakat.calculate') }}" method="POST">
                    @csrf
                    
                    <!-- Market Prices -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Gold Price (per gram)</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="gold_price" value="{{ old('gold_price', $goldPrice) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Silver Price (per gram)</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="silver_price" value="{{ old('silver_price', $silverPrice) }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <!-- Nisab Type -->
                    <div class="mb-4">
                        <label class="form-label">Nisab Calculation Basis</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="nisab_type" id="nisabGold" value="gold" {{ (old('nisab_type', $selectedNisabType) === 'gold') ? 'checked' : '' }}>
                                <label class="form-check-label" for="nisabGold">
                                    Gold (87.48g)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="nisab_type" id="nisabSilver" value="silver" {{ old('nisab_type', $selectedNisabType) === 'silver' ? 'checked' : '' }}>
                                <label class="form-check-label" for="nisabSilver">
                                    Silver (612.36g)
                                </label>
                            </div>
                        </div>
                        <small class="text-muted">Most scholars recommend using Gold Nisab for calculation</small>
                    </div>

                    <hr class="my-4">

                    <!-- Gold Assets -->
                    <h6 class="mb-3"><i class="bi bi-gem me-2 text-warning"></i>Gold Assets (grams)</h6>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">24K Gold</label>
                            <input type="number" class="form-control" name="gold_24k" value="{{ old('gold_24k', ($inputs['gold_24k'] ?? '')) }}" min="0" step="0.001" placeholder="Grams">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">22K Gold</label>
                            <input type="number" class="form-control" name="gold_22k" value="{{ old('gold_22k', ($inputs['gold_22k'] ?? '')) }}" min="0" step="0.001" placeholder="Grams">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">21K Gold</label>
                            <input type="number" class="form-control" name="gold_21k" value="{{ old('gold_21k', ($inputs['gold_21k'] ?? '')) }}" min="0" step="0.001" placeholder="Grams">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">18K Gold</label>
                            <input type="number" class="form-control" name="gold_18k" value="{{ old('gold_18k', ($inputs['gold_18k'] ?? '')) }}" min="0" step="0.001" placeholder="Grams">
                        </div>
                    </div>

                    <!-- Silver Assets -->
                    <h6 class="mb-3"><i class="bi bi-circle me-2 text-secondary"></i>Silver Assets</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Silver (grams)</label>
                            <input type="number" class="form-control" name="silver" value="{{ old('silver', ($inputs['silver'] ?? '')) }}" min="0" step="0.001" placeholder="Grams">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Financial Assets -->
                    <h6 class="mb-3"><i class="bi bi-bank me-2 text-primary"></i>Financial Assets (৳)</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Cash on Hand</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="cash" value="{{ old('cash', ($inputs['cash'] ?? '')) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Balance</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="bank_balance" value="{{ old('bank_balance', ($inputs['bank_balance'] ?? '')) }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Business Assets</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="business_assets" value="{{ old('business_assets', ($inputs['business_assets'] ?? '')) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Investments</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="investments" value="{{ old('investments', ($inputs['investments'] ?? '')) }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Stocks</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="stocks" value="{{ old('stocks', ($inputs['stocks'] ?? '')) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cryptocurrency</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="crypto" value="{{ old('crypto', ($inputs['crypto'] ?? '')) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Other Assets</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="other_assets" value="{{ old('other_assets', ($inputs['other_assets'] ?? '')) }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-calculator me-2"></i>Calculate Zakat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Results Panel -->
    <div class="col-lg-4">
        @if(isset($result))
        <div class="card shadow-sm mb-4 {{ $result['is_liable'] ? 'border border-success' : 'border border-warning' }}">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Calculation Result</h5>
            </div>
            <div class="card-body">
                <!-- Zakat Amount -->
                <div class="text-center mb-4 p-4 rounded {{ $result['is_liable'] ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10' }}">
                    @if($result['is_liable'])
                        <h6 class="text-success mb-1">Zakat Payable</h6>
                        <h2 class="text-success mb-0">৳{{ number_format($result['zakat_amount'], 2) }}</h2>
                        <small class="text-success">{{ $result['zakat_rate'] * 100 }}% of total wealth</small>
                    @else
                        <h6 class="text-warning mb-1">Below Nisab</h6>
                        <h4 class="text-warning mb-0">No Zakat Due</h4>
                        <small class="text-muted">You need ৳{{ number_format($result['remaining_for_nisab'], 2) }} more to be liable</small>
                    @endif
                </div>

                <!-- Total Wealth -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Wealth</span>
                        <strong>৳{{ number_format($result['total_wealth'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Nisab Threshold</span>
                        <strong>৳{{ number_format($result['nisab']['threshold_value'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Status</span>
                        @if($result['is_liable'])
                            <span class="badge bg-success">Liable for Zakat</span>
                        @else
                            <span class="badge bg-warning">Not Liable</span>
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Asset Breakdown -->
                <h6 class="mb-3">Asset Breakdown</h6>
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-gem text-warning me-1"></i>Gold Value</span>
                        <span>৳{{ number_format($result['assets']['gold']['value'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-circle text-secondary me-1"></i>Silver Value</span>
                        <span>৳{{ number_format($result['assets']['silver']['value'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-cash text-success me-1"></i>Cash</span>
                        <span>৳{{ number_format($result['assets']['cash'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-bank text-primary me-1"></i>Bank Balance</span>
                        <span>৳{{ number_format($result['assets']['bank_balance'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-shop text-info me-1"></i>Business Assets</span>
                        <span>৳{{ number_format($result['assets']['business_assets'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-graph-up-arrow text-dark me-1"></i>Investments</span>
                        <span>৳{{ number_format($result['assets']['investments'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-pie-chart text-danger me-1"></i>Stocks</span>
                        <span>৳{{ number_format($result['assets']['stocks'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="bi bi-currency-bitcoin text-warning me-1"></i>Crypto</span>
                        <span>৳{{ number_format($result['assets']['crypto'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="bi bi-box text-muted me-1"></i>Other Assets</span>
                        <span>৳{{ number_format($result['assets']['other_assets'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Instructions Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-book me-2"></i>How to Calculate</h5>
            </div>
            <div class="card-body">
                <ol class="ps-3">
                    <li class="mb-2">Enter current market prices for gold and silver</li>
                    <li class="mb-2">Select Nisab basis (Gold recommended)</li>
                    <li class="mb-2">Enter your gold and silver holdings by weight</li>
                    <li class="mb-2">Enter all your financial assets in BDT</li>
                    <li class="mb-0">Click Calculate to see your Zakat amount</li>
                </ol>
                <div class="alert alert-warning mt-3 mb-0 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Note:</strong> This calculator provides an estimate. Please consult with an Islamic scholar for precise calculations.
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Add padding at bottom to prevent content overlap */
    .content-area {
        padding-bottom: 20px;
    }
</style>
@endpush
