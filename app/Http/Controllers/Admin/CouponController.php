<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();
        
        // Search by code
        if ($request->search) {
            $query->where('code', 'like', "%{$request->search}%");
        }
        
        // Filter by type
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Sort — whitelisted: ?sort=nope was SQLSTATE 42S22 and ?direction=sideways a syntax error.
        $sortable = ['code', 'type', 'value', 'used_count', 'status', 'start_date', 'end_date', 'created_at'];
        $sort = in_array($request->sort, $sortable, true) ? $request->sort : 'created_at';
        $direction = strtolower((string) $request->direction) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);

        // Cards describe the rows on screen, not the whole table.
        $statsQuery = clone $query;

        $coupons = $query->paginate(15);

        // "Active" means usable: an active-status coupon whose end date passed (or whose usage limit
        // is spent) is refused by the checkout, so counting it here overstated the live coupons.
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('status', 'active')
                ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
                ->count(),
            'inactive' => (clone $statsQuery)->where('status', 'inactive')->count(),
            'expired' => (clone $statsQuery)
                ->whereNotNull('end_date')
                ->where('end_date', '<', now())
                ->count(),
        ];
        
        return view('admin.coupons.index', compact('coupons', 'stats'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,inactive',
        ]);

        // Whitelist: $request->all() let a crafted POST set `used_count` (it is fillable), so an
        // admin could burn or reset a coupon's usage with the create form.
        $data = $request->only([
            'code', 'type', 'value', 'min_order_amount', 'max_discount',
            'usage_limit', 'start_date', 'end_date', 'status',
        ]);
        $data['code'] = strtoupper($data['code']);

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only([
            'code', 'type', 'value', 'min_order_amount', 'max_discount',
            'usage_limit', 'start_date', 'end_date', 'status',
        ]);
        $data['code'] = strtoupper($data['code']);

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon deleted successfully.');
    }

    public function toggle(Coupon $coupon)
    {
        // The list hides the toggle for a coupon that is already inert; the route stays reachable, so
        // it refuses the same cases instead of writing a status that changes nothing at checkout.
        //
        // Deliberately NOT `$coupon->isValid()`: that also asserts a minimum order amount against the
        // total it is given (0 here) and would refuse to toggle every coupon that has a minimum.
        $ended = $coupon->end_date && $coupon->end_date->isPast();
        $spent = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit;

        if ($ended || $spent) {
            $reason = $ended
                ? 'expired on ' . $coupon->end_date->format('d M, Y') . '; extend the end date instead'
                : 'reached its usage limit (' . (int) $coupon->used_count . ' of ' . (int) $coupon->usage_limit . '); raise the limit instead';

            return back()->with('error', 'This coupon ' . $reason . '.');
        }

        $coupon->update([
            'status' => $coupon->status === 'active' ? 'inactive' : 'active'
        ]);
        return back()->with('success', 'Coupon status updated.');
    }
}
