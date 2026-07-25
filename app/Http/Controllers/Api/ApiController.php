<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    /**
     * Get store information
     */
    public function info(Request $request)
    {
        $apiKey = $request->attributes->get('apiKey');
        
        $siteName = Setting::get('site_name');
        $currency = Setting::get('currency_code');
        
        return response()->json([
            'success' => true,
            'data' => [
                'store_name' => $siteName ?? 'My Store',
                'store_url' => url('/'),
                'currency' => $currency ?? 'USD',
                'timezone' => config('app.timezone'),
                'api_key_type' => $apiKey->type ?? 'general',
                'rate_limit' => $apiKey->rate_limit ?? 100,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Get products list
     */
    public function products(Request $request)
    {
        $query = Product::where('status', 1);
        
        // Filter by category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }
        
        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate
        $perPage = min($request->per_page ?? 25, 100);
        $products = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Get single product
     */
    public function product(Request $request, $id)
    {
        $product = Product::where('status', 1)->find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $product,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Get categories
     */
    public function categories(Request $request)
    {
        $query = Category::where('status', 1);
        
        if ($request->parent_id !== null) {
            $query->where('parent_id', $request->parent_id ?? 0);
        }
        
        $categories = $query->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Get orders
     */
    public function orders(Request $request)
    {
        $apiKey = $request->attributes->get('apiKey');
        
        // Check if key has order permissions
        if (!in_array($apiKey->type, ['general', 'warehouse', 'Dropship'])) {
            return response()->json([
                'success' => false,
                'message' => 'API key does not have permission to access orders',
            ], 403);
        }
        
        $query = Order::query();
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate
        $perPage = min($request->per_page ?? 25, 100);
        $orders = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Get single order
     */
    public function order(Request $request, $id)
    {
        $apiKey = $request->attributes->get('apiKey');
        
        // Check if key has order permissions
        if (!in_array($apiKey->type, ['general', 'warehouse', 'Dropship'])) {
            return response()->json([
                'success' => false,
                'message' => 'API key does not have permission to access orders',
            ], 403);
        }
        
        $order = Order::with('orderItems')->find($id);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $order,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Get customers
     */
    public function customers(Request $request)
    {
        $apiKey = $request->attributes->get('apiKey');
        
        // Check if key has customer permissions
        if (!in_array($apiKey->type, ['general', ' Dropship'])) {
            return response()->json([
                'success' => false,
                'message' => 'API key does not have permission to access customers',
            ], 403);
        }
        
        $query = User::where('user_type', 'customer');
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }
        
        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate
        $perPage = min($request->per_page ?? 25, 100);
        $customers = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Get staff members
     */
    public function staffs(Request $request)
    {
        $apiKey = $request->attributes->get('apiKey');
        
        // Check if key has staff permissions
        if (!in_array($apiKey->type, ['general', 'warehouse'])) {
            return response()->json([
                'success' => false,
                'message' => 'API key does not have permission to access staff',
            ], 403);
        }
        
        $query = User::staff()->with('warehouse');
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by warehouse
        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('designation', 'like', "%{$request->search}%");
            });
        }
        
        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate
        $perPage = min($request->per_page ?? 25, 100);
        $staffs = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $staffs->items(),
            'meta' => [
                'current_page' => $staffs->currentPage(),
                'last_page' => $staffs->lastPage(),
                'per_page' => $staffs->perPage(),
                'total' => $staffs->total(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Get single staff member
     */
    public function staff(Request $request, $id)
    {
        $apiKey = $request->attributes->get('apiKey');
        
        // Check if key has staff permissions
        if (!in_array($apiKey->type, ['general', 'warehouse'])) {
            return response()->json([
                'success' => false,
                'message' => 'API key does not have permission to access staff',
            ], 403);
        }
        
        $staff = User::staff()->with('warehouse')->find($id);
        
        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member not found',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $staff,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Get API key usage stats
     */
    public function usage(Request $request)
    {
        $apiKey = $request->attributes->get('apiKey');
        
        // Get logs from last 24 hours
        $logs = $apiKey->logs()
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        // Calculate stats
        $totalRequests = $logs->count();
        $successfulRequests = $logs->where('status_code', '>=', 200)->where('status_code', '<', 300)->count();
        $failedRequests = $totalRequests - $successfulRequests;
        $avgResponseTime = $logs->avg('response_time') ?? 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'api_key' => [
                    'id' => $apiKey->id,
                    'name' => $apiKey->name,
                    'type' => $apiKey->type,
                    'rate_limit' => $apiKey->rate_limit,
                    'last_used_at' => $apiKey->last_used_at?->toIso8601String(),
                ],
                'stats' => [
                    'total_requests_24h' => $totalRequests,
                    'successful_requests' => $successfulRequests,
                    'failed_requests' => $failedRequests,
                    'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 100,
                    'avg_response_time_ms' => round($avgResponseTime, 2),
                ],
                'recent_logs' => $logs,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
