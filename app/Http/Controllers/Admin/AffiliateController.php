<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateRequest;
use App\Models\AffiliateSale;
use App\Models\AffiliateWithdrawal;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
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
        $affiliate->update([
            'status' => 'approved',
            'approved_at' => now(),
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
        $affiliate->update(['status' => 'suspended']);

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
        $affiliate->delete();

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
        $withdrawal = AffiliateWithdrawal::findOrFail($id);
        
        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending withdrawals can be approved.');
        }

        DB::transaction(function () use ($withdrawal) {
            $withdrawal->update([
                'status' => 'approved',
                'processed_at' => now(),
            ]);

            // Deduct from affiliate balance
            $affiliate = $withdrawal->affiliate;
            $affiliate->decrement('balance', $withdrawal->amount);
        });

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
        $withdrawal = AffiliateWithdrawal::findOrFail($id);
        
        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending withdrawals can be rejected.');
        }

        DB::transaction(function () use ($withdrawal) {
            $withdrawal->update([
                'status' => 'rejected',
                'processed_at' => now(),
            ]);

            // Return amount to affiliate balance
            $affiliate = $withdrawal->affiliate;
            $affiliate->increment('balance', $withdrawal->amount);
            $affiliate->decrement('pending_balance', $withdrawal->amount);
        });

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
        
        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);
        
        // Pagination with per_page support
        $perPage = $request->per_page ?? 15;
        $requests = $query->paginate($perPage);
        
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

        DB::transaction(function () use ($request) {
            $request->update([
                'status' => 'approved',
                'processed_at' => now(),
            ]);

            // Create affiliate account
            Affiliate::create([
                'user_id' => $request->user_id,
                'commission_rate' => (float) Setting::get('default_commission_rate', '5.00'),
                'status' => 'approved',
                'approved_at' => now(),
                'website' => $request->website,
                'social_links' => $request->social_links,
            ]);
        });

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
        
        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);
        
        // Pagination with per_page support
        $perPage = $request->per_page ?? 15;
        $affiliates = $query->paginate($perPage);
        
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
