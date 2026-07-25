<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Rules\Phone;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $allowedSorts = ['name', 'email', 'orders_count', 'total_spent', 'created_at'];
        
        if (in_array($sort, $allowedSorts)) {
            if ($sort === 'total_spent') {
                $query->orderBySub(function ($q) {
                    $q->selectRaw('COALESCE(SUM(`total`), 0)')
                        ->from('orders')
                        ->whereColumn('orders.user_id', 'users.id');
                }, $direction);
            } else {
                $query->orderBy($sort, $direction);
            }
        }

        $perPage = $request->per_page ?? 25;
        $customers = $query->paginate($perPage);

        $stats = [
            'total' => User::where('role', 'customer')->count(),
            'active' => User::where('role', 'customer')->where('status', 'active')->count(),
            'inactive' => User::where('role', 'customer')->where('status', 'inactive')->count(),
        ];

        if ($request->ajax()) {
            $html = view('admin.customers.partials.table-rows', compact('customers'))->render();
            return response()->json([
                'html' => $html,
                'pagination' => $customers->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    public function show(User $customer)
    {
        $customer->load('orders', 'addresses');
        return view('admin.customers.show', compact('customer'));
    }

    public function update(Request $request, User $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => ['nullable', 'string', 'max:20', new Phone],
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $data = $request->except(['status']);
        if ($request->has('status')) {
            $data['status'] = $request->status;
        }

        $customer->update($data);

        return back()->with('success', 'Customer updated successfully.');
    }

    public function destroy(User $customer)
    {
        $customer->delete();
        return back()->with('success', 'Customer deleted successfully.');
    }

    public function orders(User $customer)
    {
        $orders = $customer->orders()->latest()->paginate(10);
        return view('admin.customers.orders', compact('customer', 'orders'));
    }

    public function loginAs(User $customer)
    {
        auth()->login($customer);
        return redirect()->route('home');
    }

    public function toggleStatus(User $customer)
    {
        $customer->status = $customer->status === 'active' ? 'inactive' : 'active';
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => $customer->status === 'active' ? 'Customer activated successfully.' : 'Customer deactivated successfully.',
            'is_active' => $customer->status === 'active',
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|json',
        ]);

        $action = $request->action;
        $ids = json_decode($request->ids);

        if (empty($ids)) {
            return back()->with('error', 'No customers selected.');
        }

        $customers = User::where('role', 'customer')->whereIn('id', $ids);

        switch ($action) {
            case 'activate':
                $customers->update(['status' => 'active']);
                $message = count($ids) . ' customer(s) activated successfully.';
                break;
            case 'deactivate':
                $customers->update(['status' => 'inactive']);
                $message = count($ids) . ' customer(s) deactivated successfully.';
                break;
            case 'delete':
                $customers->delete();
                $message = count($ids) . ' customer(s) deleted successfully.';
                break;
            default:
                return back()->with('error', 'Invalid action.');
        }

        return back()->with('success', $message);
    }
}
