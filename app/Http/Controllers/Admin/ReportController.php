<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\SellerPayout;
use App\Models\UserSearch;
use App\Models\CustomerWallet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Commission History Report
     * Shows commission history from seller payouts
     */
    public function commissionHistory(Request $request)
    {
        $search = $request->search ?? '';
        $dateRange = $request->date_range ?? '';
        $status = $request->status ?? '';
        $sellerId = $request->seller ?? '';
        $sortBy = $request->sort ?? 'date_desc';
        
        // Build base query
        $query = SellerPayout::with(['seller:id,name,shop_name,email']);
        
        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]) . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        // Apply seller filter
        if ($sellerId) {
            $query->where('seller_id', $sellerId);
        }
        
        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply search filter (seller name or shop name)
        if ($search) {
            $query->whereHas('seller', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $query = match($sortBy) {
            'date_asc' => $query->oldest(),
            'amount_desc' => $query->orderByDesc('amount'),
            'amount_asc' => $query->orderBy('amount', 'asc'),
            'commission_desc' => $query->orderByDesc('commission'),
            'commission_asc' => $query->orderBy('commission', 'asc'),
            default => $query->latest(), // 'date_desc'
        };
        
        $payouts = $query->paginate(20);
        
        // Calculate summary statistics
        $totalPayouts = SellerPayout::count();
        $totalAmount = SellerPayout::sum('amount');
        $totalCommission = SellerPayout::sum('commission');
        $totalNetAmount = SellerPayout::sum('net_amount');
        $pendingCount = SellerPayout::where('status', 'pending')->count();
        $completedCount = SellerPayout::where('status', 'completed')->count();
        $rejectedCount = SellerPayout::where('status', 'rejected')->count();
        
        // Get sellers for filter
        $sellers = User::sellers()->orderBy('name')->get(['id', 'name', 'shop_name']);
        
        // Calculate average commission rate
        $avgCommissionRate = $totalAmount > 0 ? ($totalCommission / $totalAmount) * 100 : 0;
        
        return view('admin.reports.commission-history', compact(
            'payouts',
            'search',
            'dateRange',
            'status',
            'sellerId',
            'sortBy',
            'totalPayouts',
            'totalAmount',
            'totalCommission',
            'totalNetAmount',
            'pendingCount',
            'completedCount',
            'rejectedCount',
            'sellers',
            'avgCommissionRate'
        ));
    }
    
    /**
     * Export Commission History Report
     */
    public function commissionHistoryExport(Request $request)
    {
        $search = $request->search ?? '';
        $dateRange = $request->date_range ?? '';
        $status = $request->status ?? '';
        $sellerId = $request->seller ?? '';
        
        // Build base query
        $query = SellerPayout::with(['seller:id,name,shop_name,email']);
        
        // Apply filters
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]) . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        if ($sellerId) {
            $query->where('seller_id', $sellerId);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($search) {
            $query->whereHas('seller', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%");
            });
        }
        
        $payouts = $query->latest()->get();
        
        // Generate CSV
        $filename = 'commission-history-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($payouts) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Seller Name', 'Shop Name', 'Amount', 'Commission Rate', 'Commission', 'Net Amount', 'Status', 'Payment Method', 'Date']);
            
            // Data rows
            foreach ($payouts as $payout) {
                $commissionRate = $payout->amount > 0 ? ($payout->commission / $payout->amount) * 100 : 0;
                fputcsv($file, [
                    $payout->seller ? $payout->seller->name : 'N/A',
                    $payout->seller ? $payout->seller->shop_name : 'N/A',
                    number_format($payout->amount, 2),
                    number_format($commissionRate, 2) . '%',
                    number_format($payout->commission, 2),
                    number_format($payout->net_amount, 2),
                    ucfirst($payout->status),
                    $payout->getPaymentMethodName(),
                    $payout->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Wallet Recharge History Report
     * Shows all wallet recharge transactions
     */
    public function walletHistory(Request $request)
    {
        $search = $request->search ?? '';
        $dateRange = $request->date_range ?? '';
        $type = $request->type ?? '';
        $source = $request->source ?? '';
        $sortBy = $request->sort ?? 'date_desc';
        
        // Build base query - focus on credit transactions (recharges)
        $query = CustomerWallet::with(['user:id,name,email,phone']);
        
        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]) . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        // Apply type filter (credit/debit)
        if ($type) {
            $query->where('type', $type);
        }
        
        // Apply source filter
        if ($source) {
            $query->where('source', $source);
        }
        
        // Apply search filter (user name, email, phone, reference)
        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('reference_id', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        }
        
        // Apply sorting
        $query = match($sortBy) {
            'date_asc' => $query->oldest(),
            'amount_desc' => $query->orderByDesc('amount'),
            'amount_asc' => $query->orderBy('amount', 'asc'),
            'balance_desc' => $query->orderByDesc('balance_after'),
            'balance_asc' => $query->orderBy('balance_after', 'asc'),
            default => $query->latest(), // 'date_desc'
        };
        
        $transactions = $query->paginate(20);
        
        // Calculate summary statistics from filtered data
        $filteredQuery = CustomerWallet::query();
        
        // Re-apply filters for summary statistics
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]) . ' 23:59:59';
                $filteredQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        if ($type) {
            $filteredQuery->where('type', $type);
        }
        
        if ($source) {
            $filteredQuery->where('source', $source);
        }
        
        if ($search) {
            $filteredQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('reference_id', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        }
        
        $totalRecharges = (clone $filteredQuery)->where('type', 'credit')->count();
        $totalRechargeAmount = (clone $filteredQuery)->where('type', 'credit')->sum('amount');
        $totalDebits = (clone $filteredQuery)->where('type', 'debit')->count();
        $totalDebitAmount = (clone $filteredQuery)->where('type', 'debit')->sum('amount');
        $netAmount = $totalRechargeAmount - $totalDebitAmount;
        
        // Get unique users who have wallet transactions
        $totalUsers = (clone $filteredQuery)->distinct('user_id')->count('user_id');
        
        // Get source counts
        $sourceCounts = (clone $filteredQuery)->select('source', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('source')
            ->get();
        
        return view('admin.reports.wallet-history', compact(
            'transactions',
            'search',
            'dateRange',
            'type',
            'source',
            'sortBy',
            'totalRecharges',
            'totalRechargeAmount',
            'totalDebits',
            'totalDebitAmount',
            'netAmount',
            'totalUsers',
            'sourceCounts'
        ));
    }
    
    /**
     * Export Wallet Recharge History Report
     */
    public function walletHistoryExport(Request $request)
    {
        $search = $request->search ?? '';
        $dateRange = $request->date_range ?? '';
        $type = $request->type ?? '';
        $source = $request->source ?? '';
        
        // Build base query
        $query = CustomerWallet::with(['user:id,name,email,phone']);
        
        // Apply filters
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]) . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        
        if ($type) {
            $query->where('type', $type);
        }
        
        if ($source) {
            $query->where('source', $source);
        }
        
        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('reference_id', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        }
        
        $transactions = $query->latest()->get();
        
        // Generate CSV
        $filename = 'wallet-recharge-history-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['User Name', 'Email', 'Phone', 'Type', 'Source', 'Amount', 'Balance After', 'Reference ID', 'Description', 'Date']);
            
            // Data rows
            foreach ($transactions as $txn) {
                fputcsv($file, [
                    $txn->user ? $txn->user->name : 'N/A',
                    $txn->user ? $txn->user->email : 'N/A',
                    $txn->user ? $txn->user->phone : 'N/A',
                    ucfirst($txn->type),
                    ucfirst($txn->source),
                    number_format($txn->amount, 2),
                    number_format($txn->balance_after, 2),
                    $txn->reference_id ?? 'N/A',
                    $txn->description ?? 'N/A',
                    $txn->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Products Wishlist Report
     * Shows products ranked by wishlist count
     */
    public function productsWishlist(Request $request)
    {
        $search = $request->search ?? '';
        $categoryId = $request->category ?? '';
        $sortBy = $request->sort ?? 'wishlist_desc';
        $dateRange = $request->date_range ?? '';
        
        // Build base query
        $wishlistQuery = Wishlist::with(['product.category', 'product.brand'])
            ->select(
                'product_id',
                DB::raw('COUNT(*) as wishlist_count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users'),
                DB::raw('MIN(created_at) as first_wishlisted'),
                DB::raw('MAX(created_at) as last_wishlisted')
            )
            ->groupBy('product_id');
        
        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);
                $wishlistQuery->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
            }
        }
        
        // Get product IDs that match search or category
        $productIds = null;
        if ($search || $categoryId) {
            $productQuery = Product::query();
            
            if ($search) {
                $productQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }
            
            if ($categoryId) {
                $productQuery->where('category_id', $categoryId);
            }
            
            $productIds = $productQuery->pluck('id');
            $wishlistQuery->whereIn('product_id', $productIds);
        }
        
        // Apply sorting
        $wishlistQuery = match($sortBy) {
            'wishlist_asc' => $wishlistQuery->orderBy('wishlist_count', 'asc'),
            'users_desc' => $wishlistQuery->orderBy('unique_users', 'desc'),
            'users_asc' => $wishlistQuery->orderBy('unique_users', 'asc'),
            'oldest' => $wishlistQuery->orderBy('first_wishlisted', 'asc'),
            'newest' => $wishlistQuery->orderBy('last_wishlisted', 'desc'),
            default => $wishlistQuery->orderBy('wishlist_count', 'desc'),
        };
        
        $wishlists = $wishlistQuery->paginate(20);
        
        // Convert date strings to Carbon objects for proper formatting
        foreach ($wishlists as $wishlist) {
            if ($wishlist->first_wishlisted && is_string($wishlist->first_wishlisted)) {
                $wishlist->first_wishlisted = \Carbon\Carbon::parse($wishlist->first_wishlisted);
            }
            if ($wishlist->last_wishlisted && is_string($wishlist->last_wishlisted)) {
                $wishlist->last_wishlisted = \Carbon\Carbon::parse($wishlist->last_wishlisted);
            }
        }
        
        // Get product details for each wishlist entry
        $productData = [];
        if ($wishlists->isNotEmpty()) {
            $productIds = $wishlists->pluck('product_id')->filter();
            if ($productIds->isNotEmpty()) {
                $products = Product::whereIn('id', $productIds)
                    ->get()
                    ->keyBy('id');
                
                foreach ($wishlists as $wishlist) {
                    $product = $products->get($wishlist->product_id);
                    if ($product) {
                        $productData[$wishlist->product_id] = [
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'price' => $product->price,
                            'featured_image' => $product->featured_image,
                            'category' => $product->category ? $product->category->name : 'N/A',
                            'brand' => $product->brand ? $product->brand->name : 'N/A',
                            'status' => $product->status,
                        ];
                    }
                }
            }
        }

        // Calculate summary statistics (respecting filters)
        $statsQuery = Wishlist::query();

        // Apply date range filter to stats
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);
                $statsQuery->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
            }
        }

        // Apply product filter to stats
        if ($search || $categoryId) {
            $productQuery = Product::query();
            if ($search) {
                $productQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }
            if ($categoryId) {
                $productQuery->where('category_id', $categoryId);
            }
            $statsQuery->whereIn('product_id', $productQuery->pluck('id'));
        }

        $totalWishlists = (clone $statsQuery)->count();
        $uniqueProducts = (clone $statsQuery)->distinct('product_id')->count('product_id');
        $uniqueUsers = (clone $statsQuery)->distinct('user_id')->count('user_id');
        $avgWishlistPerProduct = $uniqueProducts > 0 ? round($totalWishlists / $uniqueProducts, 1) : 0;

        // Top wishlisted product (within filtered data)
        $topWishlisted = (clone $statsQuery)->select('product_id', DB::raw('COUNT(*) as count'))
            ->groupBy('product_id')
            ->orderByDesc('count')
            ->first();
        $topProduct = $topWishlisted ? Product::find($topWishlisted->product_id) : null;

        // Get categories for filter
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        
        return view('admin.reports.products-wishlist', compact(
            'wishlists',
            'productData',
            'search',
            'categoryId',
            'sortBy',
            'dateRange',
            'totalWishlists',
            'uniqueProducts',
            'uniqueUsers',
            'avgWishlistPerProduct',
            'topProduct',
            'categories'
        ));
    }

    /**
     * Export Products Wishlist Report
     */
    public function productsWishlistExport(Request $request)
    {
        $search = $request->search ?? '';
        $dateRange = $request->date_range ?? '';
        
        // Build base query
        $wishlistQuery = Wishlist::with('product')
            ->select(
                'product_id',
                DB::raw('COUNT(*) as wishlist_count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users'),
                DB::raw('MIN(created_at) as first_wishlisted'),
                DB::raw('MAX(created_at) as last_wishlisted')
            )
            ->groupBy('product_id');
        
        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]);
                $wishlistQuery->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
            }
        }
        
        // Apply search filter
        if ($search) {
            $matchingProducts = Product::where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->pluck('id');
            $wishlistQuery->whereIn('product_id', $matchingProducts);
        }
        
        $wishlists = $wishlistQuery->orderBy('wishlist_count', 'desc')->get();
        
        // Get product details
        $productIds = $wishlists->pluck('product_id')->filter();
        $products = [];
        if ($productIds->isNotEmpty()) {
            $products = Product::whereIn('id', $productIds)
                ->get()
                ->keyBy('id');
        }
        
        // Generate CSV
        $filename = 'products-wishlist-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($wishlists, $products) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Product Name', 'SKU', 'Category', 'Price', 'Wishlist Count', 'Unique Users', 'First Wishlisted', 'Last Wishlisted']);
            
            // Data rows
            foreach ($wishlists as $item) {
                $product = $products->get($item->product_id);
                fputcsv($file, [
                    $product ? $product->name : 'N/A',
                    $product ? $product->sku : 'N/A',
                    $product && $product->category ? $product->category->name : 'N/A',
                    $product ? number_format($product->price, 2) : '0.00',
                    $item->wishlist_count,
                    $item->unique_users,
                    $item->first_wishlisted ? $item->first_wishlisted->format('Y-m-d') : 'N/A',
                    $item->last_wishlisted ? $item->last_wishlisted->format('Y-m-d') : 'N/A',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * In-House Product Sale Report
     * Shows product-wise sales data for inhouse orders
     */
    public function inHouseProductSale(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $search = $request->search ?? '';
        $categoryId = $request->category ?? '';
        $sortBy = $request->sort ?? 'qty_desc';

        // Build base query for inhouse orders
        $orderIds = Order::where('order_type', 'inhouse')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid')
            ->pluck('id');

        // Base order items query
        $orderItemsQuery = OrderItem::whereIn('order_id', $orderIds);

        // Apply category filter
        if ($categoryId) {
            $productIds = Product::where('category_id', $categoryId)->pluck('id');
            $orderItemsQuery = $orderItemsQuery->whereIn('product_id', $productIds);
        }

        // Get product sales data
        $productSales = $orderItemsQuery->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(DISTINCT order_id) as order_count'),
                DB::raw('AVG(price) as avg_price')
            )
            ->groupBy('product_id', 'product_name');

        // Apply search filter
        if ($search) {
            $productSales = $productSales->where('product_name', 'like', "%{$search}%");
        }

        // Apply sorting
        $productSales = match($sortBy) {
            'qty_asc' => $productSales->orderBy('total_qty', 'asc'),
            'sales_desc' => $productSales->orderBy('total_sales', 'desc'),
            'sales_asc' => $productSales->orderBy('total_sales', 'asc'),
            'name_asc' => $productSales->orderBy('product_name', 'asc'),
            'name_desc' => $productSales->orderBy('product_name', 'desc'),
            default => $productSales->orderBy('total_qty', 'desc'),
        };

        $productSales = $productSales->with('product:id,sku,thumbnail')->paginate(20);

        // Calculate summary statistics using filtered order items
        $filteredOrderItemsQuery = OrderItem::whereIn('order_id', $orderIds);
        if ($categoryId) {
            $productIds = Product::where('category_id', $categoryId)->pluck('id');
            $filteredOrderItemsQuery = $filteredOrderItemsQuery->whereIn('product_id', $productIds);
        }
        $totalQtySold = (clone $filteredOrderItemsQuery)->sum('quantity');
        $totalSales = (clone $filteredOrderItemsQuery)->sum('total');
        $totalOrders = (clone $filteredOrderItemsQuery)->distinct('order_id')->count('order_id');
        $uniqueProducts = (clone $filteredOrderItemsQuery)->distinct('product_id')->count('product_id');

        // Get categories for filter
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('admin.reports.in-house-product-sale', compact(
            'productSales',
            'startDate',
            'endDate',
            'search',
            'categoryId',
            'sortBy',
            'totalQtySold',
            'totalSales',
            'totalOrders',
            'uniqueProducts',
            'categories'
        ));
    }

    /**
     * Seller Product Sale Report
     * Shows product-wise sales data for seller orders
     */
    public function sellerSales(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $search = $request->search ?? '';
        $sellerId = $request->seller ?? '';
        $sortBy = $request->sort ?? 'qty_desc';

        // Build base query for seller orders
        $orderQuery = Order::where('order_type', 'seller')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid');

        if ($sellerId) {
            $orderQuery->where('seller_id', $sellerId);
        }

        $orderIds = $orderQuery->pluck('id');

        // Get product sales data with seller info from orders
        $productSales = OrderItem::whereIn('order_id', $orderIds)
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(DISTINCT order_id) as order_count'),
                DB::raw('AVG(price) as avg_price')
            )
            ->groupBy('product_id', 'product_name');

        // Apply search filter
        if ($search) {
            $productSales = $productSales->where('product_name', 'like', "%{$search}%");
        }

        // Apply sorting
        $productSales = match($sortBy) {
            'qty_asc' => $productSales->orderBy('total_qty', 'asc'),
            'sales_desc' => $productSales->orderBy('total_sales', 'desc'),
            'sales_asc' => $productSales->orderBy('total_sales', 'asc'),
            'name_asc' => $productSales->orderBy('product_name', 'asc'),
            'name_desc' => $productSales->orderBy('product_name', 'desc'),
            default => $productSales->orderBy('total_qty', 'desc'),
        };

        $productSales = $productSales->paginate(20);

        // Calculate summary statistics
        $totalQtySold = OrderItem::whereIn('order_id', $orderIds)->sum('quantity');
        $totalSales = OrderItem::whereIn('order_id', $orderIds)->sum('total');
        $totalOrders = $orderIds->count();
        $uniqueProducts = OrderItem::whereIn('order_id', $orderIds)->distinct('product_id')->count('product_id');

        // Get sellers for filter (from active sellers, not just from orders)
        // This ensures the dropdown always shows sellers even when no orders exist in the date range
        $sellers = User::sellers()->where('status', 1)->orderBy('name')->get(['id', 'name', 'shop_name']);

        // Get seller names for the displayed products
        $sellerNames = [];
        $sellerUsers = [];
        if ($productSales->isNotEmpty()) {
            $productIds = $productSales->pluck('product_id')->filter();
            if ($productIds->isNotEmpty()) {
                $sellerNames = Product::whereIn('id', $productIds)
                    ->pluck('user_id', 'id')
                    ->toArray();
                
                // Get seller names
                $sellerUserIds = array_unique(array_values($sellerNames));
                $sellerUsers = User::whereIn('id', $sellerUserIds)->pluck('name', 'id')->toArray();
            }
        }

        return view('admin.reports.seller-products-sale', compact(
            'productSales',
            'startDate',
            'endDate',
            'search',
            'sellerId',
            'sortBy',
            'totalQtySold',
            'totalSales',
            'totalOrders',
            'uniqueProducts',
            'sellers',
            'sellerNames',
            'sellerUsers'
        ));
    }

    /**
     * Export Seller Product Sale Report
     */
    public function sellerSalesExport(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $search = $request->search ?? '';
        $sellerId = $request->seller ?? '';

        // Build base query for seller orders
        $orderQuery = Order::where('order_type', 'seller')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid');

        if ($sellerId) {
            $orderQuery->where('seller_id', $sellerId);
        }

        $orderIds = $orderQuery->pluck('id');

        // Get product sales data
        $productSales = OrderItem::whereIn('order_id', $orderIds)
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(DISTINCT order_id) as order_count'),
                DB::raw('AVG(price) as avg_price')
            )
            ->groupBy('product_id', 'product_name');

        // Apply search filter
        if ($search) {
            $productSales = $productSales->where('product_name', 'like', "%{$search}%");
        }

        $productSales = $productSales->orderBy('total_qty', 'desc')->get();

        // Get seller names
        $sellerNames = [];
        if ($productSales->isNotEmpty()) {
            $productIds = $productSales->pluck('product_id')->filter();
            if ($productIds->isNotEmpty()) {
                $sellerNames = Product::whereIn('id', $productIds)
                    ->pluck('user_id', 'id')
                    ->toArray();
                
                $sellerUserIds = array_unique(array_values($sellerNames));
                $sellerUsers = User::whereIn('id', $sellerUserIds)->pluck('name', 'id')->toArray();
            }
        }

        // Generate CSV
        $filename = 'seller-product-sales-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($productSales, $sellerNames, $sellerUsers) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Product Name', 'Seller', 'Orders', 'Qty Sold', 'Avg Price', 'Total Sales']);
            
            // Data rows
            foreach ($productSales as $item) {
                $sellerName = isset($sellerNames[$item->product_id]) && isset($sellerUsers[$sellerNames[$item->product_id]]) 
                    ? $sellerUsers[$sellerNames[$item->product_id]] 
                    : 'N/A';
                
                fputcsv($file, [
                    $item->product_name,
                    $sellerName,
                    $item->order_count,
                    $item->total_qty,
                    number_format($item->avg_price, 2),
                    number_format($item->total_sales, 2),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function sales(Request $request)
    {
        $startDate = $request->start_date ?? now()->subMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $orders = Order::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid')
            ->get();

        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $salesByDate = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('total');
        });

        return view('admin.reports.sales', compact('orders', 'totalSales', 'totalOrders', 'averageOrder', 'salesByDate', 'startDate', 'endDate'));
    }

    public function products(Request $request)
    {
        $topProducts = Product::withSum('orderItems as total_sold', 'quantity')
            ->orderByDesc('total_sold')
            ->take(20)
            ->get();

        $lowStockProducts = Product::where('stock', '<', 10)
            ->orderBy('stock')
            ->get();

        return view('admin.reports.products', compact('topProducts', 'lowStockProducts'));
    }

    public function customers(Request $request)
    {
        $topCustomers = User::withCount('orders')
            ->withSum('orders as total_spent', 'total')
            ->where('role', 'customer')
            ->orderByDesc('total_spent')
            ->take(20)
            ->get();

        $newCustomers = User::where('role', 'customer')
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        return view('admin.reports.customers', compact('topCustomers', 'newCustomers'));
    }

    public function inventory(Request $request)
    {
        $search = $request->search ?? '';
        $categoryId = $request->category ?? '';
        $stockStatus = $request->stock_status ?? '';
        $sortBy = $request->sort ?? 'stock_asc';
        
        // Build query with relationships
        $products = Product::with('category', 'brand')
            ->select('id', 'name', 'sku', 'featured_image', 'quantity', 'price', 'cost_price', 'low_stock_threshold', 'stock_status', 'category_id', 'brand_id');
        
        // Apply search filter
        if ($search) {
            $products = $products->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        // Apply category filter
        if ($categoryId) {
            $products = $products->where('category_id', $categoryId);
        }
        
        // Apply stock status filter
        if ($stockStatus) {
            switch($stockStatus) {
                case 'in_stock':
                    $products = $products->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $products = $products->whereColumn('quantity', '<=', 'low_stock_threshold')
                                        ->where('quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $products = $products->where('quantity', '<=', 0);
                    break;
            }
        }
        
        // Apply sorting
        $products = match($sortBy) {
            'stock_desc' => $products->orderBy('quantity', 'desc'),
            'name_asc' => $products->orderBy('name', 'asc'),
            'name_desc' => $products->orderBy('name', 'desc'),
            'price_asc' => $products->orderBy('price', 'asc'),
            'price_desc' => $products->orderBy('price', 'desc'),
            'stock_asc' => $products->orderBy('quantity', 'asc'),
            default => $products->orderBy('quantity', 'asc'),
        };
        
        $perPage = $request->per_page ?? 25;
        $products = $products->paginate($perPage);
        
        // Calculate summary statistics
        $totalProducts = Product::count();
        $inStockCount = Product::where('quantity', '>', 0)->count();
        $lowStockCount = Product::whereColumn('quantity', '<=', 'low_stock_threshold')
                                ->where('quantity', '>', 0)->count();
        $outOfStockCount = Product::where('quantity', '<=', 0)->count();
        $totalStockValue = Product::sum(DB::raw('COALESCE(quantity, 0) * COALESCE(cost_price, 0)'));
        $totalStockQty = Product::sum('quantity');
        
        // Get categories for filter
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        
        return view('admin.reports.inventory', compact(
            'products',
            'search',
            'categoryId',
            'stockStatus',
            'sortBy',
            'totalProducts',
            'inStockCount',
            'lowStockCount',
            'outOfStockCount',
            'totalStockValue',
            'totalStockQty',
            'categories'
        ));
    }

    public function export($type)
    {
        // Export logic would go here
        return back()->with('success', 'Report exported successfully.');
    }
    
    /**
     * Export Inventory Report
     */
    public function inventoryExport(Request $request)
    {
        $search = $request->search ?? '';
        $categoryId = $request->category ?? '';
        $stockStatus = $request->stock_status ?? '';
        $sortBy = $request->sort ?? 'stock_asc';
        
        // Build query with relationships
        $products = Product::with('category', 'brand')
            ->select('id', 'name', 'sku', 'featured_image', 'quantity', 'price', 'cost_price', 'low_stock_threshold', 'stock_status', 'category_id', 'brand_id');
        
        // Apply search filter
        if ($search) {
            $products = $products->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        // Apply category filter
        if ($categoryId) {
            $products = $products->where('category_id', $categoryId);
        }
        
        // Apply stock status filter
        if ($stockStatus) {
            switch($stockStatus) {
                case 'in_stock':
                    $products = $products->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $products = $products->whereColumn('quantity', '<=', 'low_stock_threshold')
                                        ->where('quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $products = $products->where('quantity', '<=', 0);
                    break;
            }
        }
        
        // Apply sorting
        $products = match($sortBy) {
            'stock_desc' => $products->orderBy('quantity', 'desc'),
            'name_asc' => $products->orderBy('name', 'asc'),
            'name_desc' => $products->orderBy('name', 'desc'),
            'price_asc' => $products->orderBy('price', 'asc'),
            'price_desc' => $products->orderBy('price', 'desc'),
            'stock_asc' => $products->orderBy('quantity', 'asc'),
            default => $products->orderBy('quantity', 'asc'),
        };
        
        $products = $products->get();
        
        // Generate CSV
        $filename = 'products-stock-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Product ID',
                'Product Name',
                'SKU',
                'Category',
                'Quantity',
                'Low Stock Threshold',
                'Stock Status',
                'Price (৳)',
                'Cost Price (৳)',
                'Stock Value (৳)'
            ]);
            
            // Data rows
            foreach ($products as $product) {
                $stockStatus = $product->quantity <= 0 ? 'Out of Stock' : 
                               ($product->quantity <= $product->low_stock_threshold ? 'Low Stock' : 'In Stock');
                
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->sku ?? 'N/A',
                    $product->category->name ?? 'Uncategorized',
                    $product->quantity ?? 0,
                    $product->low_stock_threshold ?? 0,
                    $stockStatus,
                    $product->price ?? 0,
                    $product->cost_price ?? 0,
                    ($product->quantity ?? 0) * ($product->cost_price ?? $product->price ?? 0)
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Export In-House Product Sale Report
     */
    public function inHouseProductSaleExport(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $search = $request->search ?? '';
        $categoryId = $request->category ?? '';
        $sortBy = $request->sort ?? 'qty_desc';

        // Get order IDs
        $orderIds = Order::where('order_type', 'inhouse')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid')
            ->pluck('id');

        // Build base order items query
        $orderItemsQuery = OrderItem::whereIn('order_id', $orderIds);
        
        // Apply category filter
        if ($categoryId) {
            $productIds = Product::where('category_id', $categoryId)->pluck('id');
            $orderItemsQuery = $orderItemsQuery->whereIn('product_id', $productIds);
        }

        // Get product sales data
        $productSales = $orderItemsQuery->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(DISTINCT order_id) as order_count'),
                DB::raw('AVG(price) as avg_price')
            )
            ->groupBy('product_id', 'product_name');
        
        // Apply search filter
        if ($search) {
            $productSales = $productSales->where('product_name', 'like', "%{$search}%");
        }
        
        // Apply sorting
        $productSales = match($sortBy) {
            'qty_asc' => $productSales->orderBy('total_qty', 'asc'),
            'sales_desc' => $productSales->orderBy('total_sales', 'desc'),
            'sales_asc' => $productSales->orderBy('total_sales', 'asc'),
            'name_asc' => $productSales->orderBy('product_name', 'asc'),
            'name_desc' => $productSales->orderBy('product_name', 'desc'),
            default => $productSales->orderBy('total_qty', 'desc'),
        };
        
        $productSales = $productSales->get();

        // Generate CSV
        $filename = 'in-house-product-sales-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($productSales) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Product Name', 'Orders', 'Qty Sold', 'Avg Price', 'Total Sales']);
            
            // Data rows
            foreach ($productSales as $item) {
                fputcsv($file, [
                    $item->product_name,
                    $item->order_count,
                    $item->total_qty,
                    number_format($item->avg_price, 2),
                    number_format($item->total_sales, 2),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * User Searches Report
     */
    public function userSearches(Request $request)
    {
        $search = $request->search ?? '';
        $dateRange = $request->date_range ?? '';
        $searchType = $request->search_type ?? '';
        $sortBy = $request->sort ?? 'recent';
        
        // Build base query
        $query = UserSearch::with('user:id,name,email');
        
        // Apply date range filter
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]) . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // Apply search query filter
        if ($search) {
            $query->where('query', 'like', "%{$search}%");
        }

        // Apply search type filter
        if ($searchType === 'autocomplete') {
            $query->where('is_autocomplete', true);
        } elseif ($searchType === 'manual') {
            $query->where('is_autocomplete', false);
        }

        // Apply sorting
        if ($sortBy === 'popular') {
            // For popular sort, get query counts
            $queryCounts = UserSearch::select('query', DB::raw('COUNT(*) as search_count'))
                ->groupBy('query')
                ->orderByDesc('search_count')
                ->limit(100)
                ->get()
                ->keyBy('query');

            // Get IDs of most recent search for each popular query
            $recentSearchIds = UserSearch::select(DB::raw('MAX(id) as id'))
                ->whereIn('query', $queryCounts->keys())
                ->groupBy('query')
                ->pluck('id');

            $searches = UserSearch::with('user:id,name,email')
                ->whereIn('id', $recentSearchIds)
                ->get();

            // Sort by search_count descending (matching the popular order)
            $searches = $searches->sortByDesc(function ($search) use ($queryCounts) {
                return $queryCounts[$search->query]->search_count ?? 0;
            })->values();

            // Add search_count to each result and paginate manually
            foreach ($searches as $search) {
                $search->search_count = $queryCounts[$search->query]->search_count ?? 1;
            }

            // CreateLengthAwarePaginator manually
            $perPage = 20;
            $currentPage = request()->get('page', 1);
            $paginated = $searches->slice(($currentPage - 1) * $perPage, $perPage);
            $searches = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginated,
                $searches->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url()]
            );
        } else {
            $query = match($sortBy) {
                'results_desc' => $query->orderByDesc('results_count'),
                'results_asc' => $query->orderBy('results_count', 'asc'),
                'oldest' => $query->oldest(),
                default => $query->latest(),
            };
            $searches = $query->paginate(20);
        }

        $searches = $query->paginate(20);

        // Calculate summary statistics
        $totalSearches = UserSearch::count();
        $uniqueQueries = UserSearch::distinct('query')->count('query');
        $uniqueUsers = UserSearch::distinct('user_id')->count('user_id');
        $avgResultsPerSearch = $totalSearches > 0 ? round(UserSearch::avg('results_count'), 1) : 0;
        
        // Top searched terms
        $topSearches = UserSearch::select('query', DB::raw('COUNT(*) as search_count'))
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit(10)
            ->get();

        // Searches with no results
        $noResultsCount = UserSearch::where('results_count', 0)->count();

        return view('admin.reports.user-searches', compact(
            'searches',
            'search',
            'dateRange',
            'searchType',
            'sortBy',
            'totalSearches',
            'uniqueQueries',
            'uniqueUsers',
            'avgResultsPerSearch',
            'topSearches',
            'noResultsCount'
        ));
    }

    /**
     * Export User Searches Report
     */
    public function userSearchesExport(Request $request)
    {
        $search = $request->search ?? '';
        $dateRange = $request->date_range ?? '';
        $searchType = $request->search_type ?? '';

        // Build base query
        $query = UserSearch::with('user:id,name,email');

        // Apply filters
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $startDate = trim($dates[0]);
                $endDate = trim($dates[1]) . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        if ($search) {
            $query->where('query', 'like', "%{$search}%");
        }

        if ($searchType === 'autocomplete') {
            $query->where('is_autocomplete', true);
        } elseif ($searchType === 'manual') {
            $query->where('is_autocomplete', false);
        }

        $searches = $query->latest()->get();

        // Generate CSV
        $filename = 'user-searches-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($searches) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Search Query', 'User', 'Email', 'Results Count', 'Search Type', 'IP Address', 'Date']);
            
            // Data rows
            foreach ($searches as $item) {
                fputcsv($file, [
                    $item->query,
                    $item->user ? $item->user->name : 'Guest',
                    $item->user ? $item->user->email : 'N/A',
                    $item->results_count,
                    $item->is_autocomplete ? 'Autocomplete' : 'Manual',
                    $item->ip_address ?? 'N/A',
                    $item->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
