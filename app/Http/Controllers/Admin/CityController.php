<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\Setting;

class CityController extends Controller
{
    use SortsLists;

    public function index(Request $request)
    {
        // withCount-style eager loads: the table badges `$city->areas->count()` and `$city->state->name`,
        // which was 50 extra queries per page without them.
        $query = City::with('state', 'countryRelation')->withCount('areas')->ordered();

        $checkoutMode = Setting::get('checkout_mode', 'local');
        if ($checkoutMode === 'local' && !$request->country_id) {
            // Reading the raw setting filtered every city out of the panel when the stored id no longer
            // existed; the helper only returns a country that is really there.
            $defaultCountryId = Setting::defaultCountryId();
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

        $query->orderBy(
            $this->sortColumn($request, ['sort_order', 'name', 'country', 'is_active', 'created_at'], 'sort_order'),
            $this->sortDirection($request, 'asc')
        );

        $cities = $query->paginate($this->perPage($request, 25, 100));

        $countries = \App\Models\Country::ordered()->get();
        $states = State::ordered()->get();
        $baseQuery = City::query();
        if ($checkoutMode === 'local') {
            $defaultCountryId = Setting::defaultCountryId();
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
        $data = $this->validated($request);

        // cities.slug is UNIQUE on its own, so a repeated name answered SQLSTATE 23000 as a 500.
        $this->ensureNameIsFree($data);

        // `country` is deliberately not taken from the request: it is derived from country_id in the
        // model, and it is the column the storefront's city filter and this screen's search read.
        City::create([
            'name' => $data['name'],
            'country_id' => $data['country_id'],
            'state_id' => $data['state_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

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
        $data = $this->validated($request);

        $this->ensureNameIsFree($data, $city->id);

        $city->update([
            'name' => $data['name'],
            'country_id' => $data['country_id'],
            'state_id' => $data['state_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.locations.cities.index')
            ->with('success', 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        // areas.city_id is ON DELETE CASCADE: deleting a city takes its areas with it, and the operator
        // was told nothing but "City deleted successfully."
        $removedAreas = $city->areas()->count();

        $city->delete();

        $message = $removedAreas > 0
            ? "City deleted, together with {$removedAreas} " . Str::plural('area', $removedAreas) . ' under it.'
            : 'City deleted successfully.';

        return redirect()->route('admin.locations.cities.index')->with('success', $message);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'is_active' => 'nullable',   // read with $request->boolean(): '1', 'on' and absent all work
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    protected function ensureNameIsFree(array $data, ?int $ignoreId = null): void
    {
        $countryName = Country::find($data['country_id'])?->name;
        $slug = Str::slug(trim($data['name'] . ' ' . ($countryName ?? '')));

        $query = City::where('slug', $slug);
        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => 'A city with this name already exists.',
            ]);
        }
    }

    public function getCities(Request $request)
    {
        $query = City::active()->ordered();

        if ($request->country_id) {
            $query->where('country_id', $request->country_id);
        } elseif ($request->country) {
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
            // Without exists:countries,id any string was stored, and Local mode then filtered every
            // city out of the checkout — the storefront could not be ordered from until somebody
            // noticed this screen.
            'default_country' => 'required|exists:countries,id',
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
