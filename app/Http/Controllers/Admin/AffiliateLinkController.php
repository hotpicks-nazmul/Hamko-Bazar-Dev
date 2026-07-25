<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateLink;
use App\Models\AffiliateProduct;
use App\Models\Affiliate;
use Illuminate\Http\Request;

class AffiliateLinkController extends Controller
{
    /**
     * Display list of affiliate links
     * 
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = AffiliateLink::with(['affiliate.user', 'product']);
        
        // Search functionality
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('affiliate_code', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        
        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $allowedSorts = ['id', 'name', 'clicks', 'conversions', 'created_at'];
        
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $links = $query->paginate($perPage);
        
        // Statistics for stat cards
        $stats = [
            'total' => AffiliateLink::count(),
            'active' => AffiliateLink::where('status', 'active')->count(),
            'inactive' => AffiliateLink::where('status', 'inactive')->count(),
            'total_clicks' => AffiliateLink::sum('clicks'),
            'total_conversions' => AffiliateLink::sum('conversions'),
        ];
        
        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.affiliate.links.partials.link-rows', compact('links'))->render(),
            ]);
        }
        
        return view('admin.affiliate.links.index', compact('links', 'stats'));
    }

    /**
     * Show form to create new affiliate link
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $affiliates = Affiliate::with('user')
            ->where('status', 'approved')
            ->get();
        $products = AffiliateProduct::where('status', 'active')->get();
        
        return view('admin.affiliate.links.create', compact('affiliates', 'products'));
    }

    /**
     * Store new affiliate link
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'affiliate_id' => 'required|exists:affiliates,id',
            'product_id' => 'nullable|exists:affiliate_products,id',
            'name' => 'required|string|max:255',
            'affiliate_code' => 'nullable|string|max:50|unique:affiliate_links,affiliate_code',
            'description' => 'nullable|string',
            'target_url' => 'nullable|url',
            'status' => 'required|in:active,inactive',
        ]);

        AffiliateLink::create($validated);

        return redirect()->route('admin.affiliate.links.index')
            ->with('success', 'Affiliate link created successfully.');
    }

    /**
     * Show form to edit affiliate link
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $link = AffiliateLink::findOrFail($id);
        $affiliates = Affiliate::with('user')
            ->where('status', 'approved')
            ->get();
        $products = AffiliateProduct::where('status', 'active')->get();
        
        return view('admin.affiliate.links.edit', compact('link', 'affiliates', 'products'));
    }

    /**
     * Update affiliate link
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $link = AffiliateLink::findOrFail($id);

        $validated = $request->validate([
            'affiliate_id' => 'required|exists:affiliates,id',
            'product_id' => 'nullable|exists:affiliate_products,id',
            'name' => 'required|string|max:255',
            'affiliate_code' => 'nullable|string|max:50|unique:affiliate_links,affiliate_code,' . $id,
            'description' => 'nullable|string',
            'target_url' => 'nullable|url',
            'status' => 'required|in:active,inactive',
        ]);

        $link->update($validated);

        return redirect()->route('admin.affiliate.links.index')
            ->with('success', 'Affiliate link updated successfully.');
    }

    /**
     * Delete affiliate link
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $link = AffiliateLink::findOrFail($id);
        $link->delete();

        return redirect()->back()
            ->with('success', 'Affiliate link deleted successfully.');
    }
}
