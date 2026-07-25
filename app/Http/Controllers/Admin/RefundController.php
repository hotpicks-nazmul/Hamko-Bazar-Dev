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
        $perPage = $this->perPage($request);
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
        $perPage = $this->perPage($request);
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
        $perPage = $this->perPage($request);
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
        $perPage = $this->perPage($request);
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
     *
     * Labels only: the order is not touched here. It used to flip to "refunded" on approval while
     * payment_status stayed pending, so the order left Delivered before the customer got anything.
     */
    public function approve(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        try {
            app(\App\Services\RefundService::class)->approve($refund, $request->admin_note, Auth::id());
        } catch (\App\Exceptions\RefundException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Refund approved. Process it when the money has been returned.');
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

        try {
            app(\App\Services\RefundService::class)->reject($refund, $request->admin_note, Auth::id());
        } catch (\App\Exceptions\RefundException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Refund rejected successfully.');
    }

    /**
     * Mark refund as processed (complete the refund): this is the step that moves money and stock.
     */
    public function process(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        try {
            app(\App\Services\RefundService::class)->process($refund, $request->admin_note, Auth::id());
        } catch (\App\Exceptions\RefundException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Refund processed: the order is refunded and its stock has been returned.');
    }

    /**
     * Bulk action on refunds.
     *
     * Each refund goes through the same service the single-row buttons use, so an illegal move is
     * refused here too instead of being written by a copy of the logic.
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:approve,reject,process',
            'ids' => 'required',
        ]);

        // The table posts a JSON string; accept an array too, since a form post is the other shape
        // this endpoint will ever see.
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }
        $ids = is_array($ids) ? array_values(array_filter(array_map('intval', $ids))) : [];

        if (! $ids) {
            return redirect()->back()->with('error', 'No refunds selected for this action.');
        }

        $action = $request->action;
        $service = app(\App\Services\RefundService::class);
        $done = 0;
        $refused = [];

        foreach (Refund::whereIn('id', $ids)->get() as $refund) {
            try {
                match ($action) {
                    'approve' => $service->approve($refund, null, Auth::id()),
                    'reject' => $service->reject($refund, 'Bulk rejection', Auth::id()),
                    'process' => $service->process($refund, null, Auth::id()),
                };
                $done++;
            } catch (\App\Exceptions\RefundException $e) {
                $refused[] = $refund->refund_number . ' (' . $e->getMessage() . ')';
            }
        }

        if ($done === 0) {
            return redirect()->back()->with('error', 'No refunds were changed. ' . implode(' ', array_slice($refused, 0, 3)));
        }

        $message = "{$done} refund(s) " . match ($action) {
            'approve' => 'approved',
            'reject' => 'rejected',
            'process' => 'processed',
        } . ' successfully.';

        if ($refused) {
            $message .= ' Skipped: ' . implode(' ', array_slice($refused, 0, 3));
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Page size for the four list screens: a number, clamped, instead of whatever the query string
     * says (per_page=abc used to be an HTTP 500 and per_page=100000 a full-table render).
     */
    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 25);

        return $perPage >= 1 && $perPage <= 100 ? $perPage : 25;
    }
}
