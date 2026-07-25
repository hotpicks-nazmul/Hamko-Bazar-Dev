<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Country;
use App\Models\State;

class StateController extends Controller
{
    use SortsLists;

    public function index(Request $request)
    {
        $query = State::withCount('cities')->ordered();

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

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        // `?sort=nope` was SQLSTATE 42S22, `?direction=sideways` a SQL syntax error and `?per_page=abc`
        // a type error on all three location indexes; `?per_page=100000` returned the whole table.
        $query->orderBy(
            $this->sortColumn($request, ['sort_order', 'name', 'country', 'cities_count', 'is_active', 'created_at'], 'sort_order'),
            $this->sortDirection($request, 'asc')
        );

        $states = $query->paginate($this->perPage($request, 25, 100));

        $countries = \App\Models\Country::ordered()->get();
        $stats = [
            'total' => State::count(),
            'active' => State::where('is_active', true)->count(),
            'inactive' => State::where('is_active', false)->count(),
            'countries' => State::distinct()->count('country'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.locations.states.partials.table-rows', compact('states'))->render(),
                'pagination' => $states->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.locations.states.index', compact('states', 'countries', 'stats'));
    }

    public function create()
    {
        $countries = \App\Models\Country::ordered()->get();
        return view('admin.locations.states.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        // The table carries UNIQUE (slug, country_id) and nothing checked it, so retyping an existing
        // name answered SQLSTATE 23000 as a 500 page instead of a message.
        $this->ensureNameIsFree($data);

        // Explicitly, not $request->all(): `slug` and `country` are fillable, so a crafted POST could
        // rewrite the record's identity and the country name the lists filter on.
        State::create([
            'name' => $data['name'],
            'country_id' => $data['country_id'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.locations.states.index')
            ->with('success', 'State created successfully.');
    }

    public function edit(State $state)
    {
        $state->load('countryRelation');
        $countries = \App\Models\Country::ordered()->get();
        return view('admin.locations.states.edit', compact('state', 'countries'));
    }

    public function update(Request $request, State $state)
    {
        $data = $this->validated($request);

        $this->ensureNameIsFree($data, $state->id);

        $state->update([
            'name' => $data['name'],
            'country_id' => $data['country_id'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.locations.states.index')
            ->with('success', 'State updated successfully.');
    }

    public function destroy(State $state)
    {
        // cities.state_id is ON DELETE SET NULL, so the children survive as active cities with no state:
        // they stay orderable through the country list but disappear from every state-filtered list.
        // The flash used to say only "State deleted successfully."
        $orphaned = $state->cities()->count();

        $state->delete();

        $message = $orphaned > 0
            ? "State deleted. {$orphaned} " . Str::plural('city', $orphaned) . ' lost their state and are now unassigned — reassign them from the Cities screen.'
            : 'State deleted successfully.';

        return redirect()->route('admin.locations.states.index')->with('success', $message);
    }

    /**
     * Validate the create/update payload. `is_active` is read with $request->boolean() so an unchecked
     * switch (absent) is false and a checked one ('1' — the form now posts a value) is true.
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'is_active' => 'nullable',   // read with $request->boolean(): '1', 'on' and absent all work
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    protected function ensureNameIsFree(array $data, ?int $ignoreId = null): void
    {
        $countryName = Country::find($data['country_id'])?->name;
        $slug = Str::slug(trim($data['name'] . ' ' . ($countryName ?? '')));

        $query = State::where('slug', $slug)->where('country_id', $data['country_id']);
        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => 'A state with this name already exists in that country.',
            ]);
        }
    }

    public function toggleStatus(State $state)
    {
        $state->update(['is_active' => !$state->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'State status updated.',
            'is_active' => $state->is_active,
        ]);
    }
}
