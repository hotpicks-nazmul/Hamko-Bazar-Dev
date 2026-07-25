<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AffiliatePayoutException;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Affiliate;
use App\Models\AffiliateClick;
use App\Models\AffiliateRequest;
use App\Models\AffiliateSale;
use App\Models\AffiliateWithdrawal;
use App\Models\User;
use App\Models\Setting;
use App\Services\AffiliatePayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
    use SortsLists;
    /**
     * Display list of affiliate users
     * 
     * @return \Illuminate\View\View
     */
    public function users(Request $request)
    {
        $query = Affiliate::with('user');
        
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('affiliate_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $affiliates = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistics for stat cards
        $stats = [
            'total' => Affiliate::count(),
            'approved' => Affiliate::where('status', 'approved')->count(),
            'pending' => Affiliate::where('status', 'pending')->count(),
            'suspended' => Affiliate::where('status', 'suspended')->count(),
            'total_balance' => Affiliate::sum('balance'),
            'total_earnings' => Affiliate::sum('total_earnings'),
        ];
        
        if ($request->ajax()) {
            $html = view('admin.affiliate.users.partials.table-rows', compact('affiliates'))->render();
            return response()->json(['html' => $html]);
        }
        
        return view('admin.affiliate.users.index', compact('affiliates', 'stats'));
    }

    /**
     * Show affiliate user details
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showUser($id)
    {
        $affiliate = Affiliate::with(['user', 'sales', 'withdrawals'])
            ->withCount(['clicks', 'sales'])
            ->findOrFail($id);
        
        $recentSales = AffiliateSale::where('affiliate_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return view('admin.affiliate.users.show', compact('affiliate', 'recentSales'));
    }

    /**
     * Approve affiliate user
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveUser($id)
    {
        $affiliate = Affiliate::findOrFail($id);

        if ($affiliate->status === 'approved') {
            return redirect()->back()->with('info', 'This affiliate is already approved.');
        }

        $from = $affiliate->status;

        $affiliate->update([
            'status' => 'approved',
            // A fact, not a toggle: the first approval is the one that happened.
            'approved_at' => $affiliate->approved_at ?? now(),
        ]);

        ActivityLog::adminLog('Affiliate approved', $affiliate, auth()->user(), [
            'from' => $from,
            'to' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Affiliate user approved successfully.');
    }

    /**
     * Suspend affiliate user
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspendUser($id)
    {
        $affiliate = Affiliate::findOrFail($id);

        if ($affiliate->status === 'suspended') {
            return redirect()->back()->with('info', 'This affiliate is already suspended.');
        }

        $from = $affiliate->status;
        $affiliate->update(['status' => 'suspended']);

        ActivityLog::adminLog('Affiliate suspended', $affiliate, auth()->user(), [
            'from' => $from,
            'balance' => (float) $affiliate->balance,
            'pending_balance' => (float) $affiliate->pending_balance,
        ]);

        return redirect()->back()->with('success', 'Affiliate user suspended successfully.');
    }

    /**
     * Delete affiliate user
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyUser($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        $name = $affiliate->user?->name ?? ('#' . $affiliate->id);

        // Every child table cascades on delete: payouts, sales and clicks would vanish with the
        // row and there is no undo. Refuse, as the seller and customer screens now do.
        $history = [
            'payout' => AffiliateWithdrawal::where('affiliate_id', $affiliate->id)->count(),
            'sale' => AffiliateSale::where('affiliate_id', $affiliate->id)->count(),
            'click' => AffiliateClick::where('affiliate_id', $affiliate->id)->count(),
        ];
        $held = array_filter($history);

        if (! empty($held)) {
            $parts = [];
            foreach ($held as $label => $count) {
                $parts[] = $count . ' ' . $label . ($count === 1 ? '' : 's');
            }

            return redirect()->back()->with('error', sprintf(
                'Cannot delete %s: %s attached, and deleting the affiliate would destroy them. Suspend the affiliate instead.',
                $name,
                implode(', ', $parts)
            ));
        }

        $affiliate->delete();

        ActivityLog::adminLog('Affiliate deleted', null, auth()->user(), [
            'affiliate_id' => $affiliate->id,
            'user_id' => $affiliate->user_id,
            'affiliate_code' => $affiliate->affiliate_code,
        ]);

        return redirect()->route('admin.affiliate.users.index')
            ->with('success', 'Affiliate user deleted successfully.');
    }

    /**
     * Display affiliate configuration settings
     * 
     * @return \Illuminate\View\View
     */
    public function configuration()
    {
        $settings = [
            'affiliate_enabled' => Setting::get('affiliate_enabled', '1') === '1' || Setting::get('affiliate_enabled', '1') === true,
            'default_commission_rate' => (float) Setting::get('default_commission_rate', '5.00'),
            'min_withdrawal_amount' => (float) Setting::get('min_withdrawal_amount', '50.00'),
            'cookie_lifetime' => (int) Setting::get('affiliate_cookie_lifetime', '30'),
            'affiliate_registration' => Setting::get('affiliate_registration', 'manual'),
            'commission_type' => Setting::get('affiliate_commission_type', 'percentage'),
        ];
        
        return view('admin.affiliate.configuration', compact('settings'));
    }

    /**
     * Update affiliate configuration settings
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateConfiguration(Request $request)
    {
        $validated = $request->validate([
            'affiliate_enabled' => 'required|boolean',
            'default_commission_rate' => 'required|numeric|min:0|max:100',
            'min_withdrawal_amount' => 'required|numeric|min:0',
            'cookie_lifetime' => 'required|integer|min:1|max:365',
            'affiliate_registration' => 'required|in:auto,manual',
            'commission_type' => 'required|in:percentage,fixed',
        ]);

        Setting::set('affiliate_enabled', $validated['affiliate_enabled'] ? '1' : '0', 'affiliate');
        Setting::set('default_commission_rate', (string) $validated['default_commission_rate'], 'affiliate');
        Setting::set('min_withdrawal_amount', (string) $validated['min_withdrawal_amount'], 'affiliate');
        Setting::set('affiliate_cookie_lifetime', (string) $validated['cookie_lifetime'], 'affiliate');
        Setting::set('affiliate_registration', $validated['affiliate_registration'], 'affiliate');
        Setting::set('affiliate_commission_type', $validated['commission_type'], 'affiliate');

        return redirect()->back()->with('success', 'Affiliate configuration updated successfully.');
    }

    /**
     * Display affiliate payouts
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function payouts(Request $request)
    {
        $query = AffiliateWithdrawal::with('affiliate.user');
        
        // Search by affiliate name
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('affiliate.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistics for stat cards
        $stats = [
            'total' => AffiliateWithdrawal::count(),
            'pending' => AffiliateWithdrawal::where('status', 'pending')->count(),
            'approved' => AffiliateWithdrawal::where('status', 'approved')->count(),
            'rejected' => AffiliateWithdrawal::where('status', 'rejected')->count(),
            'total_amount' => AffiliateWithdrawal::where('status', 'approved')->sum('amount'),
            'pending_amount' => AffiliateWithdrawal::where('status', 'pending')->sum('amount'),
        ];
        
        return view('admin.affiliate.payouts', compact('withdrawals', 'stats'));
    }

    /**
     * Approve affiliate payout
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approvePayout($id)
    {
        try {
            app(AffiliatePayoutService::class)->approve((int) $id, request('admin_note'), request('transaction_id'));
        } catch (AffiliatePayoutException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Payout approved successfully.');
    }

    /**
     * Reject affiliate payout
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectPayout($id)
    {
        try {
            app(AffiliatePayoutService::class)->reject((int) $id, request('admin_note'));
        } catch (AffiliatePayoutException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Payout rejected successfully.');
    }

    /**
     * Display affiliate requests
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function requests(Request $request)
    {
        $query = AffiliateRequest::with('user');
        
        // Search by user name, email, or website
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('website', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Sorting — only the columns this table actually has, and only asc/desc.
        $query->orderBy(
            $this->sortColumn($request, ['created_at', 'requested_at', 'processed_at', 'status', 'website'], 'created_at'),
            $this->sortDirection($request)
        );

        $requests = $query->paginate($this->perPage($request));
        
        // Statistics for stat cards (based on filters)
        $statsQuery = AffiliateRequest::query();
        if ($request->search) {
            $search = $request->search;
            $statsQuery->where(function($q) use ($search) {
                $q->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('website', 'like', "%{$search}%");
            });
        }
        if ($request->status) {
            $statsQuery->where('status', $request->status);
        }
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];
        
        // AJAX response for live search
        if ($request->ajax()) {
            $html = view('admin.affiliate.partials.request-rows', compact('requests'))->render();
            return response()->json([
                'html' => $html,
                'stats' => $stats
            ]);
        }
        
        return view('admin.affiliate.requests', compact('requests', 'stats'));
    }

    /**
     * Approve affiliate request
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveRequest($id)
    {
        $request = AffiliateRequest::findOrFail($id);
        
        if ($request->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be approved.');
        }

        // One affiliate account per user. Approving a second request used to create a second row
        // with its own code and its own balance, splitting the user's earnings in two.
        $existing = Affiliate::where('user_id', $request->user_id)->first();
        if ($existing) {
            return redirect()->back()->with('error', sprintf(
                'This user is already an affiliate (%s, %s). Reject the request instead of approving a second account.',
                $existing->affiliate_code,
                $existing->status
            ));
        }

        try {
            DB::transaction(function () use ($request) {
                $request->update([
                    'status' => 'approved',
                    'processed_at' => now(),
                ]);

                $affiliate = Affiliate::create([
                    'user_id' => $request->user_id,
                    'commission_rate' => (float) Setting::get('default_commission_rate', '5.00'),
                    'status' => 'approved',
                    'approved_at' => now(),
                    'website' => $request->website,
                    'social_links' => $request->social_links,
                ]);

                ActivityLog::adminLog('Affiliate request approved', $affiliate, auth()->user(), [
                    'request_id' => $request->id,
                    'user_id' => $request->user_id,
                    'affiliate_code' => $affiliate->affiliate_code,
                    'commission_rate' => (float) $affiliate->commission_rate,
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // The unique index on affiliates.user_id is the last line of defence for a race.
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'This user is already an affiliate; the request was left pending.');
            }

            throw $e;
        }

        return redirect()->back()->with('success', 'Affiliate request approved successfully.');
    }

    /**
     * Reject affiliate request
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectRequest($id)
    {
        $request = AffiliateRequest::findOrFail($id);
        
        if ($request->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be rejected.');
        }

        $request->update([
            'status' => 'rejected',
            'processed_at' => now(),
        ]);

        ActivityLog::adminLog('Affiliate request rejected', $request, auth()->user(), [
            'user_id' => $request->user_id,
            'website' => $request->website,
        ]);

        return redirect()->back()->with('success', 'Affiliate request rejected successfully.');
    }

    /**
     * Display affiliate reports
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function reports(Request $request)
    {
        $query = Affiliate::with('user')
            ->withCount(['clicks', 'sales'])
            ->withSum('sales as total_sales', 'sale_amount')
            ->withSum('sales as total_commission', 'commission_amount');
        
        // Search by affiliate code or user name/email
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('affiliate_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Sorting — `name` lives on users, not affiliates, so it is not in the whitelist.
        $query->orderBy(
            $this->sortColumn($request, ['created_at', 'affiliate_code', 'commission_rate', 'balance', 'pending_balance', 'total_earnings', 'status'], 'created_at'),
            $this->sortDirection($request)
        );

        $affiliates = $query->paginate($this->perPage($request));
        
        // Calculate stats based on filters
        $statsQuery = Affiliate::query();
        if ($request->search) {
            $search = $request->search;
            $statsQuery->where(function($q) use ($search) {
                $q->where('affiliate_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->status) {
            $statsQuery->where('status', $request->status);
        }
        
        $stats = [
            'total_affiliates' => $statsQuery->count(),
            'total_sales' => (clone $statsQuery)->withSum('sales as total_sales', 'sale_amount')->get()->sum('total_sales') ?? 0,
            'total_commissions' => (clone $statsQuery)->withSum('sales as total_commission', 'commission_amount')->get()->sum('total_commission') ?? 0,
            'total_clicks' => (clone $statsQuery)->withCount('clicks')->get()->sum('clicks_count'),
        ];
        
        // AJAX response for live search
        if ($request->ajax()) {
            $html = view('admin.affiliate.reports.partials.table-rows', compact('affiliates'))->render();
            return response()->json([
                'html' => $html,
                'stats' => $stats
            ]);
        }
        
        return view('admin.affiliate.reports', compact('stats', 'affiliates'));
    }

    /**
     * Export affiliate reports
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportReports(Request $request)
    {
        $query = Affiliate::with('user')
            ->withCount(['clicks', 'sales'])
            ->withSum('sales as total_sales', 'sale_amount')
            ->withSum('sales as total_commission', 'commission_amount');
        
        // Handle bulk export with selected IDs
        if ($request->has('ids')) {
            $ids = $request->input('ids');
            if (is_array($ids)) {
                $query->whereIn('id', $ids);
            }
        }
        
        $affiliates = $query->orderBy('created_at', 'desc')->get();

        $csvData = "ID,User Name,User Email,Affiliate Code,Status,Commission Rate,Balance,Total Earnings,Total Clicks,Total Sales,Total Commission,Joined Date\n";
        
        foreach ($affiliates as $affiliate) {
            $csvData .= "{$affiliate->id},";
            $csvData .= "\"{$affiliate->user->name}\",";
            $csvData .= "\"{$affiliate->user->email}\",";
            $csvData .= "\"{$affiliate->affiliate_code}\",";
            $csvData .= "\"{$affiliate->status}\",";
            $csvData .= "{$affiliate->commission_rate},";
            $csvData .= "{$affiliate->balance},";
            $csvData .= "{$affiliate->total_earnings},";
            $csvData .= "{$affiliate->clicks_count},";
            $csvData .= "{$affiliate->total_sales},";
            $csvData .= "{$affiliate->total_commission},";
            $csvData .= "{$affiliate->created_at}\n";
        }
        
        return response()->stream(function() use ($csvData) {
            echo $csvData;
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="affiliate-reports-' . date('Y-m-d') . '.csv"'
        ]);
    }
}
