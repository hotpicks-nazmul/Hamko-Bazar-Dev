<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\CustomerSegment;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerSegmentationController extends Controller
{
    /**
     * Display a listing of customer segments.
     */
    public function index(Request $request)
    {
        $query = CustomerSegment::query();

        // Search by name
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === '1');
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = strtolower((string) $request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $validSorts = ['name', 'created_at', 'customer_count'];
        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        // Pagination
        $perPage = (int) $request->get('per_page', 15);
        $perPage = $perPage >= 1 && $perPage <= 100 ? $perPage : 15;
        $segments = $query->with('creator')->paginate($perPage);

        // Get statistics
        $stats = [
            'total_segments' => CustomerSegment::count(),
            'active_segments' => CustomerSegment::where('is_active', true)->count(),
            'total_customers_segmented' => CustomerSegment::sum('customer_count'),
        ];

        // AJAX response for live search
        if ($request->ajax()) {
            $html = view('admin.customers.segmentation.partials.table-rows', compact('segments'))->render();

            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $segments->links()->toHtml(),
                'total' => $segments->total(),
            ]);
        }

        return view('admin.customers.segmentation.index', compact('segments', 'stats'));
    }

    /**
     * Show the form for creating a new segment.
     */
    public function create()
    {
        $customerGroups = CustomerGroup::where('is_active', true)->get();

        return view('admin.customers.segmentation.create', compact('customerGroups'));
    }

    /**
     * Store a newly created segment in storage.
     */
    public function store(Request $request)
    {
        // There is no `conditions` field in create.blade.php — the form posts the individual
        // condition inputs and this method builds the array from them. Requiring a
        // `conditions` key that the form never sends made every save answer 422.
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_segments,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $conditions = $this->conditionsFrom($request);
        if (! $conditions) {
            return back()->withInput()->with('error', 'Choose at least one condition, otherwise the segment matches every customer.');
        }

        $segment = CustomerSegment::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'conditions' => $conditions,
            'is_active' => $request->boolean('is_active'),
            'created_by' => auth()->id(),
        ]);

        // Calculate and save customer count
        $segment->updateCustomerCount();

        return redirect()->route('admin.customers.segmentation.index')
            ->with('success', 'Segment "'.$segment->name.'" created successfully with '.$segment->customer_count.' customers.');
    }

    /**
     * Display the specified segment.
     */
    public function show(CustomerSegment $segment)
    {
        $segment->load('creator');

        // Membership is the segment's conditions, evaluated now — the same rule that produces
        // customer_count and the export. It used to read the customer_segment_members pivot, which
        // nothing in the codebase ever writes, so a segment with customer_count=1 listed nobody.
        $customers = $segment->applyConditions()
            ->withCount('orders')
            ->withSum('orders as orders_sum_total', 'total')
            ->latest()
            ->paginate(20);

        return view('admin.customers.segmentation.show', compact('segment', 'customers'));
    }

    /**
     * Show the form for editing the specified segment.
     */
    public function edit(CustomerSegment $segment)
    {
        $customerGroups = CustomerGroup::where('is_active', true)->get();

        return view('admin.customers.segmentation.edit', compact('segment', 'customerGroups'));
    }

    /**
     * Update the specified segment in storage.
     */
    public function update(Request $request, CustomerSegment $segment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_segments,name,'.$segment->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $conditions = $this->conditionsFrom($request);
        if (! $conditions) {
            return back()->withInput()->with('error', 'Choose at least one condition, otherwise the segment matches every customer.');
        }

        // Update segment
        $segment->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'conditions' => $conditions,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Recalculate customer count
        $segment->updateCustomerCount();

        return redirect()->route('admin.customers.segmentation.index')
            ->with('success', 'Segment "'.$segment->name.'" updated successfully. Now includes '.$segment->customer_count.' customers.');
    }

    /**
     * Remove the specified segment from storage.
     */
    public function destroy(CustomerSegment $segment)
    {
        $segment->delete();

        return redirect()->route('admin.customers.segmentation.index')
            ->with('success', 'Segment deleted successfully.');
    }

    /**
     * Toggle segment status.
     */
    public function toggleStatus(CustomerSegment $segment)
    {
        $segment->update(['is_active' => ! $segment->is_active]);

        $status = $segment->is_active ? 'activated' : 'deactivated';

        return back()->with('success', 'Segment '.$status.' successfully.');
    }

    /**
     * Preview customers matching segment conditions.
     */
    public function preview(Request $request)
    {
        // Build temporary conditions from request
        $conditions = $this->conditionsFrom($request);

        // Create temporary segment to use its applyConditions method
        $tempSegment = new CustomerSegment(['conditions' => $conditions]);

        // Get matching customers with pagination
        $customers = $tempSegment->applyConditions()
            ->select('users.id', 'users.name', 'users.email', 'users.phone', 'users.created_at')
            ->paginate(20);

        return response()->json([
            'count' => $tempSegment->applyConditions()->count(),
            'customers' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    /**
     * Turn the condition inputs of the create/edit form into the stored conditions array.
     *
     * store(), update() and preview() all needed the same rules and each carried its own copy; one
     * definition means a preview can never promise a different set of customers than the save.
     */
    protected function conditionsFrom(Request $request): array
    {
        $conditions = [];

        if ($request->boolean('order_count_enabled')) {
            if ($request->filled('order_count_min')) {
                $conditions['order_count_min'] = (int) $request->order_count_min;
            }
            if ($request->filled('order_count_max')) {
                $conditions['order_count_max'] = (int) $request->order_count_max;
            }
        }

        if ($request->boolean('total_spent_enabled')) {
            if ($request->filled('total_spent_min')) {
                $conditions['total_spent_min'] = (float) $request->total_spent_min;
            }
            if ($request->filled('total_spent_max')) {
                $conditions['total_spent_max'] = (float) $request->total_spent_max;
            }
        }

        if ($request->boolean('last_order_days_enabled') && $request->filled('last_order_days')) {
            $conditions['last_order_days'] = (int) $request->last_order_days;
        }

        if ($request->filled('customer_group_id')) {
            $conditions['customer_group_id'] = (int) $request->customer_group_id;
        }

        if ($request->filled('registration_date_from')) {
            $conditions['registration_date_from'] = $request->registration_date_from;
        }
        if ($request->filled('registration_date_to')) {
            $conditions['registration_date_to'] = $request->registration_date_to;
        }

        if ($request->filled('customer_status')) {
            // Stored under the historical key, mapped to users.status in CustomerSegment.
            $conditions['is_active'] = $request->customer_status === 'active';
        }

        return $conditions;
    }

    /**
     * Export customers in the segment.
     */
    public function export(CustomerSegment $segment)
    {
        $customers = $segment->applyConditions()->get();

        $filename = 'segment_'.$segment->id.'_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Customer Group', 'Registration Date', 'Total Orders', 'Total Spent']);

            foreach ($customers as $customer) {
                $ordersCount = $customer->orders()->count();
                $totalSpent = $customer->orders()->sum('total');
                $groupName = $customer->customerGroup ? $customer->customerGroup->name : 'N/A';

                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $groupName,
                    $customer->created_at->format('Y-m-d'),
                    $ordersCount,
                    number_format($totalSpent, 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get segment statistics.
     */
    public function stats()
    {
        $stats = [
            'total_segments' => CustomerSegment::count(),
            'active_segments' => CustomerSegment::where('is_active', true)->count(),
            'total_customers_segmented' => CustomerSegment::sum('customer_count'),
            'avg_customers_per_segment' => CustomerSegment::avg('customer_count') ?? 0,
        ];

        return response()->json($stats);
    }
}
