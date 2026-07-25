<?php

use App\Models\Notification;

/**
 * Send notification to admin
 */
function notify_admin($type, $title, $message, $link = null, $data = null)
{
    return Notification::notifyAdmin($type, $title, $message, $link, $data);
}

/**
 * Send notification to a specific user
 */
function notify_user($user, $type, $title, $message, $link = null, $data = null)
{
    return Notification::notifyUser($user, $type, $title, $message, $link, $data);
}

/**
 * A switch from Settings -> Notification Settings.
 *
 * Every one of those toggles used to be saved and read by nothing. An absent row is treated as ON:
 * that is how the panel behaved before these gates existed, so deploying this changes nothing until
 * an admin actually turns something off.
 */
function notification_enabled(string $key): bool
{
    try {
        return (string) \App\Models\Setting::get($key, '1') !== '0';
    } catch (\Throwable $e) {
        return true;
    }
}

/**
 * Send notification for new order
 */
function notify_new_order($order)
{
    if (! notification_enabled('notify_admin_new_order')) {
        return null;
    }

    $link = route('admin.orders.in-house.show', $order->id);
    
    return Notification::notifyAdmin(
        'order',
        'New Order Received',
        'Order #' . $order->id . ' has been placed by ' . ($order->user->name ?? 'Guest'),
        $link,
        ['order_id' => $order->id, 'order_total' => $order->total]
    );
}

/**
 * Send notification for new review
 */
function notify_new_review($review)
{
    if (! notification_enabled('notify_admin_new_review')) {
        return null;
    }

    $link = route('admin.reviews.index');
    
    return Notification::notifyAdmin(
        'review',
        'New Review Received',
        'A new review has been submitted for ' . ($review->product->name ?? 'a product'),
        $link,
        ['review_id' => $review->id, 'product_id' => $review->product_id, 'rating' => $review->rating]
    );
}

/**
 * Send notification for low stock
 */
function notify_low_stock($product)
{
    if (! notification_enabled('notify_admin_low_stock')) {
        return null;
    }

    $link = route('admin.products.low-stock-alerts');
    
    return Notification::notifyAdmin(
        'stock',
        'Low Stock Alert',
        $product->name . ' is running low on stock (' . $product->stock . ' remaining)',
        $link,
        ['product_id' => $product->id, 'stock' => $product->stock]
    );
}

/**
 * Send notification for out of stock
 */
function notify_out_of_stock($product)
{
    if (! notification_enabled('notify_admin_out_of_stock')) {
        return null;
    }

    $link = route('admin.products.low-stock-alerts');
    
    return Notification::notifyAdmin(
        'stock',
        'Out of Stock Alert',
        $product->name . ' is now out of stock',
        $link,
        ['product_id' => $product->id]
    );
}

/**
 * Send notification for new refund request
 */
function notify_refund_request($refund)
{
    if (! notification_enabled('notify_admin_new_refund')) {
        return null;
    }

    $link = route('admin.refunds.requests');
    
    return Notification::notifyAdmin(
        'refund',
        'New Refund Request',
        'A refund request has been submitted for Order #' . ($refund->order_id ?? 'N/A'),
        $link,
        ['refund_id' => $refund->id, 'order_id' => $refund->order_id]
    );
}

/**
 * Send notification for new customer registration
 */
function notify_new_customer($user)
{
    if (! notification_enabled('notify_admin_new_customer')) {
        return null;
    }

    $link = route('admin.customers.show', $user->id);
    
    return Notification::notifyAdmin(
        'customer',
        'New Customer Registered',
        $user->name . ' has registered as a new customer',
        $link,
        ['user_id' => $user->id]
    );
}

/**
 * Send notification for new support ticket
 */
function notify_new_ticket($ticket)
{
    if (! notification_enabled('notify_admin_new_support_ticket')) {
        return null;
    }

    $link = route('admin.support.tickets.show', $ticket->id);
    
    return Notification::notifyAdmin(
        'support',
        'New Support Ticket',
        'A new support ticket has been submitted by ' . ($ticket->user->name ?? 'Guest'),
        $link,
        ['ticket_id' => $ticket->id]
    );
}

/**
 * Send notification for product question
 */
function notify_product_question($question)
{
    // No dedicated toggle exists for product questions; it shares the support-ticket switch.
    if (! notification_enabled('notify_admin_new_support_ticket')) {
        return null;
    }

    $link = route('admin.support.product-queries.show', $question->id);
    
    return Notification::notifyAdmin(
        'support',
        'New Product Question',
        'A new question has been asked about ' . ($question->product->name ?? 'a product'),
        $link,
        ['question_id' => $question->id, 'product_id' => $question->product_id]
    );
}

/**
 * Send notification for seller registration
 */
function notify_seller_request($seller)
{
    if (! notification_enabled('notify_admin_new_seller')) {
        return null;
    }

    $link = route('admin.sellers.verification');
    
    return Notification::notifyAdmin(
        'customer',
        'New Seller Request',
        $seller->name . ' has applied to become a seller',
        $link,
        ['seller_id' => $seller->id]
    );
}

/**
 * Send notification for payout request
 *
 * Gated by Settings -> Notification Settings -> "New Payout Request", like every other admin
 * notification in this file. It was the one business notification with no switch, so the operator
 * could silence every alert except this one.
 */
function notify_payout_request($payout)
{
    if (! notification_enabled('notify_admin_payout_request')) {
        return null;
    }

    $link = route('admin.sellers.payout-requests');

    return Notification::notifyAdmin(
        'system',
        'New Payout Request',
        'A payout request of ' . format_price($payout->amount) . ' has been submitted',
        $link,
        ['payout_id' => $payout->id, 'seller_id' => $payout->seller_id]
    );
}