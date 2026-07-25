<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateWithdrawal;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateWithdrawalController extends Controller
{
    /**
     * Display list of affiliate withdrawals
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $perPage = $request->get('per_page', 15);
        
        $query = AffiliateWithdrawal::with('affiliate.user');
        
        // Search filter
        $query->when($search, function ($q) use ($search) {
            $q->whereHas('affiliate.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhere('id', 'like', "%{$search}%")
            ->orWhere('amount', 'like', "%{$search}%");
        });
        
        // Status filter
        $query->when($status, function ($q) use ($status) {
            $q->where('status', $status);
        });
        
        $withdrawals = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Statistics for stat cards
        $stats = [
            'total' => AffiliateWithdrawal::count(),
            'pending' => AffiliateWithdrawal::where('status', 'pending')->count(),
            'approved' => AffiliateWithdrawal::where('status', 'approved')->count(),
            'rejected' => AffiliateWithdrawal::where('status', 'rejected')->count(),
            'total_amount' => AffiliateWithdrawal::where('status', 'approved')->sum('amount'),
            'pending_amount' => AffiliateWithdrawal::where('status', 'pending')->sum('amount'),
        ];
        
        // AJAX response for live search
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.affiliate.withdrawals.partials.withdrawal-rows', compact('withdrawals'))->render(),
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
        $withdrawal = AffiliateWithdrawal::with('affiliate.user')
            ->findOrFail($id);
        
        return view('admin.affiliate.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Approve affiliate withdrawal
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($id)
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

        return redirect()->back()->with('success', 'Withdrawal approved successfully.');
    }

    /**
     * Reject affiliate withdrawal
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject($id)
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

        return redirect()->back()->with('success', 'Withdrawal rejected successfully.');
    }
    
    /**
     * Bulk action on withdrawals
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|string|in:approve,reject',
        ]);
        
        $ids = json_decode($request->get('ids'), true);
        $action = $request->get('action');
        
        $withdrawals = AffiliateWithdrawal::whereIn('id', $ids)->where('status', 'pending')->get();
        
        if ($withdrawals->isEmpty()) {
            return redirect()->back()->with('error', 'No pending withdrawals selected.');
        }
        
        DB::transaction(function () use ($withdrawals, $action) {
            foreach ($withdrawals as $withdrawal) {
                if ($action === 'approve') {
                    $withdrawal->update([
                        'status' => 'approved',
                        'processed_at' => now(),
                    ]);
                    $withdrawal->affiliate->decrement('balance', $withdrawal->amount);
                } else {
                    $withdrawal->update([
                        'status' => 'rejected',
                        'processed_at' => now(),
                    ]);
                    $affiliate = $withdrawal->affiliate;
                    $affiliate->increment('balance', $withdrawal->amount);
                    $affiliate->decrement('pending_balance', $withdrawal->amount);
                }
            }
        });
        
        $count = $withdrawals->count();
        $message = $action === 'approve' 
            ? "{$count} withdrawal(s) approved successfully."
            : "{$count} withdrawal(s) rejected successfully.";
            
        return redirect()->back()->with('success', $message);
    }
}
