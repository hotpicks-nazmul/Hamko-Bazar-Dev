@foreach($shipments as $order)

<!-- Tracking Modal -->
<div class="modal fade" id="trackingModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt me-2"></i>Shipment Tracking
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Shipment Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Order Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted">Order Number:</td>
                                <td><strong>{{ $order->order_number }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tracking Number:</td>
                                <td><strong>{{ $order->tracking_number ?? 'Not Generated' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Carrier:</td>
                                <td>{{ $order->shipping_company ?? 'Not Assigned' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Order Date:</td>
                                <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Delivery Address</h6>
                        <p class="mb-0">
                            <strong>{{ $order->shipping_full_name }}</strong><br>
                            {{ $order->shipping_address }}<br>
                            {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postcode }}<br>
                            {{ $order->shipping_country }}<br>
                            <i class="bi bi-phone me-1"></i> {{ $order->shipping_phone }}
                        </p>
                    </div>
                </div>

                <!-- Status Update Form -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body">
                        <h6 class="mb-3">Update Shipment Status</h6>
                        <form id="statusForm{{ $order->id }}" onsubmit="updateStatus(event, {{ $order->id }})">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="">Select Status</option>
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="tracking_number" class="form-control form-control-sm" 
                                        placeholder="Tracking Number" value="{{ $order->tracking_number }}">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="notes" class="form-control form-control-sm" 
                                        placeholder="Notes" value="{{ $order->notes }}">
                                </div>
                            </div>
                            <div class="mt-2 text-end">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tracking Timeline -->
                <h6 class="text-muted mb-3">Tracking Timeline</h6>
                <div class="tracking-timeline">
                    <div class="tracking-item {{ in_array($order->status, ['pending', 'processing', 'confirmed', 'shipped', 'delivered']) ? 'completed' : '' }}">
                        <div class="fw-semibold">Order Placed</div>
                        <small class="text-muted">{{ $order->created_at->format('M d, Y - h:i A') }}</small>
                    </div>
                    <div class="tracking-item {{ in_array($order->status, ['processing', 'confirmed', 'shipped', 'delivered']) ? 'completed' : '' }}">
                        <div class="fw-semibold">Payment Confirmed</div>
                        <small class="text-muted">
                            @if($order->payment_status == 'paid')
                                {{ $order->created_at->addMinutes(5)->format('M d, Y - h:i A') }}
                            @else
                                Awaiting payment
                            @endif
                        </small>
                    </div>
                    <div class="tracking-item {{ in_array($order->status, ['confirmed', 'shipped', 'delivered']) ? 'completed' : '' }}">
                        <div class="fw-semibold">Order Confirmed</div>
                        <small class="text-muted">
                            @if(in_array($order->status, ['confirmed', 'shipped', 'delivered']))
                                {{ $order->created_at->addHours(2)->format('M d, Y - h:i A') }}
                            @else
                                Pending confirmation
                            @endif
                        </small>
                    </div>
                    <div class="tracking-item {{ $order->status == 'shipped' ? 'current' : '' }} {{ $order->status == 'delivered' ? 'completed' : '' }}">
                        <div class="fw-semibold">Shipped</div>
                        <small class="text-muted">
                            @if($order->status == 'shipped' || $order->status == 'delivered')
                                In Transit
                            @else
                                Not shipped yet
                            @endif
                        </small>
                    </div>
                    <div class="tracking-item {{ $order->status == 'delivered' ? 'completed' : '' }}">
                        <div class="fw-semibold">Delivered</div>
                        <small class="text-muted">
                            @if($order->status == 'delivered')
                                {{ $order->updated_at->format('M d, Y - h:i A') }}
                            @else
                                Expected delivery
                            @endif
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-eye me-1"></i>View Full Order
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach