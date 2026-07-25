<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductBundle;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class BundleController extends Controller
{
    /**
     * Display all active bundles.
     */
    public function index(Request $request)
    {
        $query = ProductBundle::with(['items.product'])
            ->valid()
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc');

        // Filter by featured if requested
        if ($request->featured) {
            $query->featured();
        }

        $bundles = $query->paginate(12);

        return view('themes.general.bundles.index', compact('bundles'));
    }

    /**
     * Display a single bundle.
     */
    public function show($slug)
    {
        $bundle = ProductBundle::with(['items.product.category'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Check if bundle is valid
        if (!$bundle->isValid()) {
            return redirect()->route('bundles.index')
                ->with('error', 'This bundle is no longer available.');
        }

        // Get related bundles
        $relatedBundles = ProductBundle::with(['items.product'])
            ->valid()
            ->where('id', '!=', $bundle->id)
            ->featured()
            ->take(3)
            ->get();

        return view('themes.general.bundles.show', compact('bundle', 'relatedBundles'));
    }

    /**
     * Add bundle to cart.
     */
    public function addToCart(Request $request, $id)
    {
        $bundle = ProductBundle::findOrFail($id);

        // Check if bundle can be purchased
        if (!$bundle->canBePurchasedBy(Auth::user())) {
            return back()->with('error', 'This bundle is no longer available for purchase.');
        }

        $quantity = $request->quantity ?? 1;

        // Check per-user limit
        if (Auth::check() && $bundle->max_purchases_per_user) {
            $userPurchases = $bundle->purchases()->where('user_id', Auth::id())->count();
            if ($userPurchases + $quantity > $bundle->max_purchases_per_user) {
                return back()->with('error', "You can only purchase this bundle {$bundle->max_purchases_per_user} time(s).");
            }
        }

        // Get or create cart
        $cart = $this->getOrCreateCart();

        // Add bundle items to cart
        foreach ($bundle->items as $item) {
            $cartItem = $cart->items()->firstOrCreate(
                ['product_id' => $item->product_id],
                ['quantity' => 0]
            );

            $cartItem->quantity += $item->quantity * $quantity;
            $cartItem->save();
        }

        // Store bundle reference in session for checkout
        $bundleCart = session()->get('bundle_cart', []);
        $bundleCart[] = [
            'bundle_id' => $bundle->id,
            'quantity' => $quantity,
            'price' => $bundle->final_price,
        ];
        session()->put('bundle_cart', $bundleCart);

        // Update cart totals
        $this->updateCartTotals($cart);

        if ($request->buy_now) {
            return redirect()->route('checkout.index');
        }

        return back()->with('success', 'Bundle added to cart successfully!');
    }

    /**
     * Get or create cart for current user/session.
     */
    protected function getOrCreateCart()
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['session_id' => session()->getId()]
            );
        } else {
            $cart = Cart::firstOrCreate(
                ['session_id' => session()->getId()],
                ['user_id' => null]
            );
        }

        return $cart;
    }

    /**
     * Update cart totals.
     */
    protected function updateCartTotals($cart)
    {
        $subtotal = 0;
        
        foreach ($cart->items as $item) {
            $price = $item->product->sale_price ?? $item->product->price;
            $subtotal += $price * $item->quantity;
        }

        // Apply bundle discounts
        $bundleCart = session()->get('bundle_cart', []);
        foreach ($bundleCart as $bundleItem) {
            $bundle = ProductBundle::find($bundleItem['bundle_id']);
            if ($bundle) {
                // Calculate the discount amount
                $originalTotal = $bundle->original_price * $bundleItem['quantity'];
                $bundlePrice = $bundle->final_price * $bundleItem['quantity'];
                $subtotal = $subtotal - $originalTotal + $bundlePrice;
            }
        }

        $cart->subtotal = $subtotal;
        $cart->total = $subtotal; // Add tax/shipping as needed
        $cart->save();
    }
}
