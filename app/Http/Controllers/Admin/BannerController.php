<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Helpers\ImageHelper;

class BannerController extends Controller
{
    /**
     * Display a listing of the banners.
     */
    public function index(Request $request)
    {
        $query = Banner::query();

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Position filter
        if ($request->position) {
            $query->where('position', $request->position);
        }

        // Status filter
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        // Sorting
        $sort = $request->sort ?? 'sort_order';
        $direction = $request->direction ?? 'asc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 15;
        $banners = $query->paginate($perPage);

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.banners.partials.table-rows', compact('banners'))->render(),
                'pagination' => $banners->links()->toHtml(),
            ]);
        }

        $positions = Banner::getPositionOptions();

        // Calculate stats from all banners, not just paginated ones
        $allBanners = Banner::all();
        $stats = [
            'total' => $allBanners->count(),
            'active' => $allBanners->where('is_active', true)->count(),
            'inactive' => $allBanners->where('is_active', false)->count(),
        ];

        return view('admin.banners.index', compact('banners', 'positions', 'stats'));
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create()
    {
        $positions = Banner::getPositionOptions();
        return view('admin.banners.create', compact('positions'));
    }

    /**
     * Store a newly created banner in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
            'link' => 'nullable|string|max:500',
            'position' => 'required|in:' . implode(',', array_keys(Banner::getPositionOptions())),
            'description' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:50',
            'button_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();

        // Handle image upload with ImageHelper
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'banners',
                    1920,
                    300,
                    85
                );
                $data['image'] = $imageResult['path'];
                $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        // Set default sort order if not provided
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = Banner::max('sort_order') + 1;
        }

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    /**
     * Display the specified banner.
     */
    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit(Banner $banner)
    {
        $positions = Banner::getPositionOptions();
        return view('admin.banners.edit', compact('banner', 'positions'));
    }

    /**
     * Update the specified banner in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'link' => 'nullable|string|max:500',
            'position' => 'required|in:' . implode(',', array_keys(Banner::getPositionOptions())),
            'description' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:50',
            'button_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();

        // Handle image upload with ImageHelper
        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image) {
                ImageHelper::deleteImage($banner->image, $banner->thumbnail ?? null);
            }
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'banners',
                    1920,
                    300,
                    85
                );
                $data['image'] = $imageResult['path'];
                $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    /**
     * Remove the specified banner from storage.
     */
    public function destroy(Banner $banner)
    {
        // Delete image using ImageHelper
        if ($banner->image) {
            ImageHelper::deleteImage($banner->image, $banner->thumbnail ?? null);
        }

        $banner->delete();

        return back()->with('success', 'Banner deleted successfully.');
    }

    /**
     * Toggle banner status.
     */
    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $banner->is_active,
                'message' => $banner->is_active ? 'Banner activated' : 'Banner deactivated'
            ]);
        }
        
        $status = $banner->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Banner {$status} successfully.");
    }

    /**
     * Reorder banners.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:banners,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            Banner::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Banners reordered successfully.']);
    }

    /**
     * Bulk actions on banners.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:banners,id',
        ]);

        $banners = Banner::whereIn('id', $request->ids)->get();

        switch ($request->action) {
            case 'activate':
                $banners->each(function ($banner) {
                    $banner->update(['is_active' => true]);
                });
                $message = 'Selected banners have been activated.';
                break;

            case 'deactivate':
                $banners->each(function ($banner) {
                    $banner->update(['is_active' => false]);
                });
                $message = 'Selected banners have been deactivated.';
                break;

            case 'delete':
                $banners->each(function ($banner) {
                    if ($banner->image) {
                        ImageHelper::deleteImage($banner->image, $banner->thumbnail ?? null);
                    }
                    $banner->delete();
                });
                $message = 'Selected banners have been deleted.';
                break;
        }

        return back()->with('success', $message);
    }
}
