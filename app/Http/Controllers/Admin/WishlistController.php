<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    /**
     * Display a listing of wishlist items.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->search;
        $productId = $request->product;
        $userId = $request->user;
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $perPage = $request->per_page ?? 25;

        // Base query with eager loading
        $query = Wishlist::with(['user', 'product.category']);

        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%");
                })->orWhereHas('user', function($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        // Filter by product
        if ($productId) {
            $query->where('product_id', $productId);
        }

        // Filter by user
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Sorting
        $allowedSorts = ['created_at', 'user_id', 'product_id'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get paginated results
        $wishlists = $query->paginate($perPage);

        // Get statistics
        $stats = $this->getStats();

        // Get products for filter dropdown
        $products = Product::select('id', 'name', 'sku')
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(100)
            ->get();

        // Get users for filter dropdown (customers only)
        $users = User::select('id', 'name', 'email')
            ->where('role', 'customer')
            ->orderBy('name')
            ->limit(100)
            ->get();

        // AJAX response
        if ($request->ajax()) {
            $html = view('admin.wishlists.partials.table-rows', compact('wishlists'))->render();
            
            return response()->json([
                'html' => $html,
                'pagination' => $wishlists->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.wishlists.index', compact('wishlists', 'stats', 'products', 'users'));
    }

    /**
     * Get wishlist statistics.
     */
    private function getStats()
    {
        $totalWishlists = Wishlist::count();
        
        $uniqueProducts = Wishlist::distinct('product_id')->count('product_id');
        
        $uniqueUsers = Wishlist::distinct('user_id')->count('user_id');
        
        // Most wished products (top 5)
        $topProducts = Wishlist::select('product_id', DB::raw('COUNT(*) as wishlist_count'))
            ->groupBy('product_id')
            ->orderByDesc('wishlist_count')
            ->limit(5)
            ->with('product:id,name,sku,featured_image')
            ->get()
            ->map(function($item) {
                return [
                    'product' => $item->product,
                    'count' => $item->wishlist_count
                ];
            });

        // Most wishlisting users (top 5)
        $topUsers = Wishlist::select('user_id', DB::raw('COUNT(*) as wishlist_count'))
            ->groupBy('user_id')
            ->orderByDesc('wishlist_count')
            ->limit(5)
            ->with('user:id,name,email,avatar')
            ->get()
            ->map(function($item) {
                return [
                    'user' => $item->user,
                    'count' => $item->wishlist_count
                ];
            });

        return [
            'total_wishlists' => $totalWishlists,
            'unique_products' => $uniqueProducts,
            'unique_users' => $uniqueUsers,
            'top_products' => $topProducts,
            'top_users' => $topUsers
        ];
    }

    /**
     * Remove wishlist item(s).
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:wishlists,id'
        ]);

        $count = Wishlist::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} wishlist item(s) removed successfully."
        ]);
    }

    /**
     * Remove single wishlist item.
     */
    public function destroySingle($id)
    {
        $wishlist = Wishlist::findOrFail($id);
        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist item removed successfully.'
        ]);
    }

    /**
     * Bulk action on wishlist items.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'exists:wishlists,id'
        ]);

        $action = $request->action;
        $ids = $request->ids;

        switch ($action) {
            case 'delete':
                $count = Wishlist::whereIn('id', $ids)->delete();
                return response()->json([
                    'success' => true,
                    'message' => "{$count} wishlist item(s) deleted successfully."
                ]);

            case 'export':
                $wishlists = Wishlist::whereIn('id', $ids)
                    ->with(['user', 'product'])
                    ->get();
                
                $csvData = "ID,User Name,User Email,Product Name,Product SKU,Added Date\n";
                foreach ($wishlists as $item) {
                    $csvData .= "{$item->id},{$item->user->name},{$item->user->email},{$item->product->name},{$item->product->sku},{$item->created_at}\n";
                }
                
                return response()->stream(function() use ($csvData) {
                    echo $csvData;
                }, 200, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="wishlists-export.csv"'
                ]);

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action.'
                ], 400);
        }
    }

    /**
     * Get wishlist details for a specific product.
     */
    public function productWishlist(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        
        $wishlists = Wishlist::where('product_id', $productId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        if ($request->ajax()) {
            $html = view('admin.wishlists.partials.product-wishlist-rows', compact('wishlists'))->render();
            
            return response()->json([
                'html' => $html,
                'pagination' => $wishlists->links()->toHtml()
            ]);
        }

        return view('admin.wishlists.product-wishlist', compact('product', 'wishlists'));
    }

    /**
     * Get wishlist details for a specific user.
     */
    public function userWishlist(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $wishlists = Wishlist::where('user_id', $userId)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        if ($request->ajax()) {
            $html = view('admin.wishlists.partials.user-wishlist-rows', compact('wishlists'))->render();
            
            return response()->json([
                'html' => $html,
                'pagination' => $wishlists->links()->toHtml()
            ]);
        }

        return view('admin.wishlists.user-wishlist', compact('user', 'wishlists'));
    }

    /**
     * Export all wishlists.
     */
    public function export(Request $request)
    {
        $wishlists = Wishlist::with(['user', 'product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $csvData = "ID,User Name,User Email,Product Name,Product SKU,Added Date\n";
        foreach ($wishlists as $item) {
            $csvData .= "{$item->id},";
            $csvData .= "\"{$item->user->name}\",";
            $csvData .= "\"{$item->user->email}\",";
            $csvData .= "\"{$item->product->name}\",";
            $csvData .= "\"{$item->product->sku}\",";
            $csvData .= "{$item->created_at}\n";
        }
        
        return response()->stream(function() use ($csvData) {
            echo $csvData;
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="all-wishlists-' . date('Y-m-d') . '.csv"'
        ]);
    }
}
