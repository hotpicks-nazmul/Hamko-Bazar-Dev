<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\City;
use App\Models\Area;
use App\Models\Setting;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request)
    {
        $query = Warehouse::query();

        // Search by name, code, city, or phone
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        // Filter by city
        if ($request->city) {
            $query->where('city', $request->city);
        }

        // Sorting
        $sort = $request->sort ?? 'sort_order';
        $direction = $request->direction ?? 'asc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $warehouses = $query->paginate($perPage);

        // Get cities for filter
        $cities = Warehouse::distinct()->pluck('city')->sort()->values();

        // Get stats
        $stats = [
            'total' => Warehouse::count(),
            'active' => Warehouse::where('is_active', true)->count(),
            'inactive' => Warehouse::where('is_active', false)->count(),
            'cities' => Warehouse::distinct()->count('city'),
        ];

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.warehouses.partials.table-rows', compact('warehouses'))->render(),
                'pagination' => $warehouses->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.warehouses.index', compact('warehouses', 'cities', 'stats'));
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create()
    {
        $checkoutMode = Setting::get('checkout_mode', 'local');
        $defaultCountryId = Setting::get('default_country', '');
        $defaultCountry = $defaultCountryId ? Country::find($defaultCountryId)?->name : null;
        $countries = Country::ordered()->get();
        $autoCode = Warehouse::generateCode();

        return view('admin.warehouses.create', compact('checkoutMode', 'defaultCountry', 'countries', 'autoCode'));
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'city_ids' => 'required|array|min:1',
            'city_ids.*' => 'required|integer|exists:cities,id',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Validate that none of the selected cities are already assigned to another warehouse
        $alreadyTaken = DB::table('warehouse_city')
            ->whereIn('city_id', $request->city_ids)
            ->exists();
        if ($alreadyTaken) {
            $takenCityNames = City::whereIn('id', function ($q) use ($request) {
                $q->select('city_id')->from('warehouse_city')->whereIn('city_id', $request->city_ids);
            })->pluck('name')->implode(', ');
            return back()->withInput()->withErrors([
                'city_ids' => "Some cities are already assigned to another warehouse: $takenCityNames",
            ]);
        }

        if ($request->is_primary) {
            Warehouse::where('is_primary', true)->update(['is_primary' => false]);
        }

        $warehouseData = $request->except(['city_ids', 'area']);
        $warehouseData['area'] = '';
        $warehouse = Warehouse::create($warehouseData);

        if (empty($warehouse->code)) {
            $warehouse->update(['code' => Warehouse::generateCode()]);
        }

        // Ensure checkbox defaults
        if (!$request->has('is_active')) {
            $warehouse->update(['is_active' => false]);
        }
        if (!$request->has('is_primary')) {
            $warehouse->update(['is_primary' => false]);
        }

        // Sync city pivot and auto-populate area pivot
        $warehouse->cities()->sync($request->city_ids);
        $areaIds = Area::whereIn('city_id', $request->city_ids)->pluck('id');
        $warehouse->areas()->sync($areaIds);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function show($id)
    {
        $warehouse = Warehouse::with('cities')->findOrFail($id);
        return view('admin.warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the warehouse.
     */
    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $checkoutMode = \App\Models\Setting::get('checkout_mode', 'local');
        $defaultCountryId = \App\Models\Setting::get('default_country', '');
        $defaultCountry = $defaultCountryId ? \App\Models\Country::find($defaultCountryId)?->name : null;
        $countries = \App\Models\Country::ordered()->get();

        return view('admin.warehouses.edit', compact('warehouse', 'checkoutMode', 'defaultCountry', 'countries'));
    }

    /**
     * Update the specified warehouse.
     */
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'city_ids' => 'required|array|min:1',
            'city_ids.*' => 'required|integer|exists:cities,id',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Validate that none of the selected cities are already assigned to another warehouse
        $alreadyTaken = DB::table('warehouse_city')
            ->whereIn('city_id', $request->city_ids)
            ->where('warehouse_id', '!=', $warehouse->id)
            ->exists();
        if ($alreadyTaken) {
            $takenCityNames = City::whereIn('id', function ($q) use ($request, $warehouse) {
                $q->select('city_id')->from('warehouse_city')
                  ->whereIn('city_id', $request->city_ids)
                  ->where('warehouse_id', '!=', $warehouse->id);
            })->pluck('name')->implode(', ');
            return back()->withInput()->withErrors([
                'city_ids' => "Some cities are already assigned to another warehouse: $takenCityNames",
            ]);
        }

        if ($request->is_primary && !$warehouse->is_primary) {
            Warehouse::where('is_primary', true)->update(['is_primary' => false]);
        }

        $warehouseData = $request->except(['city_ids', 'area']);
        $warehouseData['area'] = ''; // No longer manually selected
        $warehouse->update($warehouseData);

        // Ensure checkbox defaults
        if (!$request->has('is_active')) {
            $warehouse->update(['is_active' => false]);
        }
        if (!$request->has('is_primary')) {
            $warehouse->update(['is_primary' => false]);
        }

        // Sync city pivot and auto-populate area pivot
        $warehouse->cities()->sync($request->city_ids);
        $areaIds = Area::whereIn('city_id', $request->city_ids)->pluck('id');
        $warehouse->areas()->sync($areaIds);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }

    /**
     * Toggle the active status.
     */
    public function toggleStatus($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update(['is_active' => !$warehouse->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'is_active' => $warehouse->is_active
        ]);
    }

    /**
     * Get all cities with their warehouse assignment status.
     */
    public function getCities()
    {
        $checkoutMode = Setting::get('checkout_mode', 'local');
        $defaultCountryId = Setting::get('default_country', '');

        $query = City::active()->ordered()->with('state');

        if ($checkoutMode === 'local' && $defaultCountryId) {
            $query->where('country_id', $defaultCountryId);
        }

        $cities = $query->get(['id', 'name', 'state_id']);

        $cities->load(['warehouses' => function ($q) {
            $q->select('warehouses.id', 'warehouses.name');
        }]);

        $result = $cities->map(function ($city) {
            $assignedTo = null;
            if ($city->warehouses->isNotEmpty()) {
                $wh = $city->warehouses->first();
                $assignedTo = [
                    'id' => $wh->id,
                    'name' => $wh->name,
                ];
            }
            return [
                'id' => $city->id,
                'name' => $city->name,
                'state_id' => $city->state_id,
                'state_name' => $city->state?->name ?? 'Unknown',
                'assigned_to' => $assignedTo,
            ];
        });

        return response()->json([
            'success' => true,
            'cities' => $result,
        ]);
    }

    /**
     * Get warehouses for AJAX requests.
     */
    public function getWarehouses(Request $request)
    {
        $query = Warehouse::active();

        if ($request->city) {
            $query->where('city', $request->city);
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $warehouses = $query->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'warehouses' => $warehouses
        ]);
    }

    /**
     * Handle bulk actions on warehouses.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|string',
        ]);

        $ids = json_decode($request->ids, true);

        if (empty($ids)) {
            return back()->with('error', 'No warehouses selected.');
        }

        $warehouses = Warehouse::whereIn('id', $ids)->get();

        switch ($request->action) {
            case 'activate':
                Warehouse::whereIn('id', $ids)->update(['is_active' => true]);
                $message = $warehouses->count() . ' warehouse(s) activated successfully.';
                break;

            case 'deactivate':
                Warehouse::whereIn('id', $ids)->update(['is_active' => false]);
                $message = $warehouses->count() . ' warehouse(s) deactivated successfully.';
                break;

            case 'delete':
                foreach ($warehouses as $warehouse) {
                    $warehouse->delete();
                }
                $message = $warehouses->count() . ' warehouse(s) deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Display orders for a warehouse.
     */
    public function orders(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        $query = \App\Models\Order::where('warehouse_id', $id)
            ->with('user', 'items');

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('billing_first_name', 'like', "%{$search}%")
                  ->orWhere('billing_last_name', 'like', "%{$search}%")
                  ->orWhere('billing_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('admin.warehouses.orders', compact('warehouse', 'orders'));
    }

    /**
     * Display picking page for warehouse.
     */
    public function picking(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        $query = \App\Models\Order::where('warehouse_id', $id)
            ->whereIn('status', ['processing', 'confirmed', 'ready_to_pick', 'picking', 'packed'])
            ->where('payment_status', 'paid')
            ->with('user', 'items');

        // Filter by picking status
        if ($request->filter === 'ready') {
            $query->whereIn('status', ['processing', 'confirmed']);
        } elseif ($request->filter === 'picking') {
            $query->where('status', 'picking');
        } elseif ($request->filter === 'packed') {
            $query->where('status', 'packed');
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('billing_first_name', 'like', "%{$search}%")
                  ->orWhere('billing_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderByRaw("FIELD(status, 'processing', 'confirmed', 'ready_to_pick', 'picking', 'packed')")
            ->orderBy('created_at', 'asc')
            ->paginate(25);

        return view('admin.warehouses.picking', compact('warehouse', 'orders'));
    }

    /**
     * Start picking an order.
     */
    public function startPicking(Request $request, $warehouseId, $orderId)
    {
        $order = \App\Models\Order::where('warehouse_id', $warehouseId)->findOrFail($orderId);

        if (!in_array($order->status, ['processing', 'confirmed', 'ready_to_pick'])) {
            return back()->with('error', 'Order cannot be picked in current status.');
        }

        if ($order->payment_status !== 'paid') {
            return back()->with('error', 'Order must be paid before picking.');
        }

        $order->update([
            'status' => 'picking',
            'picking_started_at' => now(),
            'picked_by' => auth()->id(),
        ]);

        return back()->with('success', 'Picking started for order ' . $order->order_number);
    }

    /**
     * Mark an order as packed.
     */
    public function markPacked(Request $request, $warehouseId, $orderId)
    {
        $order = \App\Models\Order::where('warehouse_id', $warehouseId)->findOrFail($orderId);

        if ($order->status !== 'picking') {
            return back()->with('error', 'Order must be in picking status to mark as packed.');
        }

        $order->update([
            'status' => 'packed',
            'packed_at' => now(),
            'packed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Order ' . $order->order_number . ' marked as packed.');
    }
}
