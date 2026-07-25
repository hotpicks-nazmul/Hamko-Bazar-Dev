<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display the cart.
     */
    public function index()
    {
        $cart = $this->getCart();
        return view('themes.general.cart.index', compact('cart'));
    }

    /**
     * Add item to cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:products,id',
            'quantity' => 'integer|min:1',
            'color_id' => 'nullable',
            'attributes' => 'nullable|array',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;
        
        // Check if variant is selected
        $variant = null;
        if ($request->variant_id) {
            $variant = Product::find($request->variant_id);
            if ($variant && $variant->parent_id != $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid variant selected.'
                ], 400);
            }
        }

        // Use variant data if available
        $selectedProduct = $variant ?? $product;
        $availableStock = $selectedProduct->quantity;
        
        // Check stock - consider color-specific stock if applicable
        if ($request->color_id) {
            $colorPivot = $product->colors()->where('color_id', $request->color_id)->first();
            if ($colorPivot && $colorPivot->pivot->quantity !== null) {
                $availableStock = $colorPivot->pivot->quantity;
            }
        }

        if ($availableStock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available.'
            ], 400);
        }

        // Prepare variant data
        $variantData = [];

        // Add variant info if selected
        if ($variant) {
            $variantData['variant_id'] = $variant->id;
            $variantData['variant_name'] = $variant->name;
            $variantData['sku'] = $variant->sku;
            $variantData['image'] = $variant->featured_image;
        }
        
        // Add color info
        if ($request->color_id) {
            $colorId = $request->input('color_id');
            $color = \App\Models\Color::find($colorId);
            
            if ($color) {
                $variantData['color_id'] = $color->id;
                $variantData['color_name'] = $color->name;
                $variantData['color_hex'] = $color->hex_code;
                
                // Check for color-specific price adjustment
                $colorPivot = $product->colors()->where('color_id', $colorId)->first();
                if ($colorPivot && $colorPivot->pivot->price_adjustment) {
                    $variantData['price_adjustment'] = $colorPivot->pivot->price_adjustment;
                }
                
                // Check for color-specific image
                if ($colorPivot && $colorPivot->pivot->image) {
                    $variantData['image'] = $colorPivot->pivot->image;
                }
            } else {
                // Color ID might be from nested values structure (array index)
                // Try to find color data directly from product's colors JSON
                $productColorsRaw = $product->colors;
                $productColors = is_string($productColorsRaw) ? json_decode($productColorsRaw, true) : ($productColorsRaw ?? []);
                
                if (!empty($productColors) && is_array($productColors)) {
                    foreach ($productColors as $colorItem) {
                        // Handle nested 'values' structure
                        if (isset($colorItem['values']) && is_array($colorItem['values'])) {
                            foreach ($colorItem['values'] as $valueId => $valueData) {
                                if ((string)$valueId === (string)$colorId || (int)$valueId === (int)$colorId) {
                                    $variantData['color_id'] = $colorId;
                                    $variantData['color_name'] = $valueData['value_name'] ?? 'Color';
                                    $variantData['color_hex'] = $valueData['hex_code'] ?? '#000000';
                                    if (isset($valueData['price']) && $valueData['price'] > 0) {
                                        $variantData['price_adjustment'] = $valueData['price'];
                                    }
                                    if (isset($valueData['image'])) {
                                        $variantData['image'] = $valueData['image'];
                                    }
                                    break 2;
                                }
                            }
                        }
                        // Handle flat structure with color_id
                        elseif (isset($colorItem['color_id']) && (string)$colorItem['color_id'] === (string)$colorId) {
                            $variantData['color_id'] = $colorId;
                            $variantData['color_name'] = $colorItem['name'] ?? $colorItem['value_name'] ?? 'Color';
                            $variantData['color_hex'] = $colorItem['hex_code'] ?? '#000000';
                            if (isset($colorItem['price']) && $colorItem['price'] > 0) {
                                $variantData['price_adjustment'] = $colorItem['price'];
                            }
                            break;
                        }
                    }
                }
            }
        }

        // Add variant image from frontend if provided
        if ($request->variant_image) {
            $variantData['image'] = $request->variant_image;
        }
        
        // Add attribute info
        if ($request->has('attributes') && is_array($request->input('attributes'))) {
            $variantData['attributes'] = [];
            
            // Get product's attributes JSON for fallback lookup
            $productAttrsRaw = $product->attributes;
            $productAttrs = is_string($productAttrsRaw) ? json_decode($productAttrsRaw, true) : ($productAttrsRaw ?? []);
            
            foreach ($request->input('attributes') as $key => $valueId) {
                $attributeValue = \App\Models\AttributeValue::with('attribute')->find($valueId);
                if ($attributeValue) {
                    $variantData['attributes'][] = [
                        'attribute_id' => $attributeValue->attribute_id,
                        'attribute_name' => $attributeValue->attribute->name ?? '',
                        'value_id' => $attributeValue->id,
                        'value' => $attributeValue->value,
                    ];
                } else {
                    // Fallback: find attribute from product's JSON attributes
                    if (!empty($productAttrs) && is_array($productAttrs)) {
                        foreach ($productAttrs as $attrId => $attrData) {
                            $attrName = $attrData['name'] ?? '';
                            if (isset($attrData['values']) && is_array($attrData['values'])) {
                                foreach ($attrData['values'] as $vid => $valueData) {
                                    if ((string)$vid === (string)$valueId || (int)$vid === (int)$valueId) {
                                        $variantData['attributes'][] = [
                                            'attribute_id' => $attrId,
                                            'attribute_name' => $attrName,
                                            'value_id' => $vid,
                                            'value' => $valueData['value_name'] ?? $valueData['value'] ?? '',
                                        ];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $cart = $this->getCart();
        $cart->addItem($product, $quantity, $variantData);
        
        // Save session to ensure cart_id is persisted
        session()->save();

        // Build items array for immediate response
        $items = [];
        if ($cart && $cart->items) {
            foreach ($cart->items as $index => $item) {
                $productId = $item['product_id'] ?? null;
                $productItem = Product::find($productId);
                if ($productItem) {
                    $price = $item['price'] ?? ($productItem->sale_price ?? $productItem->price);
                    
                    // Apply price adjustment if exists
                    if (isset($item['price_adjustment'])) {
                        $price += $item['price_adjustment'];
                    }
                    
                    $imagePath = $item['image'] ?? $productItem->featured_image ?? $productItem->image;
                    
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
                    
                    $colorName = $item['color_name'] ?? null;
                    $colorHex = $item['color_hex'] ?? null;
                    if (!$colorName && isset($item['variant_data']['color_name'])) {
                        $colorName = $item['variant_data']['color_name'];
                        $colorHex = $item['variant_data']['color_hex'] ?? null;
                    }
                    
                    $attributes = $item['attributes'] ?? [];
                    if (empty($attributes) && isset($item['variant_data']['attributes'])) {
                        $attributes = $item['variant_data']['attributes'];
                    }
                    
                    $variantBadges = [];
                    if ($colorName) {
                        $variantBadges[] = [
                            'type' => 'color',
                            'label' => 'Color',
                            'value' => $colorName,
                            'hex' => $colorHex,
                        ];
                    }
                    if (isset($attributes) && is_array($attributes)) {
                        foreach ($attributes as $attr) {
                            if (!empty($attr['value'])) {
                                $variantBadges[] = [
                                    'type' => 'attribute',
                                    'label' => $attr['attribute_name'] ?? '',
                                    'value' => $attr['value'],
                                ];
                            }
                        }
                    }
                    
                    $variantDesc = [];
                    $variantInfo = [];
                    if ($colorName) {
                        $variantDesc[] = 'Color: ' . $colorName;
                        $variantInfo[] = $colorName;
                    }
                    if (isset($attributes) && is_array($attributes)) {
                        foreach ($attributes as $attr) {
                            $variantDesc[] = ($attr['attribute_name'] ?? '') . ': ' . ($attr['value'] ?? '');
                            if (!empty($attr['value'])) {
                                $variantInfo[] = $attr['value'];
                            }
                        }
                    }

                    $items[] = [
                        'cart_item_id' => $item['cart_item_id'] ?? $index,
                        'id' => $index,
                        'product_id' => $productId,
                        'name' => $item['name'] ?? $productItem->name,
                        'price' => $price,
                        'image' => $imageUrl,
                        'quantity' => $item['quantity'] ?? 1,
                        'variant' => implode(', ', $variantDesc),
                        'variant_info' => implode(' / ', array_filter($variantInfo)),
                        'variant_badges' => $variantBadges,
                        'color_name' => $colorName,
                        'color_hex' => $colorHex,
                        'attributes' => $attributes,
                        'variant_data' => $item['variant_data'] ?? null,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart!',
            'cart_count' => $cart->getItemCount(),
            'cart_id' => $cart->id,
            'items' => $items,
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();
        $cart->updateItem($request->cart_item_id, $request->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated!',
            'cart_total' => $cart->getTotal()
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|string',
        ]);

        $cart = $this->getCart();
        $cart->removeItem($request->cart_item_id);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart!',
            'cart_count' => $cart->getItemCount()
        ]);
    }

    /**
     * Clear the cart.
     */
    public function clear()
    {
        Session::forget('cart');
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared!'
        ]);
    }

    /**
     * Get cart item count.
     */
    public function count()
    {
        $cart = $this->getCart();
        
        // Count only items whose products still exist (filter out orphaned items)
        $validCount = 0;
        $dirty = false;
        $items = $cart->items ?? [];
        $cleanItems = [];
        
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            if ($productId && Product::find($productId)) {
                $validCount += (int) ($item['quantity'] ?? 1);
                $cleanItems[] = $item;
            } else {
                $dirty = true;
            }
        }
        
        // Auto-clean orphaned items from cart
        if ($dirty) {
            $cart->items = $cleanItems;
            $cart->save();
        }
        
        return response()->json(['count' => $validCount]);
    }

    /**
     * Get cart items for API.
     */
    public function items(Request $request)
    {
        $cart = $this->getCart();
        $items = [];
        $subtotal = 0;
        
        if ($cart && $cart->items) {
            foreach ($cart->items as $index => $item) {
                $productId = $item['product_id'] ?? null;
                $product = Product::find($productId);
                if ($product) {
                    $price = $item['price'] ?? ($product->sale_price ?? $product->price);
                    $imagePath = $item['image'] ?? $product->featured_image ?? $product->image;
                    
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
                    
                    $variantInfo = [];
                    $variantBadges = [];
                    
                    $colorName = $item['color_name'] ?? null;
                    $colorHex = $item['color_hex'] ?? null;
                    
                    if (!$colorName && isset($item['variant_data']['color_name'])) {
                        $colorName = $item['variant_data']['color_name'];
                        $colorHex = $item['variant_data']['color_hex'] ?? null;
                    }
                    
                    if ($colorName) {
                        $variantInfo[] = $colorName;
                        $variantBadges[] = [
                            'type' => 'color',
                            'label' => 'Color',
                            'value' => $colorName,
                            'hex' => $colorHex,
                        ];
                    }
                    
                    $attributes = $item['attributes'] ?? [];
                    if (empty($attributes) && isset($item['variant_data']['attributes'])) {
                        $attributes = $item['variant_data']['attributes'];
                    }
                    
                    if (isset($attributes) && is_array($attributes)) {
                        foreach ($attributes as $attr) {
                            if (!empty($attr['value'])) {
                                $variantInfo[] = $attr['value'];
                                $variantBadges[] = [
                                    'type' => 'attribute',
                                    'label' => $attr['attribute_name'] ?? '',
                                    'value' => $attr['value'],
                                ];
                            }
                        }
                    }

                    $items[] = [
                        'cart_item_id' => $item['cart_item_id'] ?? $index,
                        'id' => $index,
                        'product_id' => $productId,
                        'name' => $item['name'] ?? $product->name,
                        'price' => $price,
                        'image' => $imageUrl,
                        'quantity' => $item['quantity'] ?? 1,
                        'subtotal' => $price * ($item['quantity'] ?? 1),
                        'variant_info' => implode(' / ', array_filter($variantInfo)),
                        'variant_badges' => $variantBadges,
                        'color_name' => $item['color_name'] ?? null,
                        'color_hex' => $item['color_hex'] ?? null,
                        'attributes' => $item['attributes'] ?? [],
                    ];
                    $subtotal += $price * ($item['quantity'] ?? 1);
                }
            }
        }
        
        // Calculate delivery cost using admin shipping settings
        $freeShippingEnabled = Setting::get('free_shipping_enabled', '0') === '1';
        $freeShippingMinAmount = (float) Setting::get('free_shipping_min_amount', 0);
        $defaultShippingCost = (float) Setting::get('default_shipping_cost', 0);
        
        // Check if free shipping applies
        if ($freeShippingEnabled && $freeShippingMinAmount > 0 && $subtotal >= $freeShippingMinAmount) {
            $delivery = 0;
        } else {
            $delivery = $defaultShippingCost;
        }
        
        return response()->json([
            'items' => $items,
            'subtotal' => number_format($subtotal, 2),
            'delivery' => number_format($delivery, 2),
            'total' => number_format($subtotal + $delivery, 2),
            'cart_id' => $cart->id,
            'free_shipping_enabled' => $freeShippingEnabled,
            'free_shipping_min_amount' => $freeShippingMinAmount,
            'free_shipping_remaining' => $freeShippingEnabled && $freeShippingMinAmount > 0 
                ? max(0, $freeShippingMinAmount - $subtotal) 
                : 0,
        ]);
    }

/**
     * Get or create cart instance.
     */
    private function getCart()
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
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
