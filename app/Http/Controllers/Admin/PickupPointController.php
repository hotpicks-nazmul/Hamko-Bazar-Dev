<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PickupPoint;
use App\Rules\Phone;

class PickupPointController extends Controller
{
    /**
     * Display a listing of pickup points.
     */
    public function index(Request $request)
    {
        $query = PickupPoint::query();

        // Search by name, city, or code
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
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
        $pickupPoints = $query->paginate($perPage);

        // Get cities for filter
        $cities = PickupPoint::distinct()->pluck('city')->sort()->values();

        // Get stats
        $stats = [
            'total' => PickupPoint::count(),
            'active' => PickupPoint::where('is_active', true)->count(),
            'inactive' => PickupPoint::where('is_active', false)->count(),
        ];

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.pickup-points.partials.table-rows', compact('pickupPoints'))->render(),
                'pagination' => $pickupPoints->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.pickup-points.index', compact('pickupPoints', 'cities', 'stats'));
    }

    /**
     * Show the form for creating a new pickup point.
     */
    public function create()
    {
        return view('admin.pickup-points.create');
    }

    /**
     * Store a newly created pickup point.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:pickup_points,code',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:20', new Phone],
            'email' => 'nullable|email|max:255',
            'opening_hours' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        PickupPoint::create($request->all());

        return redirect()->route('admin.pickup-points.index')
            ->with('success', 'Pick-up point created successfully.');
    }

    /**
     * Display the specified pickup point.
     */
    public function show(PickupPoint $pickupPoint)
    {
        $pickupPoint->load(['orders' => function($q) {
            $q->latest()->limit(10);
        }]);

        return view('admin.pickup-points.show', compact('pickupPoint'));
    }

    /**
     * Show the form for editing the pickup point.
     */
    public function edit(PickupPoint $pickupPoint)
    {
        return view('admin.pickup-points.edit', compact('pickupPoint'));
    }

    /**
     * Update the specified pickup point.
     */
    public function update(Request $request, PickupPoint $pickupPoint)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:pickup_points,code,' . $pickupPoint->id,
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:20', new Phone],
            'email' => 'nullable|email|max:255',
            'opening_hours' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $pickupPoint->update($request->all());

        return redirect()->route('admin.pickup-points.index')
            ->with('success', 'Pick-up point updated successfully.');
    }

    /**
     * Remove the specified pickup point.
     */
    public function destroy(PickupPoint $pickupPoint)
    {
        // Check if there are any orders associated with this pickup point
        if ($pickupPoint->orders()->count() > 0) {
            return back()->with('error', 'Cannot delete pick-up point with associated orders.');
        }

        $pickupPoint->delete();

        return redirect()->route('admin.pickup-points.index')
            ->with('success', 'Pick-up point deleted successfully.');
    }

    /**
     * Toggle the active status.
     */
    public function toggleStatus(PickupPoint $pickupPoint)
    {
        $pickupPoint->update(['is_active' => !$pickupPoint->is_active]);

        return back()->with('success', 'Status updated successfully.');
    }

    /**
     * Get pickup points for AJAX requests.
     */
    public function getPickupPoints(Request $request)
    {
        $query = PickupPoint::active();

        if ($request->city) {
            $query->where('city', $request->city);
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $pickupPoints = $query->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'pickup_points' => $pickupPoints
        ]);
    }
}
