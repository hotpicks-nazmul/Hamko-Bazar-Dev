<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Helpers\ImageHelper;

class StoreController extends Controller
{
    /**
     * Display a listing of stores.
     */
    public function index(Request $request)
    {
        $query = Store::query()->withCount('products');

        // Search by name, code, city, or email
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        // Filter by type
        if ($request->type === 'physical') {
            $query->where('is_physical', true);
        } elseif ($request->type === 'online') {
            $query->where('is_physical', false);
        }

        // Sorting
        $sort = $request->sort ?? 'sort_order';
        $direction = $request->direction ?? 'asc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $stores = $query->paginate($perPage);

        // Get cities for filter
        $cities = Store::distinct()->pluck('city')->sort()->filter()->values();

        // Get stats
        $stats = [
            'total' => Store::count(),
            'active' => Store::where('is_active', true)->count(),
            'inactive' => Store::where('is_active', false)->count(),
            'physical' => Store::where('is_physical', true)->count(),
            'online' => Store::where('is_physical', false)->count(),
        ];

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.stores.partials.table-rows', compact('stores'))->render(),
                'pagination' => $stores->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.stores.index', compact('stores', 'cities', 'stats'));
    }

    /**
     * Show the form for creating a new store.
     */
    public function create()
    {
        return view('admin.stores.create');
    }

    /**
     * Store a newly created store.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:stores,slug',
            'code' => 'nullable|string|max:50|unique:stores,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'banner' => 'nullable|image|max:5120',
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_physical' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->except(['logo', 'favicon', 'banner']);
        
        // Generate slug from name if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle default store
        if ($request->is_default) {
            Store::where('is_default', true)->update(['is_default' => false]);
        }

        // If this is the first store, make it default
        if (!Store::exists()) {
            $data['is_default'] = true;
        }

        // Process logo image
        if ($request->hasFile('logo')) {
            if (ImageHelper::isValidImage($request->file('logo'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('logo'),
                    'stores/logos',
                    400,
                    100,
                    85
                );
                $data['logo'] = $imageResult['path'];
            }
        }

        // Process favicon
        if ($request->hasFile('favicon')) {
            if (ImageHelper::isValidImage($request->file('favicon'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('favicon'),
                    'stores/favicons',
                    64,
                    32,
                    90
                );
                $data['favicon'] = $imageResult['path'];
            }
        }

        // Process banner
        if ($request->hasFile('banner')) {
            if (ImageHelper::isValidImage($request->file('banner'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('banner'),
                    'stores/banners',
                    1920,
                    400,
                    85
                );
                $data['banner'] = $imageResult['path'];
            }
        }

        $store = Store::create($data);

        return redirect()->route('admin.multi-store.index')
            ->with('success', 'Store created successfully!');
    }

    /**
     * Display the specified store.
     */
    public function show(Store $store)
    {
        $store->loadCount('products');
        return view('admin.stores.show', compact('store'));
    }

    /**
     * Show the form for editing the specified store.
     */
    public function edit(Store $store)
    {
        return view('admin.stores.edit', compact('store'));
    }

