<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display staff dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Get staff's permissions via Spatie + legacy fallback
        $permissions = $user->getPermissionsArray();

        // Get stats based on permissions
        $stats = [];

        if ($user->hasPermission('orders') || $user->hasPermission('orders.view')) {
            $stats['pending_orders'] = Order::where('delivery_status', 'pending')->count();
            $stats['total_orders'] = Order::count();
        }

        if ($user->hasPermission('products') || $user->hasPermission('products.view')) {
            $stats['total_products'] = Product::count();
            $stats['low_stock'] = Product::whereRaw('stock_quantity <= low_stock_quantity')->count();
        }

        return view('staff.dashboard', compact('stats', 'permissions'));
    }
}
