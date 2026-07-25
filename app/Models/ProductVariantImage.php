<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantImage extends Model
{
    protected $fillable = [
        'product_id',
        'combination_key',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function generateKey(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $attrType => $valueId) {
            if ($valueId) {
                $parts[] = $attrType . '_' . $valueId;
            }
        }
        sort($parts);
        return implode('_', $parts);
    }

    public static function getImage($productId, array $attributes): ?string
    {
        $key = self::generateKey($attributes);
        $variantImage = self::where('product_id', $productId)
            ->where('combination_key', $key)
            ->first();
        
        return $variantImage?->image;
    }

    public static function deleteByProduct($productId): void
    {
        self::where('product_id', $productId)->delete();
    }

    public static function getByProduct($productId): array
    {
        return self::where('product_id', $productId)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }
}