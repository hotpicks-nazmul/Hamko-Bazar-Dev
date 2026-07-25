<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    /**
     * Display a listing of refunds.
     */
    public function index(Request $request)
    {
        $query = Refund::with(['order', 'user']);

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by reason
        if ($request->reason) {
            $query->where('reason', $request->reason);
        }

        // Sorting with whitelist
        $sortableColumns = ['refund_number', 'refund_amount', 'status', 'created_at'];
        $sort = $request->sort && in_array($request->sort, $sortableColumns) ? $request->sort : 'created_at';
        $direction = in_array($request->direction, ['asc', 'desc']) ? $request->direction : 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $refunds = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => Refund::count(),
            'pending' => Refund::pending()->count(),
            'approved' => Refund::approved()->count(),
            'rejected' => Refund::rejected()->count(),
            'processed' => Refund::processed()->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.refunds.partials.table-rows', compact('refunds'))->render(),
                'pagination' => $refunds->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.refunds.index', compact('refunds', 'stats'));
    }

    /**
     * Display refund requests (pending).
     */
    public function requests(Request $request)
    {
        $query = Refund::with(['order', 'user'])->pending();

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by reason
        if ($request->reason) {
            $query->where('reason', $request->reason);
        }

        // Sorting
        $sortableColumns = ['refund_number', 'refund_amount', 'status', 'created_at'];
        $sort = $request->sort && in_array($request->sort, $sortableColumns) ? $request->sort : 'created_at';
        $direction = in_array($request->direction, ['asc', 'desc']) ? $request->direction : 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $refunds = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => Refund::count(),
            'pending' => Refund::pending()->count(),
            'approved' => Refund::approved()->count(),
            'rejected' => Refund::rejected()->count(),
            'processed' => Refund::processed()->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.refunds.partials.table-rows', compact('refunds'))->render(),
                'pagination' => $refunds->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.refunds.requests', compact('refunds', 'stats'));
    }

    /**
     * Display approved refunds.
     */
    public function approved(Request $request)
    {
        $query = Refund::with(['order', 'user'])->approved();

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by reason
        if ($request->reason) {
            $query->where('reason', $request->reason);
        }

        // Sorting
        $sortableColumns = ['refund_number', 'refund_amount', 'status', 'created_at'];
        $sort = $request->sort && in_array($request->sort, $sortableColumns) ? $request->sort : 'created_at';
        $direction = in_array($request->direction, ['asc', 'desc']) ? $request->direction : 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $refunds = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => Refund::count(),
            'pending' => Refund::pending()->count(),
            'approved' => Refund::approved()->count(),
            'rejected' => Refund::rejected()->count(),
            'processed' => Refund::processed()->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.refunds.partials.table-rows', compact('refunds'))->render(),
                'pagination' => $refunds->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.refunds.approved', compact('refunds', 'stats'));
    }

    /**
     * Display rejected refunds.
     */
    public function rejected(Request $request)
    {
        $query = Refund::with(['order', 'user'])->rejected();

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by reason
        if ($request->reason) {
            $query->where('reason', $request->reason);
        }

        // Sorting
        $sortableColumns = ['refund_number', 'refund_amount', 'status', 'created_at'];
        $sort = $request->sort && in_array($request->sort, $sortableColumns) ? $request->sort : 'created_at';
        $direction = in_array($request->direction, ['asc', 'desc']) ? $request->direction : 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $refunds = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => Refund::count(),
            'pending' => Refund::pending()->count(),
            'approved' => Refund::approved()->count(),
            'rejected' => Refund::rejected()->count(),
            'processed' => Refund::processed()->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.refunds.partials.table-rows', compact('refunds'))->render(),
                'pagination' => $refunds->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.refunds.rejected', compact('refunds', 'stats'));
    }

    /**
     * Display refund configuration.
     */
    public function configuration()
    {
        $settings = \App\Models\Setting::pluck('value', 'key');

        $stats = [
            'total' => \App\Models\Refund::count(),
            'pending' => \App\Models\Refund::pending()->count(),
            'approved' => \App\Models\Refund::approved()->count(),
            'rejected' => \App\Models\Refund::rejected()->count(),
            'processed' => \App\Models\Refund::processed()->count(),
        ];

        return view('admin.refunds.configuration', compact('settings', 'stats'));
    }

    /**
     * Update refund configuration.
     */
    public function updateConfiguration(Request $request)
    {
        $request->validate([
            'enable_refunds' => 'nullable',
            'auto_approve' => 'nullable',
            'refund_reasons' => 'nullable|array',
            'refund_reasons.*' => 'nullable|string',
            'refund_within_days' => 'nullable|integer|min:1|max:365',
            'refund_method' => 'nullable|string|in:original_payment,store_credit,bank_transfer',
        ]);

        $settings = [
            'enable_refunds' => $request->has('enable_refunds') ? '1' : '0',
            'auto_approve_refunds' => $request->has('auto_approve') ? '1' : '0',
            'refund_within_days' => $request->input('refund_within_days', 30),
            'refund_method' => $request->input('refund_method', 'original_payment'),
        ];

        if ($request->has('refund_reasons')) {
            $settings['refund_reasons'] = json_encode($request->input('refund_reasons'));
        }

        foreach ($settings as $key => $value) {
            \App\Models\Setting::set($key, $value);
        }

        return redirect()->route('admin.refunds.configuration')
            ->with('success', 'Refund configuration updated successfully.');
    }

    /**
     * Show refund details.
     */
    public function show($id)
    {
        $refund = Refund::with(['order', 'order.items', 'user', 'processedBy'])->findOrFail($id);

        return view('admin.refunds.show', compact('refund'));
    }

    /**
     * Approve a refund.
     */
    public function approve(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $refund->update([
            'status' => 'approved',
            'admin_note' => $request->admin_note,
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);

        // Update order status to refunded (only if not already cancelled or refunded)
        $order = $refund->order;
        if ($order && ! in_array($order->status, ['cancelled', 'refunded'])) {
            $order->update(['status' => 'refunded']);
        }

        return redirect()->back()
            ->with('success', 'Refund approved successfully.');
    }

    /**
     * Reject a refund.
     */
    public function reject(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);

        $request->validate([
            'admin_note' => 'required|string|max:1000',
        ]);

        $refund->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);

        return redirect()->back()
            ->with('success', 'Refund rejected successfully.');
    }

    /**
     * Mark refund as processed (complete the refund).
     */
    public function process(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $refund->update([
            'status' => 'processed',
            'admin_note' => $refund->admin_note."\n".$request->admin_note,
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);

        // Update order payment status to refunded
        $order = $refund->order;
        if ($order) {
            $order->update(['payment_status' => 'refunded']);
        }

        return redirect()->back()
            ->with('success', 'Refund processed successfully.');
    }

    /**
     * Bulk action on refunds.
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:approve,reject,process',
            'ids' => 'required|json',
        ]);

        $ids = json_decode($request->ids, true);
        $action = $request->action;

        // For approve/reject actions, only process pending refunds
        // For process action, only process approved refunds
        $statusFilter = $action === 'process' ? 'approved' : 'pending';
        $refunds = Refund::whereIn('id', $ids)->where('status', $statusFilter)->get();

        if ($refunds->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No refunds selected for this action.');
        }

        foreach ($refunds as $refund) {
            if ($action === 'approve') {
                $refund->update([
                    'status' => 'approved',
                    'processed_at' => now(),
                    'processed_by' => Auth::id(),
                ]);

                $order = $refund->order;
                if ($order && ! in_array($order->status, ['cancelled', 'refunded'])) {
                    $order->update(['status' => 'refunded']);
                }
            } elseif ($action === 'reject') {
                $refund->update([
                    'status' => 'rejected',
                    'admin_note' => 'Bulk rejection',
                    'processed_at' => now(),
                    'processed_by' => Auth::id(),
                ]);
            } elseif ($action === 'process') {
                $refund->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'processed_by' => Auth::id(),
                ]);

                $order = $refund->order;
                if ($order) {
                    $order->update(['payment_status' => 'refunded']);
                }
            }
        }

        $count = $refunds->count();
        $message = match ($action) {
            'approve' => "{$count} refund(s) approved successfully.",
            'reject' => "{$count} refund(s) rejected successfully.",
            'process' => "{$count} refund(s) processed successfully.",
        };

        return redirect()->back()->with('success', $message);
    }
}
