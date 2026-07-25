<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-size: 14px;
            line-height: 1.5;
        }
        .quotation-header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .quotation-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }
        .quotation-number {
            font-size: 16px;
            color: #666;
        }
        .section-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            margin-bottom: 10px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .total-section {
            border-top: 2px solid #333;
            margin-top: 30px;
            padding-top: 20px;
        }
        .terms-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <!-- Print Button -->
        <div class="text-end mb-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Quotation
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="bi bi-x-lg"></i> Close
            </button>
        </div>

        <!-- Header -->
        <div class="quotation-header">
            <div class="row">
                <div class="col-8">
                    <div class="quotation-title">QUOTATION</div>
                    <div class="quotation-number">{{ $quotation->quotation_number }}</div>
                </div>
                <div class="col-4 text-end">
                    @php
                        $settings = \App\Models\Setting::first();
                        $logo = $settings->logo ?? null;
                    @endphp
                    @if($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="Logo" style="max-height: 60px;">
                    @else
                    <h4 class="mb-0">{{ config('app.name') }}</h4>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quotation Info -->
        <div class="row mb-4">
            <div class="col-6">
                <div class="section-title">Customer</div>
                <h5 class="mb-1">{{ $quotation->customer_name }}</h5>
                @if($quotation->customer_email)
                <p class="mb-1">{{ $quotation->customer_email }}</p>
                @endif
                @if($quotation->customer_phone)
                <p class="mb-1">{{ $quotation->customer_phone }}</p>
                @endif
                @if($quotation->customer_address)
                <p class="mb-0">{{ $quotation->customer_full_address }}</p>
                @endif
            </div>
            <div class="col-6 text-end">
                <div class="section-title">Quotation Details</div>
                <table class="table table-borderless table-sm text-end">
                    <tr>
                        <td><strong>Date:</strong></td>
                        <td style="width: 150px;">{{ $quotation->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Valid Until:</strong></td>
                        <td>{{ $quotation->valid_until->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><span class="badge bg-{{ $quotation->status_badge_class }}">{{ ucfirst($quotation->status) }}</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Description</th>
                    <th class="text-center" style="width: 80px;">Qty</th>
                    <th class="text-end" style="width: 120px;">Unit Price</th>
                    <th class="text-end" style="width: 120px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->description)
                        <br><small class="text-muted">{{ $item->description }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="row">
            <div class="col-6">
                @if($quotation->notes)
                <div class="mb-4">
                    <div class="section-title">Notes</div>
                    <p>{{ $quotation->notes }}</p>
                </div>
                @endif
            </div>
            <div class="col-6">
                <table class="table table-borderless table-sm text-end">
                    <tr>
                        <td>Subtotal:</td>
                        <td style="width: 150px;">{{ number_format($quotation->subtotal, 2) }}</td>
                    </tr>
                    @if($quotation->tax > 0)
                    <tr>
                        <td>Tax:</td>
                        <td>{{ number_format($quotation->tax, 2) }}</td>
                    </tr>
                    @endif
                    @if($quotation->discount > 0)
                    <tr>
                        <td>Discount:</td>
                        <td>{{ number_format($quotation->discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="border-top">
                        <td><strong class="fs-5">Total:</strong></td>
                        <td><strong class="fs-5">{{ number_format($quotation->total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Terms & Signature -->
        <div class="terms-section">
            <div class="row">
                <div class="col-6">
                    @if($quotation->terms_conditions)
                    <div class="mb-4">
                        <div class="section-title">Terms & Conditions</div>
                        <p class="mb-0">{{ $quotation->terms_conditions }}</p>
                    </div>
                    @endif
                </div>
                <div class="col-6">
                    <div class="section-title">Authorization</div>
                    <div style="border-top: 1px solid #333; margin-top: 60px; padding-top: 10px;">
                        Signature & Date
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-muted mt-5 pt-4" style="border-top: 1px solid #ddd;">
            <p class="mb-0">Thank you for your business!</p>
            <small>This quotation is valid until {{ $quotation->valid_until->format('M d, Y') }}</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-print on load
        window.addEventListener('load', function() {
            // Uncomment to auto-print: window.print();
        });
    </script>
</body>
</html>
