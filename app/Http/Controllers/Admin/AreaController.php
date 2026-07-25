<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\City;
use App\Models\Setting;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::with('city.countryRelation')->ordered();

        $checkoutMode = Setting::get('checkout_mode', 'local');
        $defaultCountryId = Setting::get('default_country', '');
        if ($checkoutMode === 'local' && $defaultCountryId && !$request->city_id) {
            $cityIds = City::where('country_id', $defaultCountryId)->pluck('id');
            $query->whereIn('city_id', $cityIds);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $sort = $request->sort ?? 'sort_order';
        $direction = $request->direction ?? 'asc';
        $query->orderBy($sort, $direction);

        $perPage = $request->per_page ?? 25;
        $areas = $query->paginate($perPage);

        $cityQuery = City::active()->ordered();
        if ($checkoutMode === 'local' && $defaultCountryId) {
            $cityQuery->where('country_id', $defaultCountryId);
        }
        $cities = $cityQuery->get();

        $areaBase = Area::query();
        if ($checkoutMode === 'local' && $defaultCountryId) {
            $areaBase->whereIn('city_id', City::where('country_id', $defaultCountryId)->pluck('id'));
        }
        $stats = [
            'total' => (clone $areaBase)->count(),
            'active' => (clone $areaBase)->where('is_active', true)->count(),
            'inactive' => (clone $areaBase)->where('is_active', false)->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.locations.areas.partials.table-rows', compact('areas'))->render(),
                'pagination' => $areas->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.locations.areas.index', compact('areas', 'cities', 'stats'));
    }

    public function create()
    {
        $cities = City::active()->ordered()->get();
        return view('admin.locations.areas.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'city_id' => 'required|exists:cities,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        Area::create($request->all());

        return redirect()->route('admin.locations.areas.index')
            ->with('success', 'Area created successfully.');
    }

    public function edit(Area $area)
    {
        $cities = City::active()->ordered()->get();
        return view('admin.locations.areas.edit', compact('area', 'cities'));
    }

    public function update(Request $request, Area $area)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'city_id' => 'required|exists:cities,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $area->update($request->all());

        return redirect()->route('admin.locations.areas.index')
            ->with('success', 'Area updated successfully.');
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return redirect()->route('admin.locations.areas.index')
            ->with('success', 'Area deleted successfully.');
    }

    public function getAreas(Request $request)
    {
        $query = Area::active()->ordered();

        $cityIds = null;
        if ($request->filled('city_ids') && is_array($request->city_ids)) {
            $cityIds = $request->city_ids;
        } elseif ($request->filled('city_id') && is_array($request->city_id)) {
            $cityIds = $request->city_id;
        } elseif ($request->filled('city_id') && !is_array($request->city_id)) {
            $cityIds = [$request->city_id];
        }

        if ($cityIds && count($cityIds) > 0) {
            $query->whereIn('city_id', $cityIds);
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return response()->json([
            'success' => true,
            'areas' => $query->get(['id', 'name', 'city_id']),
        ]);
    }

    public function toggleStatus(Area $area)
    {
        $area->update(['is_active' => !$area->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Area status updated.',
            'is_active' => $area->is_active,
        ]);
    }
}
