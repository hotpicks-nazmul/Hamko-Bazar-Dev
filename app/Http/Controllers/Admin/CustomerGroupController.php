<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerGroupController extends Controller
{
    /**
     * Display customer groups list with statistics.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $sort = $request->get('sort', 'sort_order');
        $direction = $request->get('direction', 'asc');
        $perPage = $request->get('per_page', 25);

        // Statistics
        $stats = [
            'total' => CustomerGroup::count(),
            'active' => CustomerGroup::where('is_active', true)->count(),
            'inactive' => CustomerGroup::where('is_active', false)->count(),
            'with_discount' => CustomerGroup::where('discount_percentage', '>', 0)->count(),
        ];

        // Build query
        $query = CustomerGroup::withCount('users');

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }

        // Sorting
        $validSorts = ['name', 'sort_order', 'created_at', 'discount_percentage', 'users_count'];
        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('sort_order')->orderBy('name');
        }

        $customerGroups = $query->paginate($perPage)->appends($request->query());

        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.customers.groups.partials.table-rows', compact('customerGroups'))->render();

            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $customerGroups->links()->toHtml(),
                'total' => $customerGroups->total(),
            ]);
        }

        return view('admin.customers.groups.index', compact('customerGroups', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.customers.groups.create');
    }

    /**
     * Store new customer group.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name',
            'slug' => 'nullable|string|max:255|unique:customer_groups,slug',
            'description' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $customerGroup = CustomerGroup::create([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        flash()->success('Customer group created successfully!');

        return redirect()->route('admin.customers.groups.index');
    }

    /**
     * Show edit form.
     */
    public function edit(CustomerGroup $customerGroup)
    {
        return view('admin.customers.groups.edit', compact('customerGroup'));
    }

    /**
     * Update customer group.
     */
    public function update(Request $request, CustomerGroup $customerGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name,'.$customerGroup->id,
            'slug' => 'nullable|string|max:255|unique:customer_groups,slug,'.$customerGroup->id,
            'description' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $customerGroup->update([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        flash()->success('Customer group updated successfully!');

        return redirect()->route('admin.customers.groups.index');
    }

    /**
     * Delete customer group.
     */
    public function destroy(CustomerGroup $customerGroup)
    {
        // Check if group has customers
        if ($customerGroup->users()->count() > 0) {
            flash()->error('Cannot delete customer group with assigned customers. Please reassign customers first.');

            return back();
        }

        $customerGroup->delete();

        flash()->success('Customer group deleted successfully!');

        return redirect()->route('admin.customers.groups.index');
    }

    /**
     * Toggle status.
     */
    public function toggleStatus(CustomerGroup $customerGroup)
    {
        $customerGroup->update([
            'is_active' => ! $customerGroup->is_active,
        ]);

        $status = $customerGroup->is_active ? 'activated' : 'deactivated';
        flash()->success("Customer group {$status} successfully!");

        return back();
    }
}
