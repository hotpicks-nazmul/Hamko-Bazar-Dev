@extends('admin.layouts.app')

@section('title', 'Pay Seller — ' . ($seller->shop_name ?? $seller->name))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Record a Payout</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Seller
        </a>
        <a href="{{ route('admin.sellers.payouts') }}" class="btn btn-outline-secondary">
            <i class="bi bi-cash-stack me-1"></i> All Payouts
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3">{{ $seller->shop_name ?? $seller->name }}</h6>
                <dl class="row mb-0 small">
                    <dt class="col-7 text-muted fw-normal">Seller</dt>
                    <dd class="col-5 text-end">{{ $seller->name }}</dd>

                    <dt class="col-7 text-muted fw-normal">Email</dt>
                    <dd class="col-5 text-end text-truncate">{{ $seller->email }}</dd>

                    <dt class="col-7 text-muted fw-normal">Commission</dt>
                    <dd class="col-5 text-end">{{ number_format((float) $seller->commission_rate, 2) }}%</dd>

                    <dt class="col-7 text-muted fw-normal">Pending balance</dt>
                    <dd class="col-5 text-end fw-semibold">৳{{ number_format((float) $seller->pending_balance, 2) }}</dd>

                    <dt class="col-7 text-muted fw-normal">Wallet balance</dt>
                    <dd class="col-5 text-end fw-semibold">৳{{ number_format((float) $seller->wallet_balance, 2) }}</dd>
                </dl>
                <p class="text-muted small mb-0 mt-3">
                    A payout is debited from the one balance you choose below, and only that balance is
                    checked. Commission is withheld from the amount and recorded on the payout.
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.sellers.payouts.store', $seller->id) }}">
            @csrf
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="1" name="amount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}" required>
                            </div>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pay from <span class="text-danger">*</span></label>
                            <select name="from_wallet" class="form-select">
                                <option value="0" {{ old('from_wallet', '0') === '0' ? 'selected' : '' }}>
                                    Pending balance — ৳{{ number_format((float) $seller->pending_balance, 2) }}
                                </option>
                                <option value="1" {{ old('from_wallet') === '1' ? 'selected' : '' }}>
                                    Wallet balance — ৳{{ number_format((float) $seller->wallet_balance, 2) }}
                                </option>
                            </select>
                            <div class="form-text">Debits exactly one balance; the amount cannot exceed it.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                @foreach(['bank_transfer' => 'Bank transfer', 'cash' => 'Cash', 'mobile_banking' => 'Mobile banking', 'cheque' => 'Cheque', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Transaction / reference ID</label>
                            <input type="text" name="transaction_id" maxlength="100" class="form-control"
                                   value="{{ old('transaction_id') }}" placeholder="bKash TrxID, cheque no, bank ref…">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Bank name</label>
                            <input type="text" name="bank_name" maxlength="255" class="form-control" value="{{ old('bank_name', $seller->bank_name) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Account number</label>
                            <input type="text" name="account_number" maxlength="50" class="form-control" value="{{ old('account_number', $seller->bank_account_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Account name</label>
                            <input type="text" name="account_name" maxlength="255" class="form-control" value="{{ old('account_name', $seller->bank_account_name) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Anything worth recording on this payout">{{ old('notes') }}</textarea>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary"
                                    onclick="return confirm('Record this payout and debit the seller balance?')">
                                <i class="bi bi-check-lg me-1"></i> Record Payout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
