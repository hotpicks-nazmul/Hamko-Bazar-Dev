<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use App\Models\State;
use App\Models\Setting;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::ordered();

        $checkoutMode = Setting::get('checkout_mode', 'local');
        if ($checkoutMode === 'local' && !$request->country_id) {
            $defaultCountryId = Setting::get('default_country', '');
            if ($defaultCountryId) {
                $query->where('country_id', $defaultCountryId);
            }
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($request->country_id) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->state_id) {
            $query->where('state_id', $request->state_id);
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
        $cities = $query->paginate($perPage);

        $countries = \App\Models\Country::ordered()->get();
        $states = State::ordered()->get();
        $baseQuery = City::query();
        if ($checkoutMode === 'local') {
            $defaultCountryId = Setting::get('default_country', '');
            if ($defaultCountryId) {
                $baseQuery->where('country_id', $defaultCountryId);
            }
        }
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'inactive' => (clone $baseQuery)->where('is_active', false)->count(),
            'countries' => $checkoutMode === 'local' ? 1 : City::distinct()->count('country'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.locations.cities.partials.table-rows', compact('cities'))->render(),
                'pagination' => $cities->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.locations.cities.index', compact('cities', 'countries', 'states', 'stats'));
    }

    public function create()
    {
        $countries = \App\Models\Country::ordered()->get();
        $states = State::ordered()->get();
        return view('admin.locations.cities.create', compact('countries', 'states'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'country' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        City::create($request->all());

        return redirect()->route('admin.locations.cities.index')
            ->with('success', 'City created successfully.');
    }

    public function edit(City $city)
    {
        $city->load('countryRelation');
        $countries = \App\Models\Country::ordered()->get();
        $states = State::ordered()->get();
        return view('admin.locations.cities.edit', compact('city', 'countries', 'states'));
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'country' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $city->update($request->all());

        return redirect()->route('admin.locations.cities.index')
            ->with('success', 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        $city->delete();
        return redirect()->route('admin.locations.cities.index')
            ->with('success', 'City deleted successfully.');
    }

    public function getCities(Request $request)
    {
        $query = City::active()->ordered();

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->state_id) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return response()->json([
            'success' => true,
            'cities' => $query->get(['id', 'name']),
        ]);
    }

    public function toggleStatus(City $city)
    {
        $city->update(['is_active' => !$city->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'City status updated.',
            'is_active' => $city->is_active,
        ]);
    }

    public function countrySettings()
    {
        $settings = Setting::where('group', 'order_configuration')->pluck('value', 'key');
        return view('admin.locations.country-settings', compact('settings'));
    }

    public function updateCountrySettings(Request $request)
    {
        $request->validate([
            'checkout_mode' => 'required|in:local,international',
            'default_country' => 'required|string|max:100',
        ]);

        Setting::updateOrCreate(
            ['key' => 'checkout_mode'],
            ['value' => $request->checkout_mode, 'group' => 'order_configuration']
        );
        Setting::updateOrCreate(
            ['key' => 'default_country'],
            ['value' => $request->default_country, 'group' => 'order_configuration']
        );

        return redirect()->route('admin.locations.country-settings')
            ->with('success', 'Country settings updated successfully.');
    }
}