    /**
     * Update the specified store.
     */
    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('stores', 'slug')->ignore($store->id)],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('stores', 'code')->ignore($store->id)],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'banner' => 'nullable|image|max:5120',
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_physical' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->except(['logo', 'favicon', 'banner']);

        // Generate slug from name if empty
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle default store
        if ($request->is_default && !$store->is_default) {
            Store::where('is_default', true)->update(['is_default' => false]);
            $data['is_default'] = true;
        } elseif (!$request->is_default && $store->is_default) {
            $data['is_default'] = false;
        }

        // Process logo image
        if ($request->hasFile('logo')) {
            if (ImageHelper::isValidImage($request->file('logo'))) {
                // Delete old logo
                if ($store->logo) {
                    ImageHelper::deleteImage($store->logo);
                }
                $imageResult = ImageHelper::processImage(
                    $request->file('logo'),
                    'stores/logos',
                    400,
                    100,
                    85
                );
                $data['logo'] = $imageResult['path'];
            }
        }

        // Process favicon
        if ($request->hasFile('favicon')) {
            if (ImageHelper::isValidImage($request->file('favicon'))) {
                // Delete old favicon
                if ($store->favicon) {
                    ImageHelper::deleteImage($store->favicon);
                }
                $imageResult = ImageHelper::processImage(
                    $request->file('favicon'),
                    'stores/favicons',
                    64,
                    32,
                    90
                );
                $data['favicon'] = $imageResult['path'];
            }
        }

        // Process banner
        if ($request->hasFile('banner')) {
            if (ImageHelper::isValidImage($request->file('banner'))) {
                // Delete old banner
                if ($store->banner) {
                    ImageHelper::deleteImage($store->banner);
                }
                $imageResult = ImageHelper::processImage(
                    $request->file('banner'),
                    'stores/banners',
                    1920,
                    400,
                    85
                );
                $data['banner'] = $imageResult['path'];
            }
        }

        $store->update($data);

        return redirect()->route('admin.multi-store.index')
            ->with('success', 'Store updated successfully!');
    }

    /**
     * Remove the specified store.
     */
    public function destroy(Store $store)
    {
        // Prevent deleting if store has products
        $productCount = $store->products()->count();
        if ($productCount > 0) {
            return redirect()->route('admin.multi-store.index')
                ->with('error', "Cannot delete store! This store has {$productCount} product(s) assigned to it. Please remove or reassign products first.");
        }

        // Prevent deleting the default store if it's the only one
        if ($store->is_default && Store::count() === 1) {
            return redirect()->route('admin.multi-store.index')
                ->with('error', 'Cannot delete the default store!');
        }

        // If deleting default store, make another store default
        if ($store->is_default) {
            Store::where('id', '!=', $store->id)->first()?->update(['is_default' => true]);
        }

        // Delete associated images
        if ($store->logo) {
            ImageHelper::deleteImage($store->logo);
        }
        if ($store->favicon) {
            ImageHelper::deleteImage($store->favicon);
        }
        if ($store->banner) {
            ImageHelper::deleteImage($store->banner);
        }

        $store->delete();

        return redirect()->route('admin.multi-store.index')
            ->with('success', 'Store deleted successfully!');
    }

    /**
     * Toggle store status.
     */
    public function toggleStatus(Store $store)
    {
        $store->update(['is_active' => !$store->is_active]);

        $status = $store->is_active ? 'activated' : 'deactivated';
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Store {$status} successfully!",
                'is_active' => $store->is_active,
            ]);
        }

        return redirect()->route('admin.multi-store.index')
            ->with('success', "Store {$status} successfully!");
    }

    /**
     * Set store as default.
     */
    public function setDefault(Store $store)
    {
        Store::setAsDefault($store->id);

        return redirect()->route('admin.multi-store.index')
            ->with('success', 'Default store updated successfully!');
    }

    /**
     * Bulk action on stores.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                Store::whereIn('id', $ids)->update(['is_active' => true]);
                $message = 'Stores activated successfully!';
                break;
            case 'deactivate':
                // Don't deactivate default store
                Store::whereIn('id', $ids)->where('is_default', false)->update(['is_active' => false]);
                $message = 'Stores deactivated successfully!';
                break;
            case 'delete':
                // Don't delete default stores or stores with products
                $stores = Store::whereIn('id', $ids)->where('is_default', false)->get();
                $deletedCount = 0;
                $skippedWithProducts = 0;
                foreach ($stores as $store) {
                    if ($store->products()->count() > 0) {
                        $skippedWithProducts++;
                        continue;
                    }
                    if ($store->is_default && Store::count() > 1) {
                        Store::where('id', '!=', $store->id)->first()?->update(['is_default' => true]);
                    }
                    // Delete associated images
                    if ($store->logo) {
                        ImageHelper::deleteImage($store->logo);
                    }
                    if ($store->favicon) {
                        ImageHelper::deleteImage($store->favicon);
                    }
                    if ($store->banner) {
                        ImageHelper::deleteImage($store->banner);
                    }
                    $store->delete();
                    $deletedCount++;
                }
                if ($skippedWithProducts > 0) {
                    $message = "{$deletedCount} store(s) deleted. {$skippedWithProducts} store(s) with products were skipped.";
                } else {
                    $message = "{$deletedCount} store(s) deleted successfully!";
                }
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Invalid action!'], 400);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('admin.multi-store.index')->with('success', $message);
    }

    /**
     * Get stores for API/ AJAX.
     */
    public function getStores(Request $request)
    {
        $stores = Store::active()->ordered();
        
        if ($request->has('physical')) {
            $stores->where('is_physical', $request->boolean('physical'));
        }
        
        $stores = $stores->get();
        
        return response()->json([
            'success' => true,
            'stores' => $stores
        ]);
    }
}
