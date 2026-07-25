<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AffiliatePayoutException;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use App\Http\Controllers\Controller;
use App\Models\AffiliateWithdrawal;
use App\Services\AffiliatePayoutService;
use Illuminate\Http\Request;

/**
 * Affiliate withdrawals — the same table and the same movements as `admin.affiliate.payouts`.
 *
 * This controller used to carry its own copy of approve/reject (and a bulk loop built on them), which
 * is where the money bugs came from: the copies drifted and each treated the pending bucket
 * differently. It now delegates every movement to AffiliatePayoutService, so there is one rule behind
 * both URLs.
 *
 * Its screen is not in the admin sidebar; keep the route alive for bookmarks until the owner decides
 * whether Payouts or Withdrawals is the keeper (see the Section 10 note in the tracker).
 */
class AffiliateWithdrawalController extends Controller
{
    use SortsLists;

    public function __construct(private readonly AffiliatePayoutService $payouts)
    {
    }

    /**
     * Display list of affiliate withdrawals
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $query = AffiliateWithdrawal::with('affiliate.user');

        // Grouped: an ungrouped orWhere here let `where('status', …)` bind to the last OR term only,
        // so a search overrode the status filter.
        $query->when($search, function ($q) use ($search) {
            $q->where(function ($inner) use ($search) {
                $inner->whereHas('affiliate.user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");
            });
        });

        $query->when($status, function ($q) use ($status) {
            $q->where('status', $status);
        });

        $withdrawals = $query
            ->orderBy(
                $this->sortColumn($request, ['created_at', 'requested_at', 'processed_at', 'amount', 'status', 'payment_method'], 'created_at'),
                $this->sortDirection($request)
            )
            ->paginate($this->perPage($request));

        $stats = [
            'total' => AffiliateWithdrawal::count(),
            'pending' => AffiliateWithdrawal::where('status', 'pending')->count(),
            'approved' => AffiliateWithdrawal::where('status', 'approved')->count(),
            'rejected' => AffiliateWithdrawal::where('status', 'rejected')->count(),
            'total_amount' => AffiliateWithdrawal::where('status', 'approved')->sum('amount'),
            'pending_amount' => AffiliateWithdrawal::where('status', 'pending')->sum('amount'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.affiliate.withdrawals.partials.withdrawal-rows', compact('withdrawals'))->render(),
                // Modals come back separately so the swap never injects them inside <tbody>.
                'modals' => view('admin.affiliate.withdrawals.partials.withdrawal-modals', compact('withdrawals'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('admin.affiliate.withdrawals.index', compact('withdrawals', 'search', 'stats'));
    }

    /**
     * Display affiliate withdrawal details
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $withdrawal = AffiliateWithdrawal::with('affiliate.user')->findOrFail($id);

        return view('admin.affiliate.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Approve affiliate withdrawal — delegate to the one money rule.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($id)
    {
        try {
            $this->payouts->approve((int) $id, request('admin_note'));
        } catch (AffiliatePayoutException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Withdrawal approved successfully.');
    }

    /**
     * Reject affiliate withdrawal — delegate to the one money rule.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject($id)
    {
        try {
            $this->payouts->reject((int) $id, request('admin_note'));
        } catch (AffiliatePayoutException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Withdrawal rejected successfully.');
    }

    /**
     * Bulk approve/reject.
     *
     * The page posts `ids` as `JSON.stringify([...])` (a string) while this method validated
     * `required|array` and then `json_decode`d it: the button answered 422 every time, and an
     * array payload answered 500 (json_decode does not take an array). Accept both shapes.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:approve,reject',
            'ids' => 'required',
        ]);

        $ids = $this->normaliseIds($request->input('ids'));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No pending withdrawals selected.');
        }

        $withdrawals = AffiliateWithdrawal::whereIn('id', $ids)->where('status', 'pending')->get();

        if ($withdrawals->isEmpty()) {
            return redirect()->back()->with('error', 'No pending withdrawals selected.');
        }

        $done = 0;
        $skipped = [];

        foreach ($withdrawals as $withdrawal) {
            try {
                $validated['action'] === 'approve'
                    ? $this->payouts->approve($withdrawal->id, 'Bulk approved')
                    : $this->payouts->reject($withdrawal->id, 'Bulk rejected');
                $done++;
            } catch (AffiliatePayoutException $e) {
                // One bad row (typically not enough balance) must not abort the whole batch.
                $skipped[] = '#' . $withdrawal->id . ': ' . $e->getMessage();
            }
        }

        $message = $validated['action'] === 'approve'
            ? "{$done} withdrawal(s) approved."
            : "{$done} withdrawal(s) rejected.";

        if (! empty($skipped)) {
            return redirect()->back()->with('warning', $message . ' Skipped — ' . implode(' ', $skipped));
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Accept `[1,2]`, '"[1,2]"' or '1,2' and return a clean int list.
     *
     * @param  mixed  $raw
     * @return array<int, int>
     */
    private function normaliseIds($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : explode(',', $raw);
        }

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $raw)));
    }
}
