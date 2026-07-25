<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function created(Product $product)
    {
        if ($product->isLowStock()) {
            notify_low_stock($product);
        }

        if ($product->isOutOfStock()) {
            notify_out_of_stock($product);
        }
    }

    public function updated(Product $product)
    {
        if ($product->isOutOfStock() && !$product->wasChanged('quantity')) {
            return;
        }

        if ($product->isOutOfStock()) {
            $previousStock = $product->getOriginal('quantity');
            if ($previousStock > 0) {
                notify_out_of_stock($product);
            }
        } elseif ($product->isLowStock()) {
            $previousStock = $product->getOriginal('quantity');
            if ($previousStock > $product->low_stock_threshold) {
                notify_low_stock($product);
            }
        }
    }
}
