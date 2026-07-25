<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of membership plans.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $featured = $request->get('featured');
        $sort = $request->get('sort', 'sort_order');
        $direction = $request->get('direction', 'asc');
        $perPage = $request->get('per_page', 25);

        // Statistics
        $stats = [
            'total' => MembershipPlan::count(),
            'active' => MembershipPlan::where('is_active', true)->count(),
            'inactive' => MembershipPlan::where('is_active', false)->count(),
            'featured' => MembershipPlan::where('is_featured', true)->count(),
            'total_members' => MembershipPlan::sum('members_count'),
        ];

        // Build query
        $query = MembershipPlan::query();

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

        // Featured filter
        if ($featured !== null && $featured !== '') {
            $query->where('is_featured', $featured === '1');
        }

        // Sorting
        $validSorts = ['name', 'sort_order', 'price', 'duration_days', 'discount_percentage', 'created_at', 'members_count'];
        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('sort_order')->orderBy('name');
        }

        $plans = $query->paginate($perPage)->appends($request->query());

        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.customers.membership.partials.table-rows', compact('plans'))->render();

            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $plans->links()->toHtml(),
                'total' => $plans->total(),
                'plans_firstitem' => $plans->firstItem(),
                'plans_lastitem' => $plans->lastItem(),
                'search' => $search,
            ]);
        }

        return view('admin.customers.membership.index', compact('plans', 'stats', 'search'));
    }

    /**
     * Show the form for creating a new membership plan.
     */
    public function create()
    {
        return view('admin.customers.membership.create');
    }

    /**
     * Store a newly created membership plan in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:membership_plans,name',
            'slug' => 'nullable|string|max:255|unique:membership_plans,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'minimum_spent' => 'nullable|numeric|min:0',
            'benefits' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'max_members' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $plan = MembershipPlan::create([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'minimum_spent' => $request->minimum_spent ?? 0,
            'benefits' => $request->benefits,
            'icon' => $request->icon,
            'color' => $request->color ?? '#6c757d',
            'sort_order' => $request->sort_order ?? 0,
            'max_members' => $request->max_members,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->is_active ?? true,
            'is_featured' => $request->is_featured ?? false,
        ]);

        flash()->success('Membership plan created successfully!');

        return redirect()->route('admin.customers.membership.index');
    }

    /**
     * Show the form for editing the specified membership plan.
     */
    public function edit(MembershipPlan $membershipPlan)
    {
        return view('admin.customers.membership.edit', compact('membershipPlan'));
    }

    /**
     * Update the specified membership plan in storage.
     */
    public function update(Request $request, MembershipPlan $membershipPlan)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:membership_plans,name,'.$membershipPlan->id,
            'slug' => 'nullable|string|max:255|unique:membership_plans,slug,'.$membershipPlan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'minimum_spent' => 'nullable|numeric|min:0',
            'benefits' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'max_members' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $membershipPlan->update([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'minimum_spent' => $request->minimum_spent ?? 0,
            'benefits' => $request->benefits,
            'icon' => $request->icon,
            'color' => $request->color ?? '#6c757d',
            'sort_order' => $request->sort_order ?? 0,
            'max_members' => $request->max_members,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->is_active ?? true,
            'is_featured' => $request->is_featured ?? false,
        ]);

        flash()->success('Membership plan updated successfully!');

        return redirect()->route('admin.customers.membership.index');
    }

    /**
     * Remove the specified membership plan from storage.
     */
    public function destroy(MembershipPlan $membershipPlan)
    {
        // Check if plan has members
        if ($membershipPlan->members_count > 0) {
            flash()->error('Cannot delete membership plan with active members. Please remove members first.');

            return back();
        }

        $membershipPlan->delete();

        flash()->success('Membership plan deleted successfully!');

        return redirect()->route('admin.customers.membership.index');
    }

    /**
     * Toggle the status of the membership plan.
     */
    public function toggleStatus(MembershipPlan $membershipPlan)
    {
        $membershipPlan->update([
            'is_active' => ! $membershipPlan->is_active,
        ]);

        $status = $membershipPlan->is_active ? 'activated' : 'deactivated';
        $message = "Membership plan {$status} successfully!";

        // Check if AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $membershipPlan->is_active,
            ]);
        }

        flash()->success($message);

        return back();
    }

    /**
     * Toggle the featured status of the membership plan.
     */
    public function toggleFeatured(MembershipPlan $membershipPlan)
    {
        $membershipPlan->update([
            'is_featured' => ! $membershipPlan->is_featured,
        ]);

        $status = $membershipPlan->is_featured ? 'featured' : 'unfeatured';
        $message = "Membership plan {$status} successfully!";

        // Check if AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_featured' => $membershipPlan->is_featured,
            ]);
        }

        flash()->success($message);

        return back();
    }
}
