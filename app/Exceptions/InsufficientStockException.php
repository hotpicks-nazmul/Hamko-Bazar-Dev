<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

/**
 * Thrown when a movement would take more stock than the shelf holds and the caller
 * did not opt into overselling.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public ?Product $product,
        public int $requested,
        public int $available
    ) {
        $name = $product->name ?? 'This product';

        parent::__construct("{$name}: only {$available} available, {$requested} requested.");
    }
}
