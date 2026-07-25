<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Refund;
use App\Models\Order;
use App\Models\ActivityLog;
use App\Exceptions\RefundException;
use App\Services\RefundService;

class OrderController extends Controller
{
    /**
     * Display user's orders.
     */
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('themes.general.orders.index', compact('orders'));
    }

    /**
     * Display a single order.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product');

        return view('themes.general.orders.show', $this->orderPageData($order));
    }

    /**
     * Ask for a refund on a delivered order.
     *
     * The refund module had no creation path at all: no store route here, no admin create, nothing
     * calling Refund::create, so the table stayed empty and the configuration screen's settings were
     * read by nobody. Eligibility, the window and the amount all come from RefundService — the
     * request supplies only a reason and free text.
     */
    public function requestRefund(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:50',
            'details' => 'nullable|string|max:1000',
        ]);

        try {
            $refund = app(RefundService::class)->request(
                $order,
                auth()->id(),
                $validated['reason'],
                $validated['details'] ?? null
            );
        } catch (RefundException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::customerLog('Refund requested', $order, auth()->user(), [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'refund_number' => $refund->refund_number,
            'amount' => (float) $refund->refund_amount,
            'status' => $refund->status,
        ]);

        $message = $refund->status === 'approved'
            ? 'Your refund request was approved and will be paid out shortly.'
            : 'Your refund request has been submitted. We will review it and get back to you.';

        return back()->with('success', $message);
    }

    /** Everything the order page needs: the order's refunds and whether it can be refunded now. */
    protected function orderPageData(Order $order): array
    {
        return [
            'order' => $order,
            'refunds' => Refund::where('order_id', $order->id)->latest('id')->get(),
            'refundCheck' => app(RefundService::class)->canRequest($order),
            'refundReasons' => Refund::getReasonOptions(),
        ];
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'This order cannot be cancelled.');
        }

        $oldStatus = $order->status;
        $order->update(['status' => 'cancelled']);

        // Restore product stock — but only if this order actually took it (COD at placement,
        // gateway on payment). Returning stock for an unpaid gateway order would invent it.
        if (app(\App\Services\StockService::class)->orderHoldsStock($order)) {
            app(\App\Services\StockService::class)->restoreOrder(
                $order,
                'Order ' . $order->order_number . ' cancelled by customer'
            );
        }

        // Log order cancellation
        ActivityLog::customerLog(
            'Order cancelled',
            $order,
            auth()->user(),
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => 'cancelled'
            ]
        );

        return back()->with('success', 'Order cancelled successfully.');
    }

    /**
     * Track order status.
     */
    public function track(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // themes/general/orders/track.blade.php never existed, so this route answered 500.
        // The order page already carries the courier + tracking number, so point there.
        $order->load('items.product');

        return view('themes.general.orders.show', $this->orderPageData($order));
    }
}
