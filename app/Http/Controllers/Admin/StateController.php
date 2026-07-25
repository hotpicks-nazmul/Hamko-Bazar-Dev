<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\State;

class StateController extends Controller
{
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

        $sort = $request->sort ?? 'sort_order';
        $direction = $request->direction ?? 'asc';
        $query->orderBy($sort, $direction);

        $perPage = $request->per_page ?? 25;
        $states = $query->paginate($perPage);

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
        $request->validate([
            'name' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        State::create($request->all());

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
        $request->validate([
            'name' => 'required|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $state->update($request->all());

        return redirect()->route('admin.locations.states.index')
            ->with('success', 'State updated successfully.');
    }

    public function destroy(State $state)
    {
        $state->delete();
        return redirect()->route('admin.locations.states.index')
            ->with('success', 'State deleted successfully.');
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
