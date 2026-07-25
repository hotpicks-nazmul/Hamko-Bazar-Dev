<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SortsLists;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AffiliateBanner;
use App\Models\Affiliate;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AffiliateBannerController extends Controller
{
    use SortsLists;

    /**
     * Display list of affiliate banners
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = AffiliateBanner::with('affiliate.user');

        // Search functionality
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('size', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Sorting — the column must exist on this table and the direction is one of two words.
        $query->orderBy(
            $this->sortColumn($request, ['id', 'name', 'width', 'height', 'clicks', 'created_at'], 'created_at'),
            $this->sortDirection($request)
        );

        $banners = $query->paginate($this->perPage($request));

        // Statistics for stat cards
        $stats = [
            'total' => AffiliateBanner::count(),
            'active' => AffiliateBanner::where('status', 'active')->count(),
            'inactive' => AffiliateBanner::where('status', 'inactive')->count(),
            'total_clicks' => AffiliateBanner::sum('clicks'),
        ];

        // AJAX response for live search
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.affiliate.banners.partials.banner-rows', compact('banners'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('admin.affiliate.banners.index', compact('banners', 'stats'));
    }

    /**
     * Show form to create new affiliate banner
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $affiliates = Affiliate::with('user')
            ->where('status', 'approved')
            ->get();
        
        return view('admin.affiliate.banners.create', compact('affiliates'));
    }

    /**
     * Store new affiliate banner
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'affiliate_id' => 'nullable|exists:affiliates,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'target_url' => 'nullable|url',
            'size' => 'nullable|string',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle image upload using ImageHelper
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'affiliate-banners',
                    1920,
                    300,
                    85
                );
                
                $validated['image'] = $imageResult['path'];
                $validated['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        $banner = AffiliateBanner::create($validated);

        ActivityLog::adminLog('Affiliate banner created', $banner, auth()->user(), [
            'affiliate_id' => $banner->affiliate_id,
            'image' => $banner->image,
            'size' => $banner->size,
        ]);

        return redirect()->route('admin.affiliate.banners.index')
            ->with('success', 'Affiliate banner created successfully.');
    }

    /**
     * Show form to edit affiliate banner
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $banner = AffiliateBanner::findOrFail($id);
        $affiliates = Affiliate::with('user')
            ->where('status', 'approved')
            ->get();
        
        return view('admin.affiliate.banners.edit', compact('banner', 'affiliates'));
    }

    /**
     * Update affiliate banner
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $banner = AffiliateBanner::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'affiliate_id' => 'nullable|exists:affiliates,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'target_url' => 'nullable|url',
            'size' => 'nullable|string',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle image upload using ImageHelper
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                // Delete old image
                if ($banner->image) {
                    ImageHelper::deleteImage($banner->image, $banner->thumbnail ?? null);
                }
                
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'affiliate-banners',
                    1920,
                    300,
                    85
                );
                
                $validated['image'] = $imageResult['path'];
                $validated['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        $from = ['status' => $banner->status, 'image' => $banner->image];
        $banner->update($validated);

        ActivityLog::adminLog('Affiliate banner updated', $banner, auth()->user(), [
            'from' => $from,
            'to' => ['status' => $banner->status, 'image' => $banner->image],
        ]);

        return redirect()->route('admin.affiliate.banners.index')
            ->with('success', 'Affiliate banner updated successfully.');
    }

    /**
     * Delete affiliate banner
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $banner = AffiliateBanner::findOrFail($id);

        // Delete image using ImageHelper
        if ($banner->image) {
            ImageHelper::deleteImage($banner->image, $banner->thumbnail ?? null);
        }

        $name = $banner->name;
        $banner->delete();

        ActivityLog::adminLog('Affiliate banner deleted', null, auth()->user(), [
            'banner_id' => $id,
            'name' => $name,
        ]);

        return redirect()->back()
            ->with('success', 'Affiliate banner deleted successfully.');
    }

    /**
     * Bulk action for banners
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required',
        ]);

        // The page posts JSON.stringify([...]); accept a real array too.
        $raw = $request->input('ids');
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : explode(',', $raw);
        }
        $ids = is_array($raw) ? array_values(array_filter(array_map('intval', $raw))) : [];
        $action = $request->action;

        ActivityLog::adminLog('Affiliate banners bulk action', null, auth()->user(), [
            'action' => $action,
            'ids' => $ids,
        ]);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No banners selected.');
        }

        $banners = AffiliateBanner::whereIn('id', $ids)->get();

        switch ($action) {
            case 'activate':
                $banners->each->update(['status' => 'active']);
                return redirect()->back()->with('success', 'Selected banners activated.');

            case 'deactivate':
                $banners->each->update(['status' => 'inactive']);
                return redirect()->back()->with('success', 'Selected banners deactivated.');

            case 'delete':
                // Delete images and banners
                $banners->each(function ($banner) {
                    if ($banner->image) {
                        ImageHelper::deleteImage($banner->image, $banner->thumbnail ?? null);
                    }
                    $banner->delete();
                });
                return redirect()->back()->with('success', 'Selected banners deleted.');
        }

        return redirect()->back()->with('error', 'Invalid action.');
    }
}
