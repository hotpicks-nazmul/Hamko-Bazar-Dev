<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\ActivityLog;

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
        // The column is whitelisted, the direction was not: ?direction=sideways hit the query
        // builder and answered 500.
        $direction = strtolower((string) ($request->direction ?? 'desc')) === 'asc' ? 'asc' : 'desc';
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

        $perPage = (int) ($request->per_page ?? 25);
        $perPage = $perPage >= 1 && $perPage <= 100 ? $perPage : 25;

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
        // Whitelist, not except(). This used to be $customer->update($request->except(['status'])),
        // and User::$fillable carries role, is_super_admin, wallet_balance, loyalty_points,
        // password and customer_group_id — so the customer edit form was a way to mint a super
        // admin, hand yourself 999,999 in a wallet, or set someone's password. Only these three
        // fields are editable from this screen.
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // Customers registered by phone have no email (2 of 6 here), so it cannot be "required".
            'email' => 'nullable|email|max:255|unique:users,email,' . $customer->id,
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => 'nullable|string|in:active,inactive,banned',
        ]);

        $customer->name = $validated['name'];
        $customer->email = $validated['email'] ?? null;
        $customer->phone = $validated['phone'] ?? null;
        if (! empty($validated['status'])) {
            $customer->status = $validated['status'];
        }
        $customer->save();

        return back()->with('success', 'Customer updated successfully.');
    }

    public function destroy(User $customer)
    {
        if ($this->hasHistory($customer)) {
            return back()->with('error', $this->historyMessage($customer));
        }

        // Log before the row goes away: the subject reference is nulled by the FK and the name is
        // the only thing that survives in the log.
        ActivityLog::adminLog('Customer deleted', $customer, auth()->user(), [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
        ]);

        $customer->delete();

        return back()->with('success', 'Customer deleted successfully.');
    }

    public function orders(User $customer)
    {
        $orders = $customer->orders()->latest()->paginate(10);

        return view('admin.customers.orders', compact('customer', 'orders'));
    }

    public function loginAs(Request $request, User $customer)
    {
        // Only a customer, only an account that can actually sign in, and never on top of an
        // existing impersonation (that would lose the way back to the real admin).
        if ($customer->role !== 'customer') {
            return back()->with('error', 'Only customer accounts can be logged in as.');
        }
        if ($customer->status === 'banned') {
            return back()->with('error', 'This customer is banned. Unban the account before logging in as them.');
        }
        if (session('impersonator_id')) {
            return back()->with('error', 'You are already logged in as a customer. Return to your own account first.');
        }

        $admin = auth()->user();

        session([
            'impersonator_id' => $admin->id,
            'impersonating_customer_id' => $customer->id,
        ]);

        ActivityLog::adminLog('Logged in as customer', $customer, $admin, [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
        ]);

        auth()->login($customer);

        return redirect()->route('home')
            ->with('success', 'You are now browsing as ' . $customer->name . '. Use the bar at the top to return.');
    }

    /**
     * Anything that would be destroyed with the customer row (or orphaned by it).
     *
     * orders.user_id is ON DELETE SET NULL, so the orders survive with no customer; everything else
     * in the ledger — wallet_transactions, loyalty_points_transactions, refunds.user_id, reviews,
     * addresses, tickets, carts, wishlists — is ON DELETE CASCADE and disappears with the row.
     */
    protected function hasHistory(User $customer): bool
    {
        return $customer->orders()->exists()
            || \App\Models\CustomerWallet::where('user_id', $customer->id)->exists()
            || \App\Models\LoyaltyPointsTransaction::where('user_id', $customer->id)->exists()
            || \App\Models\Refund::where('user_id', $customer->id)->exists();
    }

    protected function historyMessage(User $customer): string
    {
        $orders = $customer->orders()->count();

        return sprintf(
            'Cannot delete %s: %d order(s) and ledger history are attached. %s',
            $customer->name,
            $orders,
            'Deactivate the account instead so the orders stay linked to a customer.'
        );
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
            'action' => 'required|string|in:activate,deactivate,delete',
            'ids' => 'required|json',
        ]);

        $action = $request->action;
        $ids = array_values(array_filter(array_map('intval', (array) json_decode($request->ids, true))));

        if (! $ids) {
            return back()->with('error', 'No customers selected.');
        }

        $customers = User::where('role', 'customer')->whereIn('id', $ids)->get();

        if ($customers->isEmpty()) {
            return back()->with('error', 'No matching customers found.');
        }

        if ($action === 'delete') {
            // Same rule as the single-row delete, applied per row so one protected customer does
            // not silently cancel the whole batch.
            $deleted = 0;
            $skipped = [];
            foreach ($customers as $customer) {
                if ($this->hasHistory($customer)) {
                    $skipped[] = $customer->name;

                    continue;
                }
                ActivityLog::adminLog('Customer deleted', $customer, auth()->user(), [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'via' => 'bulk',
                ]);
                $customer->delete();
                $deleted++;
            }

            $message = "{$deleted} customer(s) deleted.";
            if ($skipped) {
                $message .= ' Skipped (order or ledger history): ' . implode(', ', array_slice($skipped, 0, 5));
            }

            return $skipped && ! $deleted
                ? back()->with('error', $message)
                : back()->with('success', $message);
        }

        // Report the rows that actually changed, not the number of ids posted.
        $affected = 0;
        foreach ($customers as $customer) {
            $wanted = $action === 'activate' ? 'active' : 'inactive';
            if ($customer->status !== $wanted) {
                $customer->status = $wanted;
                $customer->save();
                $affected++;
            }
        }

        $label = $action === 'activate' ? 'activated' : 'deactivated';

        return back()->with('success', "{$affected} customer(s) {$label} successfully.");
    }
}
