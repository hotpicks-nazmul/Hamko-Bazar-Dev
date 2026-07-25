<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get user's shipping address for tax calculation
     */
    protected function getTaxAddress()
    {
        $user = $this->user;
        
        if ($user) {
            $address = $user->addresses()->where('is_default', true)->first()
                ?? $user->addresses()->first();
            
            if ($address) {
                return [
                    'country' => $address->country,
                    'state' => $address->state,
                    'zip_code' => $address->zip_code,
                ];
            }
        }
        
        // Check session for address
        return session('tax_address', [
            'country' => null,
            'state' => null,
            'zip_code' => null,
        ]);
    }

    /**
     * Calculate tax amount for the cart
     */
    public function getTax()
    {
        $taxEnabled = Setting::get('tax_enabled', '1');
        
        if ($taxEnabled != '1') {
            return 0;
        }

        $taxType = Setting::get('tax_type', 'global');
        $subtotal = $this->getSubtotal();
        
        if ($taxType === 'location') {
            $address = $this->getTaxAddress();
            $tax = Tax::getTaxForLocation(
                $address['country'],
                $address['state'],
                $address['zip_code']
            );
        } else {
            $tax = Tax::getDefault();
        }
        
        if (!$tax) {
            return 0;
        }
        
        return $tax->calculateTax($subtotal);
    }

    /**
     * Get cart total including tax
     */
    public function getTotal()
    {
        return $this->getSubtotal() + $this->getTax();
    }

    /**
     * Get tax rate percentage
     */
    public function getTaxRate()
    {
        $taxType = Setting::get('tax_type', 'global');
        
        if ($taxType === 'location') {
            $address = $this->getTaxAddress();
            $tax = Tax::getTaxForLocation(
                $address['country'],
                $address['state'],
                $address['zip_code']
            );
        } else {
            $tax = Tax::getDefault();
        }
        
        return $tax ? $tax->rate : 0;
    }

    /**
     * Get the active tax
     */
    public function getActiveTax()
    {
        $taxType = Setting::get('tax_type', 'global');
        
        if ($taxType === 'location') {
            $address = $this->getTaxAddress();
            return Tax::getTaxForLocation(
                $address['country'],
                $address['state'],
                $address['zip_code']
            );
        } else {
            return Tax::getDefault();
        }
    }

    public function addItem($product, $quantity = 1, $variantData = [])
    {
        $items = $this->items ?? [];

        $price = isset($variantData['custom_price']) ? $variantData['custom_price'] : $product->final_price;
        
        // Generate unique variant key to check for existing item
        $variantKey = $this->generateVariantKey($product->id, $variantData);
        
        // Check if item with same variant already exists
        foreach ($items as &$item) {
            if (isset($item['product_id']) && $item['product_id'] == $product->id) {
                $existingKey = $this->generateVariantKey($product->id, [
                    'color_id' => $item['color_id'] ?? null,
                    'attributes' => $item['attributes'] ?? [],
                ]);
                
                if ($existingKey === $variantKey) {
                    // Same variant found - increment quantity
                    $item['quantity'] = ($item['quantity'] ?? 1) + $quantity;
                    $this->items = $items;
                    $this->save();
                    return;
                }
            }
        }
        
        // No existing item found - create new item
        $newItem = [
            'cart_item_id' => uniqid('cart_', true),
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $price,
            'quantity' => $quantity,
            'image' => $variantData['image'] ?? $product->featured_image ?? $product->image,
            'variant_data' => $variantData,
        ];
        
        if (isset($variantData['color_id'])) {
            $newItem['color_id'] = $variantData['color_id'];
            $newItem['color_name'] = $variantData['color_name'] ?? '';
            $newItem['color_hex'] = $variantData['color_hex'] ?? '';
        }
        
        if (isset($variantData['price_adjustment'])) {
            $newItem['price_adjustment'] = $variantData['price_adjustment'];
        }
        
        if (isset($variantData['image'])) {
            $newItem['image'] = $variantData['image'];
        }
        
        if (isset($variantData['attributes'])) {
            $newItem['attributes'] = $variantData['attributes'];
        }
        
        $items[] = $newItem;

        $this->items = $items;
        $this->save();
    }
    
    /**
     * Generate a unique key for a product variant using value_ids.
     */
    public function generateVariantKey($productId, $variantData)
    {
        $key = 'product_' . $productId;

        // Use color_id for uniqueness
        if (isset($variantData['color_id']) && !empty($variantData['color_id'])) {
            $key .= '_color_' . $variantData['color_id'];
        }

        // Use attribute value_ids for uniqueness (sorted for consistency)
        if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
            $attrIds = [];
            foreach ($variantData['attributes'] as $attr) {
                // Use value_id for uniqueness
                if (isset($attr['value_id'])) {
                    $attrIds[] = 'attr_' . $attr['value_id'];
                }
            }
            sort($attrIds);
            if (!empty($attrIds)) {
                $key .= '_attrs_' . implode('_', $attrIds);
            }
        }

        return $key;
    }

    public function updateItem($cartItemId, $quantity)
    {
        $items = $this->items ?? [];
        
        foreach ($items as &$item) {
            if ($item['cart_item_id'] === $cartItemId) {
                $item['quantity'] = $quantity;
                break;
            }
        }

        $this->items = $items;
        $this->save();
    }

    public function removeItem($cartItemId)
    {
        $items = $this->items ?? [];
        
        $items = array_filter($items, function ($item) use ($cartItemId) {
            return $item['cart_item_id'] !== $cartItemId;
        });

        $this->items = array_values($items);
        $this->save();
    }

    public function getItemCount()
    {
        $items = $this->items ?? [];
        return (int) array_sum(array_column($items, 'quantity'));
    }

    public function getSubtotal()
    {
        $items = $this->items ?? [];
        return array_sum(array_map(function ($item) {
            $price = $item['price'];
            // Add price adjustment if exists (only when not using custom_price)
            if (isset($item['price_adjustment']) && !isset($item['variant_data']['custom_price'])) {
                $price += $item['price_adjustment'];
            }
            return $price * $item['quantity'];
        }, $items));
    }
    /**
     * Check if cart is empty.
     */
    public function isEmpty()
    {
        return empty($this->items) || count($this->items) === 0;
    }

    /**
     * Get the subtotal (alias for getSubtotal).
     */
    public function getSubtotalAttribute()
    {
        return $this->getSubtotal();
    }

    /**
     * Get total items count.
     */
    public function getTotalItemsAttribute()
    {
        return $this->getItemCount();
    }

    /**
     * Clear the cart.
     */
    public function clear()
    {
        $this->items = [];
        $this->save();
    }
}
