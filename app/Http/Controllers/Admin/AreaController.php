<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Area;
use App\Models\City;
use App\Models\Setting;

class AreaController extends Controller
{
    use SortsLists;

    public function index(Request $request)
    {
        $query = Area::with('city.countryRelation')->ordered();

        $checkoutMode = Setting::get('checkout_mode', 'local');
        $defaultCountryId = Setting::defaultCountryId();
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

        $query->orderBy(
            $this->sortColumn($request, ['sort_order', 'name', 'is_active', 'created_at'], 'sort_order'),
            $this->sortDirection($request, 'asc')
        );

        $areas = $query->paginate($this->perPage($request, 25, 100));

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
        $data = $this->validated($request);

        // areas_slug_city_id_unique: the same area name twice in one city answered SQLSTATE 23000
        // as a 500 page.
        $this->ensureNameIsFree($data);

        Area::create([
            'name' => $data['name'],
            'city_id' => $data['city_id'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.locations.areas.index')
            ->with('success', 'Area created successfully.');
    }

    public function edit(Area $area)
    {
        // Only active cities used to be offered, so an area whose city had been deactivated rendered
        // with its own city missing from the select: the required field could not be submitted
        // unchanged, and any save silently re-pointed the area at whichever city the browser picked.
        $cities = City::active()->orWhere('id', $area->city_id)->ordered()->get();

        return view('admin.locations.areas.edit', compact('area', 'cities'));
    }

    public function update(Request $request, Area $area)
    {
        $data = $this->validated($request);

        $this->ensureNameIsFree($data, $area->id);

        $area->update([
            'name' => $data['name'],
            'city_id' => $data['city_id'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.locations.areas.index')
            ->with('success', 'Area updated successfully.');
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return redirect()->route('admin.locations.areas.index')
            ->with('success', 'Area deleted successfully.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'city_id' => 'required|exists:cities,id',
            'is_active' => 'nullable',   // read with $request->boolean(): '1', 'on' and absent all work
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    protected function ensureNameIsFree(array $data, ?int $ignoreId = null): void
    {
        $cityName = City::find($data['city_id'])?->name;
        $slug = Str::slug(trim($data['name'] . ' ' . ($cityName ?? '')));

        $query = Area::where('slug', $slug)->where('city_id', $data['city_id']);
        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => 'An area with this name already exists in that city.',
            ]);
        }
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
