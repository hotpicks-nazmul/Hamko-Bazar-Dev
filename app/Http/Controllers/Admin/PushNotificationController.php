<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\PushNotification;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PushNotificationController extends Controller
{
    /**
     * Display a listing of the push notifications.
     */
    public function index(Request $request)
    {
        $query = PushNotification::query()->orderBy('created_at', 'desc');

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('message', 'like', "%{$request->search}%");
            });
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->paginate(25);

        // Statistics
        $stats = [
            'total' => PushNotification::count(),
            'draft' => PushNotification::draft()->count(),
            'scheduled' => PushNotification::scheduled()->count(),
            'sent' => PushNotification::sent()->count(),
            'failed' => PushNotification::failed()->count(),
            'total_delivered' => PushNotification::sum('delivered_count'),
            'total_clicked' => PushNotification::sum('clicked_count'),
        ];

        return view('admin.marketing.push-notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Show the form for creating a new push notification.
     */
    public function create()
    {
        $products = Product::where('is_active', 1)->select('id', 'name', 'sku')->limit(50)->get();
        $categories = Category::where('status', 1)->select('id', 'name')->get();
        $users = User::where('status', 'active')->select('id', 'name', 'email')->limit(50)->get();

        return view('admin.marketing.push-notifications.create', compact('products', 'categories', 'users'));
    }

    /**
     * Store a newly created push notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
            'target_type' => 'nullable|string|in:all,specific_user,user_group,product,category',
            'target_id' => 'nullable',
            'action_url' => 'nullable|url',
            'schedule_type' => 'required|in:now,scheduled',
            'scheduled_at' => 'required_if:schedule_type,scheduled|nullable|date|after:now',
        ]);

        $data = $request->except(['image', 'schedule_type', 'scheduled_at']);

        // Handle image upload with ImageHelper
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'push-notifications',
                    512,
                    150,
                    85
                );
                $data['image'] = $imageResult['path'];
            }
        }

        // Handle schedule
        if ($request->schedule_type === 'scheduled') {
            $data['status'] = 'scheduled';
            $data['scheduled_at'] = $request->scheduled_at;
        } else {
            $data['status'] = 'draft';
        }

        $data['created_by'] = Auth::id();

        // Calculate recipients count based on target type
        $data['recipients_count'] = $this->calculateRecipients($request->target_type, $request->target_id);

        $notification = PushNotification::create($data);

        // If "send now", trigger the send process
        if ($request->schedule_type === 'now') {
            $this->sendNotification($notification);
        }

        return redirect()->route('admin.marketing.push-notifications.index')
            ->with('success', $request->schedule_type === 'now' 
                ? 'Push notification sent successfully!' 
                : 'Push notification scheduled successfully!');
    }

    /**
     * Show the form for editing the specified push notification.
     */
    public function edit(PushNotification $pushNotification)
    {
        // Only allow editing draft notifications
        if (!in_array($pushNotification->status, ['draft', 'failed'])) {
            return redirect()->route('admin.marketing.push-notifications.index')
                ->with('error', 'Only draft or failed notifications can be edited.');
        }

        $products = Product::where('is_active', 1)->select('id', 'name', 'sku')->limit(50)->get();
        $categories = Category::where('status', 1)->select('id', 'name')->get();
        $users = User::where('status', 'active')->select('id', 'name', 'email')->limit(50)->get();

        return view('admin.marketing.push-notifications.edit', compact('pushNotification', 'products', 'categories', 'users'));
    }

    /**
     * Update the specified push notification.
     */
    public function update(Request $request, PushNotification $pushNotification)
    {
        // Only allow editing draft or failed notifications
        if (!in_array($pushNotification->status, ['draft', 'failed'])) {
            return redirect()->route('admin.marketing.push-notifications.index')
                ->with('error', 'Only draft or failed notifications can be edited.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
            'target_type' => 'nullable|string|in:all,specific_user,user_group,product,category',
            'target_id' => 'nullable',
            'action_url' => 'nullable|url',
            'schedule_type' => 'required|in:now,scheduled',
            'scheduled_at' => 'required_if:schedule_type,scheduled|nullable|date|after:now',
        ]);

        $data = $request->except(['image', 'schedule_type', 'scheduled_at']);

        // Handle image upload with ImageHelper
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($pushNotification->image) {
                ImageHelper::deleteImage($pushNotification->image);
            }
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'push-notifications',
                    512,
                    150,
                    85
                );
                $data['image'] = $imageResult['path'];
            }
        }

        // Handle schedule
        if ($request->schedule_type === 'scheduled') {
            $data['status'] = 'scheduled';
            $data['scheduled_at'] = $request->scheduled_at;
            $data['sent_at'] = null;
        } else {
            $data['status'] = 'draft';
            $data['scheduled_at'] = null;
        }

        // Update recipients count
        $data['recipients_count'] = $this->calculateRecipients($request->target_type, $request->target_id);

        $pushNotification->update($data);

        // If "send now", trigger the send process
        if ($request->schedule_type === 'now') {
            $this->sendNotification($pushNotification);
        }

        return redirect()->route('admin.marketing.push-notifications.index')
            ->with('success', $request->schedule_type === 'now' 
                ? 'Push notification sent successfully!' 
                : 'Push notification updated successfully!');
    }

    /**
     * Remove the specified push notification from storage.
     */
    public function destroy(PushNotification $pushNotification)
    {
        // Delete image if exists
        if ($pushNotification->image) {
            ImageHelper::deleteImage($pushNotification->image);
        }

        $pushNotification->delete();

        return redirect()->route('admin.marketing.push-notifications.index')
            ->with('success', 'Push notification deleted successfully!');
    }

    /**
     * Send notification immediately (simulated - would integrate with FCM/APNs in production)
     */
    public function send(PushNotification $pushNotification)
    {
        if ($pushNotification->status === 'sent') {
            return redirect()->route('admin.marketing.push-notifications.index')
                ->with('error', 'This notification has already been sent.');
        }

        $this->sendNotification($pushNotification);

        return redirect()->route('admin.marketing.push-notifications.index')
            ->with('success', 'Push notification sent successfully!');
    }

    /**
     * Duplicate a push notification.
     */
    public function duplicate(PushNotification $pushNotification)
    {
        $newNotification = $pushNotification->replicate();
        $newNotification->title = $pushNotification->title . ' (Copy)';
        $newNotification->status = 'draft';
        $newNotification->scheduled_at = null;
        $newNotification->sent_at = null;
        $newNotification->delivered_count = 0;
        $newNotification->clicked_count = 0;
        $newNotification->created_by = Auth::id();
        $newNotification->save();

        return redirect()->route('admin.marketing.push-notifications.edit', $newNotification->id)
            ->with('success', 'Push notification duplicated! You can now edit it.');
    }

    /**
     * Get recipient count via AJAX.
     */
    public function getRecipientCount(Request $request)
    {
        $count = $this->calculateRecipients($request->target_type, $request->target_id);
        
        return response()->json(['count' => $count]);
    }

    /**
     * Calculate recipients based on target type.
     */
    private function calculateRecipients($targetType, $targetId)
    {
        switch ($targetType) {
            case 'specific_user':
                return $targetId ? 1 : 0;
            case 'product':
            case 'category':
                return 0; // These are informational, not recipient counts
            case 'user_group':
                // Would query user group members
                return 0;
            case 'all':
            default:
                return User::where('status', 'active')->count();
        }
    }

    /**
     * Simulate sending the notification.
     * In production, this would integrate with Firebase Cloud Messaging (FCM) 
     * or Apple Push Notification Service (APNs).
     */
    private function sendNotification(PushNotification $notification)
    {
        // Simulate sending - in production, integrate with FCM/APNs
        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
            'delivered_count' => $notification->recipients_count,
        ]);
    }
}
