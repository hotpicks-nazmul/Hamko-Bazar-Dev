<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use App\Models\Store;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Helpers\ImageHelper;

class StoreController extends Controller
{
    use SortsLists;

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

        // Sorting and paging. `?sort=nope`, `?direction=sideways` and `?per_page=abc` were three separate
        // 500s on this list (SQLSTATE 42S22, InvalidArgumentException, and a TypeError in paginate()).
        $sort = $this->sortColumn($request, ['name', 'slug', 'code', 'city', 'email', 'phone', 'is_active',
            'is_default', 'is_physical', 'sort_order', 'created_at'], 'sort_order');
        $direction = $this->sortDirection($request, 'asc');
        $query->orderBy($sort, $direction);

        $stores = $query->paginate($this->perPage($request, 25, 100));

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

        // Named fields only: `except(['logo','favicon','banner'])` forwarded every other request key to
        // Store::create(), and the model's $fillable decides what that meant.
        $data = $request->only(['name', 'slug', 'code', 'description', 'email', 'phone', 'address', 'city',
            'state', 'postcode', 'country', 'latitude', 'longitude', 'opening_hours', 'primary_color',
            'secondary_color', 'meta_title', 'meta_description', 'meta_keywords', 'contact_person_name',
            'contact_person_phone', 'contact_person_email', 'is_active', 'is_default', 'is_physical',
            'sort_order']);

        // An unchecked switch posts nothing at all, and the columns default to true — so "inactive" and
        // "not a physical branch" were impossible to save. The absence of the key now means false.
        $data['is_active'] = $request->boolean('is_active');
        $data['is_physical'] = $request->boolean('is_physical');

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

        $data = $request->only(['name', 'slug', 'code', 'description', 'email', 'phone', 'address', 'city',
            'state', 'postcode', 'country', 'latitude', 'longitude', 'opening_hours', 'primary_color',
            'secondary_color', 'meta_title', 'meta_description', 'meta_keywords', 'contact_person_name',
            'contact_person_phone', 'contact_person_email', 'is_active', 'is_default', 'is_physical',
            'sort_order']);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_physical'] = $request->boolean('is_physical');

        // Generate slug from name if empty
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle default store. Unchecking the box on the current default used to leave ZERO default
        // stores, and every reader then fell back to `active()->first()` — an unordered row. The shop's
        // "current store" would silently change to whichever store MySQL returned first.
        if ($request->is_default && !$store->is_default) {
            Store::where('is_default', true)->update(['is_default' => false]);
            $data['is_default'] = true;
        } elseif (! $request->is_default && $store->is_default) {
            $otherDefault = Store::where('id', '!=', $store->id)->where('is_default', true)->exists();

            if (! $otherDefault) {
                return redirect()->back()->withInput()->with('error',
                    'This is the default store. Make another store the default first — the shop always needs exactly one.');
            }

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
        // The bulk action already refuses to deactivate the default store (`where('is_default', false)`),
        // but the single toggle allowed it — and `Store::getDefault()` then still returned that inactive
        // row, so the storefront served a closed store while the switcher listed none.
        if ($store->is_active && $store->is_default) {
            $message = 'The default store cannot be deactivated. Make another store the default first.';

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('admin.multi-store.index')->with('error', $message);
        }

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

    // bulkAction() removed with its route — the list has no bulk controls (see routes/admin.php). The
    // per-record activate/deactivate/delete paths remain, and all three enforce the default-store rules.

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
