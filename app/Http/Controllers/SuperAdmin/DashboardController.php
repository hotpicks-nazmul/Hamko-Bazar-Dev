<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Refund;

class DashboardController extends Controller
{
    /**
     * Display super admin dashboard.
     */
    public function index()
    {
        // Get stats for super admin
        $stats = [
            'total_admins' => User::where('role', 'admin')->count(),
            'total_staffs' => User::where('role', 'staff')->count(),
            'active_admins' => User::where('role', 'admin')->where('status', 'active')->count(),
            'active_staffs' => User::where('role', 'staff')->where('status', 'active')->count(),
            'pending_refunds' => Refund::where('status', 'pending')->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
        ];
        
        return view('super-admin.dashboard', compact('stats'));
    }
}
