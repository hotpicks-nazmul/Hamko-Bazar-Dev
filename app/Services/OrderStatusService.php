<?php

namespace App\Services;

use App\Models\Order;

/**
 * The single place an order's status moves with its stock consequences.
 *
 * It began life inside Admin\OrderController::applyStatus() because the detail page, the row
 * dropdown and the bulk action each did their own thing and only the first of them moved stock.
 * Refunds need exactly the same rule ("return what the order was holding, and only once"), so the
 * body moved here and the admin controller delegates — a second copy in RefundController would be
 * the same bug with a different caller.
 */
class OrderStatusService
{
    /**
     * Move an order to $status (and optionally $paymentStatus), returning or taking stock as the
     * before/after pair requires.
     */
    public function apply(Order $order, string $status, ?string $paymentStatus = null): void
    {
        $previousStatus = $order->status;
        $previousPayment = $order->payment_status;

        $data = ['status' => $status];
        if ($paymentStatus !== null) {
            $data['payment_status'] = $paymentStatus;
        }

        $order->update($data);

        $stock = app(StockService::class);

        // "Holds stock" is COD-at-placement or paid, and not closed. Compare before and after so
        // every transition is covered: cancel/refund returns it, reopening takes it back, and
        // marking a gateway order paid finally charges the shelf it never charged at checkout.
        $heldBefore = ! in_array($previousStatus, ['cancelled', 'refunded'], true)
            && ($order->payment_method === 'cod' || $previousPayment === 'paid');
        // orderHoldsStock() only looks at how the order is paid, so a cancelled COD order still
        // reads as holding stock; the closed check has to sit on top of it.
        $heldNow = ! in_array($order->status, ['cancelled', 'refunded'], true)
            && $stock->orderHoldsStock($order);

        if ($heldBefore && ! $heldNow) {
            $stock->restoreOrder($order, "Order {$order->order_number} {$order->status} by admin");
        } elseif (! $heldBefore && $heldNow) {
            $stock->deductOrder($order, "Order {$order->order_number} now takes stock ({$previousStatus}/{$previousPayment} -> {$order->status}/{$order->payment_status})", true);
        }
    }
}
