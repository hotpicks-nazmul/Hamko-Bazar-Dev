<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerWallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerWalletController extends Controller
{
    /**
     * Display customer wallet listing with search and filters.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');

        // Search by name, email, phone
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        // Filter by balance range
        if ($request->min_balance) {
            $query->where('wallet_balance', '>=', $request->min_balance);
        }

        if ($request->max_balance) {
            $query->where('wallet_balance', '<=', $request->max_balance);
        }

        // Filter by wallet status (has balance or not)
        if ($request->has_balance) {
            if ($request->has_balance === 'yes') {
                $query->where('wallet_balance', '>', 0);
            } elseif ($request->has_balance === 'no') {
                $query->where('wallet_balance', '<=', 0);
            }
        }

        // Sorting
        $sort = $request->sort ?? 'wallet_balance';
        $direction = $request->direction ?? 'desc';

        // Validate sort field
        $allowedSorts = ['wallet_balance', 'wallet_points', 'name', 'email', 'created_at'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'wallet_balance';
        }

        $query->orderBy($sort, $direction);

        $customers = $query->paginate(25)->appends($request->query());

        // Calculate statistics
        $stats = [
            'total_customers' => User::where('role', 'customer')->count(),
            'total_wallet_balance' => User::where('role', 'customer')->sum('wallet_balance'),
            'total_wallet_points' => User::where('role', 'customer')->sum('wallet_points'),
            'customers_with_balance' => User::where('role', 'customer')->where('wallet_balance', '>', 0)->count(),
            'customers_with_points' => User::where('role', 'customer')->where('wallet_points', '>', 0)->count(),
        ];

        // Recent transactions
        $recentTransactions = CustomerWallet::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.wallet.index', compact('customers', 'stats', 'recentTransactions'));
    }

    /**
     * Display customer wallet details.
     */
    public function show(Request $request, $customerId)
    {
        $customer = User::where('role', 'customer')
            ->withCount('orders')
            ->findOrFail($customerId);

        $transactions = CustomerWallet::where('user_id', $customerId)
            ->when($request->type, function ($q) use ($request) {
                if ($request->type === 'credit') {
                    $q->where('type', 'credit');
                } elseif ($request->type === 'debit') {
                    $q->where('type', 'debit');
                }
            })
            ->when($request->source, function ($q) use ($request) {
                $q->where('source', $request->source);
            })
            ->latest()
            ->paginate(25)->appends($request->query());

        return view('admin.wallet.show', compact('customer', 'transactions'));
    }

    /**
     * Display wallet transactions.
     */
    public function transactions(Request $request)
    {
        $query = CustomerWallet::with('user');

        // Search by customer name, email
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        // Filter by type
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Filter by source
        if ($request->source) {
            $query->where('source', $request->source);
        }

        // Filter by date range
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        $transactions = $query->paginate(25)->appends($request->query());

        // Statistics
        $stats = [
            'total_credit' => CustomerWallet::where('type', 'credit')->sum('amount'),
            'total_debit' => CustomerWallet::where('type', 'debit')->sum('amount'),
            'total_transactions' => CustomerWallet::count(),
        ];

        return view('admin.wallet.transactions', compact('transactions', 'stats'));
    }

    /**
     * Add balance to customer wallet.
     */
    public function addBalance(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $customer = User::findOrFail($request->customer_id);

        DB::transaction(function () use ($customer, $request) {
            // Update customer wallet balance
            $newBalance = $customer->wallet_balance + $request->amount;
            $customer->wallet_balance = $newBalance;
            $customer->save();

            // Create transaction record
            CustomerWallet::create([
                'user_id' => $customer->id,
                'amount' => $request->amount,
                'type' => 'credit',
                'source' => 'admin',
                'description' => $request->description ?? 'Balance added by admin',
                'balance_after' => $newBalance,
            ]);
        });

        return redirect()->route('admin.customers.wallet.show', $customer->id)
            ->with('success', 'Wallet balance added successfully!');
    }

    /**
     * Deduct balance from customer wallet.
     */
    public function deductBalance(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $customer = User::findOrFail($request->customer_id);

        // Check if customer has enough balance
        if ($customer->wallet_balance < $request->amount) {
            return redirect()->back()
                ->with('error', 'Insufficient wallet balance. Current balance: '.number_format($customer->wallet_balance, 2));
        }

        DB::transaction(function () use ($customer, $request) {
            // Update customer wallet balance
            $newBalance = $customer->wallet_balance - $request->amount;
            $customer->wallet_balance = $newBalance;
            $customer->save();

            // Create transaction record
            CustomerWallet::create([
                'user_id' => $customer->id,
                'amount' => $request->amount,
                'type' => 'debit',
                'source' => 'admin',
                'description' => $request->description ?? 'Balance deducted by admin',
                'balance_after' => $newBalance,
            ]);
        });

        return redirect()->route('admin.customers.wallet.show', $customer->id)
            ->with('success', 'Wallet balance deducted successfully!');
    }

    /**
     * Search customers for AJAX autocomplete.
     */
    public function searchCustomers(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $customers = User::where('role', 'customer')
            ->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                    ->orWhere('email', 'like', "%{$request->q}%")
                    ->orWhere('phone', 'like', "%{$request->q}%");
            })
            ->select('id', 'name', 'email', 'phone', 'wallet_balance')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }
}
