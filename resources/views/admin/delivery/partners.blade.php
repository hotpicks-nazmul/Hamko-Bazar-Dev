@extends('admin.layouts.app')

@section('title', 'Delivery Partners')

@section('content')
<div class="content-area">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-building me-2"></i>Delivery Partners</h4>
            <p class="text-muted mb-0">Manage your delivery partner companies</p>
        </div>
        <button class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Partner
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="text-center py-5">
                <i class="bi bi-building text-primary" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Delivery Partners Management</h5>
                <p class="text-muted">This feature is under development. Here you will be able to:</p>
                <ul class="text-start d-inline-block">
                    <li>Add and manage delivery partner companies</li>
                    <li>Configure partnership terms and rates</li>
                    <li>Track partner performance metrics</li>
                    <li>Manage contracts and agreements</li>
                </ul>
                <div class="mt-4">
                    <span class="badge bg-primary">Coming Soon</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
