<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartController extends Controller
{
    protected $theme;

    public function __construct(ThemeService $theme)
    {
        $this->theme = $theme;
    }

    /**
     * Display cart.
     */
    public function index()
    {
        $cart = $this->getCart();
        
        return $this->theme->view('cart.index', compact('cart'));
    }

    /**
     * Add item to cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'variation' => 'nullable|array',
        ]);

        $product = Product::active()->findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;

        // Debug
        \Log::info('ADD CART - variation received', ['variation' => $request->variation]);

        // Check stock
        if ($product->quantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available.',
            ], 400);
        }

        $cart = $this->getCart();

        $cart->addItem($product, $quantity, $request->variation);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart!',
            'cart_count' => $cart->total_items,
        ]);
    }

    /**
     * Update cart item.
     */
    public function update(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();
        $cart->updateItem($request->item_id, $request->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated!',
            'cart_count' => $cart->total_items,
            'subtotal' => $cart->subtotal,
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:cart_items,id',
        ]);

        $cart = $this->getCart();
        $cart->removeItem($request->item_id);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart!',
            'cart_count' => $cart->total_items,
        ]);
    }

    /**
     * Clear cart.
     */
    public function clear()
    {
        $cart = $this->getCart();
        $cart->clear();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared!',
            'cart_count' => 0,
        ]);
    }

    /**
     * Get mini cart (for AJAX).
     */
    public function miniCart()
    {
        $cart = $this->getCart();
        
        return response()->json([
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'product_image' => $item->product->featured_image,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ];
            }),
            'subtotal' => $cart->subtotal,
            'total_items' => $cart->total_items,
        ]);
    }

    /**
     * Get cart count (for AJAX).
     */
    public function count()
    {
        $cart = $this->getCart();
        
        return response()->json([
            'count' => $cart->total_items,
        ]);
    }

    /**
     * Get or create cart.
     */
    protected function getCart()
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        // Use actual session ID from Laravel
        $sessionId = session()->getId();
        
        $cart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
        if ($cart) {
            return $cart;
        }

        // Create new cart for this session
        $cart = Cart::create([
            'session_id' => $sessionId,
            'user_id' => null,
            'items' => []
        ]);
        session()->put('cart_id', $cart->id);
        return $cart;
    }
}
