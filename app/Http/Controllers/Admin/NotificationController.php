<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for the admin panel.
     */
    public function index(Request $request)
    {
        $query = Notification::forAdmin()
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->unread();
            } elseif ($request->filter === 'read') {
                $query->read();
            }
        }

        // Pagination
        $perPage = $request->per_page ?? 20;
        $notifications = $query->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'notifications' => $notifications->items(),
                'total' => $notifications->total(),
                'unread_count' => Notification::forAdmin()->unread()->count(),
                'has_more' => $notifications->hasMorePages(),
                'next_page' => $notifications->nextPageUrl(),
            ]);
        }

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Get notification counts.
     */
    public function counts()
    {
        $total = Notification::forAdmin()->count();
        $unread = Notification::forAdmin()->unread()->count();
        $read = Notification::forAdmin()->read()->count();

        // Group by type
        $byType = Notification::forAdmin()
            ->select('type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(CASE WHEN is_read = false THEN 1 ELSE 0 END) as unread_count')
            ->groupBy('type')
            ->get();

        return response()->json([
            'total' => $total,
            'unread' => $unread,
            'read' => $read,
            'by_type' => $byType,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::forAdmin()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => Notification::forAdmin()->unread()->count(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notification::forAdmin()->unread()->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Mark notifications as read via AJAX (for dropdown clicks).
     */
    public function markRead(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        Notification::whereIn('id', $request->ids)
            ->where('is_for_admin', true)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'unread_count' => Notification::forAdmin()->unread()->count(),
        ]);
    }

    /**
     * Get recent unread notifications for the dropdown.
     */
    public function recent(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = 10;

        $notifications = Notification::forAdmin()
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $total = Notification::forAdmin()->count();
        $unreadCount = Notification::forAdmin()->unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'has_more' => $page * $perPage < $total,
            'current_page' => $page,
        ]);
    }

    /**
     * Delete all read notifications.
     */
    public function clearRead()
    {
        Notification::forAdmin()->read()->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Delete all notifications.
     */
    public function clearAll()
    {
        Notification::forAdmin()->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Create a test notification (for testing purposes).
     */
    public function createTestNotification()
    {
        $types = ['order', 'review', 'stock', 'refund', 'customer', 'support', 'system', 'product'];
        $type = $types[array_rand($types)];

        $messages = [
            'order' => [
                'New order received from {customer}',
                'Order #{order_id} has been placed',
                'Order payment confirmed',
            ],
            'review' => [
                'New review submitted for {product}',
                'Customer left a 5-star review',
                'Pending review requires approval',
            ],
            'stock' => [
                '{product} is running low on stock',
                'Product {product} is out of stock',
                'Stock alert: {product} below threshold',
            ],
            'refund' => [
                'New refund request from {customer}',
                'Refund request #{id} pending approval',
                'Refund processed for order #{order_id}',
            ],
            'customer' => [
                'New customer registered: {customer}',
                'Customer {customer} placed first order',
                'New subscriber added to newsletter',
            ],
            'support' => [
                'New support ticket from {customer}',
                'Ticket #{id} requires attention',
                'Support ticket replied by customer',
            ],
            'system' => [
                'System update available',
                'Backup completed successfully',
                'Scheduled task executed',
            ],
            'product' => [
                'New product added: {product}',
                'Product {product} published',
                'Product {product} draft saved',
            ],
        ];

        $titles = [
            'order' => 'New Order',
            'review' => 'New Review',
            'stock' => 'Stock Alert',
            'refund' => 'Refund Request',
            'customer' => 'New Customer',
            'support' => 'Support Ticket',
            'system' => 'System Update',
            'product' => 'Product Update',
        ];

        $message = $messages[$type][array_rand($messages[$type])];

        Notification::notifyAdmin(
            $type,
            $titles[$type],
            $message,
            '#',
            ['order_id' => rand(1000, 9999)]
        );

        return response()->json([
            'success' => true,
            'message' => 'Test notification created',
        ]);
    }
}
