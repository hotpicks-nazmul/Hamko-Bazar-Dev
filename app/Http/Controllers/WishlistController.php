<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\ActivityLog;

class WishlistController extends Controller
{
    /**
     * Display user's wishlist.
     */
    public function index()
    {
        $wishlist = auth()->user()->wishlist()->with('product')->get();
        return view('themes.general.wishlist.index', compact('wishlist'));
    }

    /**
     * Get wishlist items for sidebar.
     */
    public function items()
    {
        if (!auth()->check()) {
            return response()->json([
                'items' => [],
                'count' => 0
            ]);
        }

        $wishlist = Wishlist::where('user_id', auth()->id())
            ->with('product')
            ->get();

        $items = $wishlist->map(function ($item) {
            $product = $item->product;
            $imagePath = $product->featured_image ?? ($product->image ?? null);
            
            // Build proper image URL
            $imageUrl = null;
            if ($imagePath) {
                if (str_starts_with($imagePath, 'http')) {
                    $imageUrl = $imagePath;
                } elseif (str_starts_with($imagePath, '/storage/')) {
                    $imageUrl = $imagePath;
                } elseif (str_starts_with($imagePath, '/uploads/')) {
                    $imageUrl = asset($imagePath);
                } else {
                    $imageUrl = asset('storage/' . $imagePath);
                }
            }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'image' => $imageUrl,
            ];
        });

        return response()->json([
            'items' => $items,
            'count' => $items->count()
        ]);
    }

    /**
     * Add item to wishlist.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $exists = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist.'
            ]);
        }

        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
        ]);

        // Log wishlist add activity
        $product = Product::find($request->product_id);
        ActivityLog::customerLog(
            'Added product to wishlist',
            $product,
            auth()->user(),
            ['product_id' => $product->id, 'product_name' => $product->name]
        );

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist!'
        ]);
    }

    /**
     * Remove item from wishlist.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::find($request->product_id);
        
        Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->delete();

        // Log wishlist remove activity
        ActivityLog::customerLog(
            'Removed product from wishlist',
            $product,
            auth()->user(),
            ['product_id' => $product->id, 'product_name' => $product->name]
        );

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist!'
        ]);
    }

    /**
     * Toggle item in wishlist.
     */
    public function toggle(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to add to wishlist.',
                'login_required' => true
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $exists = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            Wishlist::where('user_id', auth()->id())
                ->where('product_id', $request->product_id)
                ->delete();

            $items = $this->getWishlistItems();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist!',
                'added' => false,
                'items' => $items
            ]);
        }

        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
        ]);

        $items = $this->getWishlistItems();

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist!',
            'added' => true,
            'items' => $items
        ]);
    }

    /**
     * Get wishlist items helper.
     */
    private function getWishlistItems()
    {
        $wishlist = Wishlist::where('user_id', auth()->id())
            ->with('product')
            ->get();

        return $wishlist->map(function ($item) {
            $product = $item->product;
            $imagePath = $product->featured_image ?? ($product->image ?? null);
            
            // Build proper image URL
            $imageUrl = null;
            if ($imagePath) {
                if (str_starts_with($imagePath, 'http')) {
                    $imageUrl = $imagePath;
                } elseif (str_starts_with($imagePath, '/storage/')) {
                    $imageUrl = $imagePath;
                } elseif (str_starts_with($imagePath, '/uploads/')) {
                    $imageUrl = asset($imagePath);
                } else {
                    $imageUrl = asset('storage/' . $imagePath);
                }
            }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'image' => $imageUrl,
            ];
        });
    }
}
